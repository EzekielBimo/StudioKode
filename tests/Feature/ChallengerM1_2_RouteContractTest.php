<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM1_2_RouteContractTest extends TestCase
{
    use RefreshDatabase;

    private string $inertiaVersion = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Derive active Inertia version from build manifest if present
        $manifest = public_path('build/manifest.json');
        if (file_exists($manifest)) {
            $this->inertiaVersion = hash_file('xxh128', $manifest);
        }
    }

    /**
     * Helper to perform raw Inertia HTTP JSON request and reset defaultHeaders immediately.
     */
    private function getInertiaJson(string $uri, array $extraHeaders = [])
    {
        $headers = array_merge([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $this->inertiaVersion,
            'X-Requested-With' => 'XMLHttpRequest',
        ], $extraHeaders);

        $response = $this->withHeaders($headers)->get($uri);
        $this->flushHeaders();

        return $response;
    }

    /**
     * Challenge Route 1: GET / (Home)
     * Verifies HTTP 200, Inertia component Home/Index, and exact props contract:
     * - featuredProjects with expected fields
     * - statistics with required architectural metrics
     * - philosophyHighlights structure
     */
    public function test_route_1_home_inertia_headers_and_props_contract(): void
    {
        // 1. Raw Inertia HTTP JSON request challenge
        $rawInertia = $this->getInertiaJson('/');

        $rawInertia->assertStatus(200);
        $rawInertia->assertHeader('X-Inertia', 'true');
        $rawInertia->assertHeader('Vary', 'X-Inertia');
        $this->assertStringContainsString('application/json', $rawInertia->headers->get('Content-Type'));

        $json = $rawInertia->json();
        $this->assertSame('Home/Index', $json['component']);
        $this->assertArrayHasKey('props', $json);
        $this->assertArrayHasKey('featuredProjects', $json['props']);
        $this->assertArrayHasKey('statistics', $json['props']);
        $this->assertArrayHasKey('philosophyHighlights', $json['props']);

        // 2. High-level Inertia Assertable assertions (Blade view with page payload)
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects', 3, fn (Assert $project) => $project
                ->has('id')
                ->has('title')
                ->has('slug')
                ->has('location')
                ->has('year')
                ->has('area_size')
                ->has('cover_image')
                ->etc()
            )
            ->has('statistics', 4, fn (Assert $stat) => $stat
                ->has('value')
                ->has('label')
            )
            ->has('philosophyHighlights', 3)
            ->where('statistics.0.value', '15+')
            ->where('statistics.0.label', 'Years Experience')
        );
    }

    /**
     * Challenge Route 2: GET /projects (Projects Index)
     * Verifies HTTP 200, Inertia component Projects/Index, and exact props contract:
     * - projects collection containing all 6 seeded projects
     * - filters object with search and sort
     * - totalCount integer
     */
    public function test_route_2_projects_index_inertia_headers_and_props_contract(): void
    {
        $rawInertia = $this->getInertiaJson('/projects');

        $rawInertia->assertStatus(200);
        $rawInertia->assertHeader('X-Inertia', 'true');
        $rawInertia->assertHeader('Vary', 'X-Inertia');

        $json = $rawInertia->json();
        $this->assertSame('Projects/Index', $json['component']);
        $this->assertCount(6, $json['props']['projects']);
        $this->assertSame(6, $json['props']['totalCount']);
        $this->assertSame('', $json['props']['filters']['search']);
        $this->assertSame('newest', $json['props']['filters']['sort']);

        $this->get('/projects')->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->has('filters', fn (Assert $f) => $f
                ->has('search')
                ->has('sort')
            )
            ->where('totalCount', 6)
        );
    }

    /**
     * Challenge Route 3: GET /projects/{slug} (Projects Show)
     * Verifies HTTP 200, Inertia component Projects/Show, and exact props contract:
     * - project object with complete architectural fields
     * - images array ordered by sort_order
     * - relatedProjects array of 2-3 other projects, excluding the active project
     */
    public function test_route_3_projects_show_inertia_headers_and_props_contract(): void
    {
        $project = Project::firstOrFail();

        $rawInertia = $this->getInertiaJson('/projects/' . $project->slug);

        $rawInertia->assertStatus(200);
        $rawInertia->assertHeader('X-Inertia', 'true');
        $rawInertia->assertHeader('Vary', 'X-Inertia');

        $json = $rawInertia->json();
        $this->assertSame('Projects/Show', $json['component']);
        $this->assertSame($project->id, $json['props']['project']['id']);
        $this->assertSame($project->slug, $json['props']['project']['slug']);
        $this->assertNotEmpty($json['props']['images']);

        // Assert related projects do NOT contain current project
        $relatedIds = array_column($json['props']['relatedProjects'], 'id');
        $this->assertNotContains($project->id, $relatedIds);
        $this->assertGreaterThanOrEqual(2, count($relatedIds));

        $this->get('/projects/' . $project->slug)->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('project', fn (Assert $p) => $p
                ->where('id', $project->id)
                ->where('title', $project->title)
                ->where('slug', $project->slug)
                ->has('client')
                ->has('location')
                ->has('year')
                ->has('area_size')
                ->has('cover_image')
                ->has('masterplan_image')
                ->etc()
            )
            ->has('images')
            ->has('relatedProjects')
        );
    }

    /**
     * Challenge Route 4: GET /studio (Studio Index)
     * Verifies HTTP 200, Inertia component Studio/Index, and exact props contract:
     * - studio narrative metadata
     * - philosophy pillars
     * - team profiles
     * - process stages
     * - accolades
     */
    public function test_route_4_studio_inertia_headers_and_props_contract(): void
    {
        $rawInertia = $this->getInertiaJson('/studio');

        $rawInertia->assertStatus(200);
        $rawInertia->assertHeader('X-Inertia', 'true');

        $json = $rawInertia->json();
        $this->assertSame('Studio/Index', $json['component']);
        $this->assertArrayHasKey('studio', $json['props']);
        $this->assertArrayHasKey('philosophy', $json['props']);
        $this->assertArrayHasKey('team', $json['props']);
        $this->assertArrayHasKey('process', $json['props']);
        $this->assertArrayHasKey('accolades', $json['props']);

        $this->get('/studio')->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->has('studio.name')
            ->has('studio.manifesto')
            ->has('philosophy', 4)
            ->has('team', 4)
            ->has('process', 4)
            ->has('accolades', 3)
        );
    }

    /**
     * Challenge Route 5: GET /services (Services Index)
     * Verifies HTTP 200, Inertia component Services/Index, and exact props contract:
     * - services array of 5 architectural capabilities with deliverables
     */
    public function test_route_5_services_inertia_headers_and_props_contract(): void
    {
        $rawInertia = $this->getInertiaJson('/services');

        $rawInertia->assertStatus(200);
        $rawInertia->assertHeader('X-Inertia', 'true');

        $json = $rawInertia->json();
        $this->assertSame('Services/Index', $json['component']);
        $this->assertArrayHasKey('services', $json['props']);
        $this->assertCount(5, $json['props']['services']);

        $this->get('/services')->assertInertia(fn (Assert $page) => $page
            ->component('Services/Index')
            ->has('services', 5, fn (Assert $service) => $service
                ->has('id')
                ->has('number')
                ->has('title')
                ->has('tagline')
                ->has('description')
                ->has('deliverables')
            )
        );
    }

    /**
     * Challenge Route 6: GET /contact (Contact Index)
     * Verifies HTTP 200, Inertia component Contact/Index, and exact props contract:
     * - studios array (Kyoto, Zurich, Melbourne)
     * - consultationDetails object
     */
    public function test_route_6_contact_inertia_headers_and_props_contract(): void
    {
        $rawInertia = $this->getInertiaJson('/contact');

        $rawInertia->assertStatus(200);
        $rawInertia->assertHeader('X-Inertia', 'true');

        $json = $rawInertia->json();
        $this->assertSame('Contact/Index', $json['component']);
        $this->assertArrayHasKey('studios', $json['props']);
        $this->assertArrayHasKey('consultationDetails', $json['props']);

        $this->get('/contact')->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('studios', 3)
            ->has('consultationDetails.leadTime')
            ->has('consultationDetails.minimumScale')
        );
    }

    /**
     * Challenge Route 7: POST /inquiries (Inquiries Store)
     * Verifies HTTP 302 redirect, database record creation, session flash message,
     * and Inertia flash prop transmission on the subsequent page visit.
     */
    public function test_route_7_inquiries_store_redirect_persistence_and_flash_contract(): void
    {
        $inquiryData = [
            'name' => 'Baroness Charlotte von Berg',
            'email' => 'charlotte@sanctuary-estates.ch',
            'phone' => '+41 44 991 4200',
            'site_location' => 'Lake Zurich, Switzerland',
            'estimated_area' => '1,450 m²',
            'budget_range' => '$500,000+',
            'message' => 'Commissioning an alpine lakeside garden sanctuary with natural reflection pool.',
        ];

        // Perform POST request from /contact referer
        $response = $this->from('/contact')->post('/inquiries', $inquiryData);

        // 1. Assert 302 redirect back to referer
        $response->assertStatus(302);
        $response->assertRedirect('/contact');
        $response->assertSessionHas('success', 'Thank you for contacting StudioKode. Our principal architects will review your project.');

        // 2. Assert database persistence
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Baroness Charlotte von Berg',
            'email' => 'charlotte@sanctuary-estates.ch',
            'estimated_area' => '1,450 m²',
            'status' => 'new',
        ]);

        // 3. Follow redirect and verify Inertia receives flash prop in shared data
        $followResponse = $this->get('/contact');
        $followResponse->assertStatus(200);
        $followResponse->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->where('flash.success', 'Thank you for contacting StudioKode. Our principal architects will review your project.')
        );

        // 4. Subsequent visit verifies flash is transient (consumed)
        $subsequentResponse = $this->get('/contact');
        $subsequentResponse->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->where('flash.success', null)
        );
    }

    /**
     * Challenge Route 7 Alias: POST /contact (Contact Store)
     * Verifies that POST /contact is functional as an alias to /inquiries.
     */
    public function test_route_7_contact_alias_store_redirect_and_persistence(): void
    {
        $inquiryData = [
            'name' => 'Kenzo Tange Studio',
            'email' => 'projects@kenzotange-demo.jp',
            'message' => 'Botanical consultation inquiry via /contact route.',
        ];

        $response = $this->from('/contact')->post('/contact', $inquiryData);

        $response->assertStatus(302);
        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'projects@kenzotange-demo.jp',
            'message' => 'Botanical consultation inquiry via /contact route.',
        ]);
    }

    /**
     * Standard non-Inertia requests must return valid HTML with div#app and data-page attribute.
     */
    public function test_html_response_renders_data_page_when_no_inertia_header(): void
    {
        $routes = ['/', '/projects', '/studio', '/services', '/contact'];

        foreach ($routes as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(200);
            $this->assertStringStartsWith('text/html', $response->headers->get('Content-Type'));
            $content = $response->getContent();
            $this->assertStringContainsString('id="app"', $content, "Missing div#app on {$uri}");
            $this->assertStringContainsString('data-page="', $content, "Missing data-page on {$uri}");
        }
    }

    /**
     * Inertia Version Mismatch Protection:
     * When X-Inertia: true is sent without the matching asset version,
     * Inertia MUST return 409 Conflict with X-Inertia-Location header.
     */
    public function test_inertia_version_mismatch_returns_409_conflict(): void
    {
        if ($this->inertiaVersion === '') {
            $this->markTestSkipped('No build manifest present to test version conflict.');
        }

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => 'outdated-hash-99999',
        ])->get('/');
        $this->flushHeaders();

        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location');
    }

    /**
     * Comprehensive 404 matrix for project slug boundaries:
     * - completely nonexistent slug
     * - special characters & XSS injection
     * - SQL injection strings
     * - pure numeric unseeded ID
     * - Unicode non-existent slug
     */
    public function test_404_responses_for_invalid_project_slugs_matrix(): void
    {
        $adversarialSlugs = [
            'non-existent-garden-villa',
            '123456789',
            '%3Cscript%3Ealert(document.cookie)%3C%2Fscript%3E',
            "'; DROP TABLE projects; --",
            "non'existent",
            'invalid/nested/slug',
            '京都市左京区-nonexistent',
            '---',
        ];

        foreach ($adversarialSlugs as $slug) {
            $response = $this->get('/projects/' . $slug);
            $this->assertSame(404, $response->getStatusCode(), "Expected 404 for slug: {$slug}");
        }
    }

    /**
     * 404 test on completely non-existent top-level routes.
     */
    public function test_404_response_for_non_existent_system_routes(): void
    {
        $bogusRoutes = ['/bogus-admin', '/unknown-dashboard', '/api/projects', '/portfolio', '/about-us'];

        foreach ($bogusRoutes as $route) {
            $response = $this->get($route);
            $this->assertSame(404, $response->getStatusCode(), "Expected 404 for route: {$route}");
        }
    }

    /**
     * Method Not Allowed (HTTP 405) checks on non-permitted HTTP verbs.
     */
    public function test_405_method_not_allowed_for_restricted_verbs(): void
    {
        // GET on /inquiries is not permitted
        $response = $this->get('/inquiries');
        $this->assertSame(405, $response->getStatusCode());

        // PUT on /projects is not permitted
        $response = $this->put('/projects');
        $this->assertSame(405, $response->getStatusCode());

        // DELETE on /services is not permitted
        $response = $this->delete('/services');
        $this->assertSame(405, $response->getStatusCode());
    }

    /**
     * Test Inertia partial reloads: requesting only a subset of props.
     */
    public function test_inertia_partial_reloads(): void
    {
        $response = $this->getInertiaJson('/', [
            'X-Inertia-Partial-Component' => 'Home/Index',
            'X-Inertia-Partial-Data' => 'statistics',
        ]);

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertSame('Home/Index', $json['component']);
        // Only statistics prop returned in partial reload
        $this->assertArrayHasKey('statistics', $json['props']);
        $this->assertArrayNotHasKey('featuredProjects', $json['props']);
    }

    /**
     * Test Area Size sorting cross-check:
     * Largest Area Size sorting in Projects Index orders projects by numerical area descending.
     */
    public function test_projects_index_area_size_sorting_empirical_order(): void
    {
        $response = $this->getInertiaJson('/projects?sort=area_desc');
        $response->assertStatus(200);

        $projects = $response->json()['props']['projects'];
        $this->assertCount(6, $projects);

        $parsedAreas = array_map(function ($proj) {
            return (int) preg_replace('/[^0-9]/', '', $proj['area_size']);
        }, $projects);

        // Assert strictly descending or equal
        for ($i = 0; $i < count($parsedAreas) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $parsedAreas[$i + 1],
                $parsedAreas[$i],
                "Area sizes not correctly sorted descending: {$parsedAreas[$i]} vs {$parsedAreas[$i + 1]}"
            );
        }
    }
}
