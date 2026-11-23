<?php

namespace Tests\Feature\E2E;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

class Tier3CrossFeatureTest extends E2ETestCase
{
    /**
     * Cross 1: FilterBar Search × Category Filter × Numeric Sort combined interaction.
     */
    public function test_xfeat_search_category_and_sort_combined_filtering(): void
    {
        $response = $this->get('/projects?category=design&search=Kyoto&sort=area_desc');
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('activeCategory', 'design')
            ->where('filters.search', 'Kyoto')
            ->where('filters.sort', 'area_desc')
            ->has('projects', fn (Assert $projects) => $projects
                ->each(fn (Assert $p) => $p
                    ->where('category', Project::CATEGORY_DESIGN)
                    ->etc()
                )
            )
        );
    }

    /**
     * Cross 2: Contact form submission propagates flash toast to PublicLayout session.
     */
    public function test_xfeat_contact_form_submission_propagates_flash_to_public_layout(): void
    {
        $payload = [
            'name' => 'Sophia Vane',
            'email' => 'sophia@vane-estates.com',
            'message' => 'Lakeside botanical sanctuary consultation request.',
        ];

        // Submit form from contact page
        $response = $this->from('/contact')->post('/inquiries', $payload);
        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');

        // Follow redirect to assert flash in session
        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(200);
        $contactResponse->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('flash.success')
        );
    }

    /**
     * Cross 3: Navigation graph integrity across all 4 canonical public routes.
     */
    public function test_xfeat_navigation_graph_integrity_across_public_views(): void
    {
        $canonicalRoutes = [
            '/' => 'Home/Index',
            '/projects' => 'Projects/Index',
            '/studio' => 'Studio/Index',
            '/contact' => 'Contact/Index',
        ];

        foreach ($canonicalRoutes as $route => $component) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page->component($component));
        }

        // Assert that Navbar and Footer both declare navigation links to all canonical routes
        $navbar = file_get_contents(resource_path('js/Components/Navigation/Navbar.jsx'));
        $footer = file_get_contents(resource_path('js/Components/Navigation/Footer.jsx'));

        $this->assertStringContainsString('href="/"', $navbar, 'Navbar must link to /');
        $this->assertStringContainsString('/projects', $navbar, 'Navbar must link to /projects');
        $this->assertStringContainsString('/studio', $navbar, 'Navbar must link to /studio');
        $this->assertStringContainsString('/contact', $navbar, 'Navbar must link to /contact');

        $links = ['/', '/projects', '/studio', '/contact'];
        foreach ($links as $link) {
            $this->assertStringContainsString("href=\"{$link}\"", $footer, "Footer must link to {$link}");
        }
    }

    /**
     * Cross 4: Home featured projects all resolve to active Project Show routes.
     */
    public function test_xfeat_home_featured_projects_resolve_to_project_show_routes(): void
    {
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);

        $featured = Project::published()
            ->orderByDesc('is_featured')
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $this->assertNotEmpty($featured);

        foreach ($featured as $proj) {
            $showResponse = $this->get('/projects/' . $proj->slug);
            $showResponse->assertStatus(200);
            $showResponse->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Show')
                ->where('project.slug', $proj->slug)
                ->where('project.title', $proj->title)
            );
        }
    }

    /**
     * Cross 5: Projects Index collection items match Projects Show canonical attributes.
     */
    public function test_xfeat_projects_index_and_show_prop_consistency(): void
    {
        $indexResponse = $this->get('/projects');
        $indexResponse->assertStatus(200);

        $projects = Project::published()->get();

        foreach ($projects as $project) {
            $showResponse = $this->get('/projects/' . $project->slug);
            $showResponse->assertStatus(200);

            $showResponse->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Show')
                ->where('project.id', $project->id)
                ->where('project.slug', $project->slug)
                ->where('project.title', $project->title)
                ->where('project.location', $project->location)
                ->where('project.year', $project->year)
                ->where('project.area_size', $project->area_size)
            );
        }
    }

    /**
     * Cross 6: Palette tokens coexistence across Blade, Tailwind, and CSS.
     */
    public function test_xfeat_theme_color_tokens_coexistence_across_views(): void
    {
        $blade = file_get_contents(resource_path('views/app.blade.php'));
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $css = file_get_contents(resource_path('css/app.css'));

        // Redesign tokens asserted individually per file using E2ETestCase helper
        $this->assertContainsRedesignTokens($tailwind, 'tailwind.config.js');
        $this->assertContainsRedesignTokens($css, 'resources/css/app.css');

        // Legacy tokens preservation asserted individually
        $this->assertStringContainsString('#0B0E0C', $tailwind, 'Tailwind must preserve legacy obsidian hex #0B0E0C');
        $this->assertStringContainsString('#0B0E0C', $blade, 'app.blade.php must retain legacy dark theme #0B0E0C');
        $this->assertStringContainsString('#0B0E0C', $css, 'app.css must retain legacy obsidian hex #0B0E0C');
        $this->assertStringContainsString('moss', $tailwind, 'Tailwind must preserve moss token family');
        $this->assertStringContainsString('#1C3024', $css, 'app.css must preserve legacy moss selection token #1C3024');
    }

    /**
     * Cross 7: Typography token coexistence: Instrument Serif and Cormorant Garamond.
     */
    public function test_xfeat_typography_token_coexistence_across_views(): void
    {
        $blade = file_get_contents(resource_path('views/app.blade.php'));
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $css = file_get_contents(resource_path('css/app.css'));

        // Primary display serif is Instrument Serif
        $this->assertStringContainsString('Instrument+Serif', $blade);
        $this->assertStringContainsString('Instrument Serif', $tailwind);
        $this->assertStringContainsString('Instrument Serif', $css);

        // Fallback serif Cormorant Garamond is maintained across all views
        $this->assertStringContainsString('Cormorant Garamond', $tailwind, 'Tailwind must retain Cormorant Garamond fallback');
        $this->assertStringContainsString('Cormorant Garamond', $css, 'app.css must retain Cormorant Garamond fallback');
        $this->assertStringContainsString('Cormorant Garamond', $blade, 'app.blade.php must retain Cormorant Garamond fallback');
    }

    /**
     * Cross 8: Studio Step 1 (Contact Us) aligns with Contact consultation channels.
     */
    public function test_xfeat_studio_concept_steps_align_with_contact_channels(): void
    {
        $studioResponse = $this->get('/studio');
        $studioResponse->assertStatus(200);

        $studioResponse->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('conceptSteps.0.title', 'Contact Us')
        );

        // Verifies Contact route and POST inquiry endpoint are operational
        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(200);

        $inquiryResponse = $this->post('/inquiries', [
            'name' => 'Studio Step 1 Alignment Tester',
            'email' => 'alignment@studiokode.design',
            'message' => 'Initiating concept step 01 brief.',
        ]);
        $inquiryResponse->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['email' => 'alignment@studiokode.design']);
    }

    /**
     * Cross 9: Validation error bag propagates back into Inertia page props.
     */
    public function test_xfeat_validation_error_session_propagation_to_inertia(): void
    {
        $response = $this->from('/contact')->post('/inquiries', [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHasErrors(['name', 'email', 'message']);

        // Inspect redirect destination to verify errors bag in Inertia
        $followResponse = $this->get('/contact');
        $followResponse->assertStatus(200);
        $followResponse->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('errors.name')
            ->has('errors.email')
            ->has('errors.message')
        );
    }

    /**
     * Cross 10: Home featured projects cache coherence.
     */
    public function test_xfeat_home_featured_projects_cache_coherence(): void
    {
        Cache::forget('home.featured_projects');

        $response1 = $this->get('/');
        $response1->assertStatus(200);
        $this->assertTrue(Cache::has('home.featured_projects'));

        $cached = Cache::get('home.featured_projects');
        $this->assertCount(3, $cached);

        // Flushes cache and re-queries
        Cache::forget('home.featured_projects');
        $response2 = $this->get('/');
        $response2->assertStatus(200);
        $this->assertTrue(Cache::has('home.featured_projects'));
    }

    /**
     * Cross 11: Responsive grid layout container invariants across all 5 public pages.
     */
    public function test_xfeat_responsive_grid_class_invariants_across_all_pages(): void
    {
        $pageFiles = [
            'Home/Index.jsx',
            'Projects/Index.jsx',
            'Projects/Show.jsx',
            'Studio/Index.jsx',
            'Contact/Index.jsx',
        ];

        foreach ($pageFiles as $file) {
            $content = file_get_contents(resource_path('js/Pages/' . $file));
            $this->assertStringContainsString('max-w-7xl', $content, "Page {$file} must preserve max-w-7xl container");
        }
    }

    /**
     * Cross 12: Projects Show related projects strictly excludes current project ID.
     */
    public function test_xfeat_related_projects_strictly_excludes_current_project(): void
    {
        $projects = Project::published()->get();
        $this->assertGreaterThan(1, $projects->count());

        foreach ($projects as $project) {
            $response = $this->get('/projects/' . $project->slug);
            $response->assertStatus(200);

            $response->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Show')
                ->where('project.id', $project->id)
                ->has('relatedProjects', fn (Assert $related) => $related
                    ->each(fn (Assert $item) => $item
                        ->where('id', fn ($id) => $id !== $project->id)
                        ->etc()
                    )
                )
            );
        }
    }
}
