<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Challenger C1: Search with SQL LIKE wildcard character '%'
     * Should search for literal '%' or match safely without SQL error.
     */
    public function test_project_search_with_percent_wildcard(): void
    {
        $response = $this->get('/projects?search=%');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    /**
     * Challenger C2: Search with SQL LIKE wildcard character '_'
     */
    public function test_project_search_with_underscore_wildcard(): void
    {
        $response = $this->get('/projects?search=_');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    /**
     * Challenger C3: Search with SQL injection payload `' OR '1'='1`
     * Must not dump unfiltered results or cause SQL syntax error.
     */
    public function test_project_search_sql_injection_resilience(): void
    {
        $response = $this->get("/projects?search=' OR '1'='1");
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /**
     * Challenger C4: Search with HTML script tag
     */
    public function test_project_search_html_script_injection(): void
    {
        $response = $this->get('/projects?search=<script>alert("xss")</script>');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
            ->where('filters.search', '<script>alert("xss")</script>')
        );
    }

    /**
     * Challenger C5: Search with Unicode/Japanese text
     */
    public function test_project_search_with_unicode_japanese(): void
    {
        // "Kyoto" in seeded DB is in English, searching Japanese should safely return 0 results
        $response = $this->get('/projects?search=京都');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /**
     * Challenger C6: Search with leading and trailing whitespace
     */
    public function test_project_search_trims_whitespace(): void
    {
        $response = $this->get('/projects?search=%20%20Kyoto%20%20');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'moss-stone-sanctuary')
        );
    }

    /**
     * Challenger C7: Unrecognized sort parameter falls back gracefully to default (newest)
     */
    public function test_project_unrecognized_sort_defaults_gracefully(): void
    {
        $response = $this->get('/projects?sort=malicious_or_unknown_sort_value');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('filters.sort', 'malicious_or_unknown_sort_value')
        );
    }

    /**
     * Challenger C8: Area size descending sort order strictly verified
     */
    public function test_project_sort_area_descending_exact_order(): void
    {
        $response = $this->get('/projects?sort=area_desc');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('projects', function ($projects) {
                $slugs = collect($projects)->pluck('slug')->all();
                $expected = [
                    'coastal-dune-pine-refuge',    // 2,100 m²
                    'verdant-terrace-estate',      // 1,250 m²
                    'highland-mist-alpine-garden', // 850 m²
                    'oasis-dune-residence',        // 620 m²
                    'moss-stone-sanctuary',        // 380 m²
                    'atelier-botanica-courtyard',  // 180 m²
                ];
                return $slugs === $expected;
            })
        );
    }

    /**
     * Challenger C9: Invalid inquiry - missing all required fields
     */
    public function test_inquiry_fails_empty_payload(): void
    {
        $response = $this->post('/inquiries', []);
        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertDatabaseCount('inquiries', 2); // only 2 seeded
    }

    /**
     * Challenger C10: Invalid inquiry - invalid email formats
     */
    public function test_inquiry_fails_various_invalid_email_formats(): void
    {
        $invalidEmails = [
            'plainaddress',
            '@missing-username.com',
            'username@.com',
            'username@domain..com',
            'spaces in@email.com',
        ];

        foreach ($invalidEmails as $badEmail) {
            $response = $this->post('/inquiries', [
                'name' => 'Test User',
                'email' => $badEmail,
                'message' => 'Test Message for bad email',
            ]);
            $response->assertSessionHasErrors(['email']);
        }
    }

    /**
     * Challenger C11: Invalid inquiry - oversized fields
     */
    public function test_inquiry_fails_oversized_optional_fields(): void
    {
        // phone > 50 chars
        $response = $this->post('/inquiries', [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'phone' => str_repeat('1', 51),
            'message' => 'Valid message',
        ]);
        $response->assertSessionHasErrors(['phone']);

        // site_location > 255 chars
        $response = $this->post('/inquiries', [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'site_location' => str_repeat('L', 256),
            'message' => 'Valid message',
        ]);
        $response->assertSessionHasErrors(['site_location']);

        // estimated_area > 100 chars
        $response = $this->post('/inquiries', [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'estimated_area' => str_repeat('A', 101),
            'message' => 'Valid message',
        ]);
        $response->assertSessionHasErrors(['estimated_area']);

        // budget_range > 100 chars
        $response = $this->post('/inquiries', [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'budget_range' => str_repeat('B', 101),
            'message' => 'Valid message',
        ]);
        $response->assertSessionHasErrors(['budget_range']);
    }

    /**
     * Challenger C12: Valid inquiry with boundary exact maximum lengths
     */
    public function test_inquiry_succeeds_exact_boundary_maximum_lengths(): void
    {
        $payload = [
            'name' => str_repeat('N', 255),
            'email' => 'boundary@example.com',
            'phone' => str_repeat('P', 50),
            'site_location' => str_repeat('L', 255),
            'estimated_area' => str_repeat('A', 100),
            'budget_range' => str_repeat('$', 100),
            'message' => str_repeat('M', 5000),
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'boundary@example.com',
            'phone' => str_repeat('P', 50),
            'site_location' => str_repeat('L', 255),
            'estimated_area' => str_repeat('A', 100),
            'budget_range' => str_repeat('$', 100),
        ]);
    }

    /**
     * Challenger C13: Contact route alias POST /contact also works identically
     */
    public function test_contact_route_alias_stores_inquiry(): void
    {
        $payload = [
            'name' => 'Contact Route User',
            'email' => 'contact_user@example.com',
            'message' => 'Submitted via /contact POST route.',
        ];

        $response = $this->post('/contact', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'contact_user@example.com',
        ]);
    }
}
