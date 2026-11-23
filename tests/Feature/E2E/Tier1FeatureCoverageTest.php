<?php

namespace Tests\Feature\E2E;

use App\Models\Project;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

class Tier1FeatureCoverageTest extends E2ETestCase
{
    /*
    |--------------------------------------------------------------------------
    | Feature 1: Color Palette Tokens (Jet Black, Forest Green, Lime, Soft Ivory)
    |--------------------------------------------------------------------------
    */

    public function test_f1_tailwind_defines_jet_black_token(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertMatchesRegularExpression("/(['\"]?)jet(['\"]?)\s*:\s*\{[^}]*#0D0D0D/i", $tailwind, 'Tailwind must configure jet palette with #0D0D0D');
    }

    public function test_f1_tailwind_defines_forest_green_token(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertMatchesRegularExpression("/(['\"]?)forest(['\"]?)\s*:\s*\{[^}]*#224F10/i", $tailwind, 'Tailwind must configure forest palette with #224F10');
    }

    public function test_f1_tailwind_defines_lime_green_token(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertMatchesRegularExpression("/(['\"]?)lime(['\"]?)\s*:\s*\{[^}]*#B0E454/i", $tailwind, 'Tailwind must configure lime palette with #B0E454');
    }

    public function test_f1_tailwind_defines_soft_ivory_token(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertMatchesRegularExpression("/(['\"]?)ivory(['\"]?)\s*:\s*\{[^}]*#EAFFD0/i", $tailwind, 'Tailwind must configure ivory palette with #EAFFD0');
    }

    public function test_f1_tailwind_preserves_backward_compatible_tokens(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertStringContainsString('obsidian', $tailwind, 'Tailwind must preserve obsidian token key');
        $this->assertStringContainsString('moss', $tailwind, 'Tailwind must preserve moss token key');
        $this->assertStringContainsString('#0B0E0C', $tailwind, 'Tailwind must preserve legacy obsidian hex #0B0E0C');
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 2: Instrument Serif Typography
    |--------------------------------------------------------------------------
    */

    public function test_f2_blade_loads_instrument_serif_from_google_fonts(): void
    {
        $blade = file_get_contents(resource_path('views/app.blade.php'));
        $this->assertStringContainsString('Instrument+Serif', $blade, 'app.blade.php must include Google Fonts link for Instrument Serif');
    }

    public function test_f2_tailwind_configures_instrument_serif_font_family(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertStringContainsString('Instrument Serif', $tailwind, 'tailwind.config.js must configure Instrument Serif in fontFamily.serif');
    }

    public function test_f2_css_applies_serif_to_headings(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertMatchesRegularExpression('/h1,\s*h2,\s*h3,\s*h4,\s*\.font-serif/i', $css, 'app.css must target heading elements with font-serif');
        $this->assertStringContainsString('Instrument Serif', $css, 'app.css must apply Instrument Serif to headings');
    }

    public function test_f2_blade_retains_cormorant_fallback(): void
    {
        $blade = file_get_contents(resource_path('views/app.blade.php'));
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $this->assertStringContainsString('Cormorant Garamond', $tailwind, 'Tailwind must configure Cormorant Garamond fallback');
        $this->assertStringContainsString('Cormorant Garamond', $blade, 'app.blade.php must retain Cormorant Garamond reference');
    }

    public function test_f2_live_home_renders_font_preload_in_html(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Instrument+Serif', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 3: Global CSS & Theme Setup
    |--------------------------------------------------------------------------
    */

    public function test_f3_blade_declares_dark_color_scheme_meta(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('<meta name="color-scheme" content="dark">', false);
    }

    public function test_f3_blade_declares_theme_color_meta(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('<meta name="theme-color"', false);
    }

    public function test_f3_global_css_defines_dark_canvas(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('color-scheme: dark', $css);
        $this->assertStringContainsString('#0D0D0D', $css, 'Global CSS must define Jet Black (#0D0D0D) canvas token');
        $this->assertStringContainsString('#0B0E0C', $css, 'Global CSS must preserve legacy dark canvas token (#0B0E0C) for coexistence');
    }

    public function test_f3_global_css_defines_selection_styles(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('::selection', $css, 'Global CSS must style ::selection');
    }

    public function test_f3_app_jsx_configures_progress_bar_color(): void
    {
        $appJsx = file_get_contents(resource_path('js/app.jsx'));
        $this->assertStringContainsString('progress:', $appJsx);
        $this->assertStringContainsString('#B0E454', $appJsx, 'Inertia progress bar color must be configured to Lime Green (#B0E454)');
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 4: Chrome & Navigation Layouts
    |--------------------------------------------------------------------------
    */

    public function test_f4_public_layout_wraps_content_in_dark_canvas(): void
    {
        $layout = file_get_contents(resource_path('js/Layouts/PublicLayout.jsx'));
        $this->assertStringContainsString('min-h-screen', $layout);
        $this->assertStringContainsString('flex flex-col', $layout);
        $this->assertStringContainsString('<Navbar', $layout);
        $this->assertStringContainsString('<Footer', $layout);
    }

    public function test_f4_navbar_declares_canonical_route_links(): void
    {
        $navbar = file_get_contents(resource_path('js/Components/Navigation/Navbar.jsx'));
        $this->assertStringContainsString('/projects', $navbar);
        $this->assertStringContainsString('/studio', $navbar);
        $this->assertStringContainsString('/contact', $navbar);
    }

    public function test_f4_navbar_implements_glass_or_pill_container(): void
    {
        $navbar = file_get_contents(resource_path('js/Components/Navigation/Navbar.jsx'));
        $this->assertStringContainsString('rounded-full', $navbar, 'Navbar must implement floating pill container');
        $this->assertStringContainsString('backdrop-blur', $navbar, 'Navbar must implement glassmorphism backdrop-blur');
    }

    public function test_f4_footer_renders_brand_and_atelier_columns(): void
    {
        $footer = file_get_contents(resource_path('js/Components/Navigation/Footer.jsx'));
        $this->assertStringContainsString('StudioKode', $footer);
        $this->assertStringContainsString('Kyoto', $footer);
        $this->assertStringContainsString('Z&uuml;rich', $footer, 'Footer must reference Zurich atelier');
        $this->assertStringContainsString('Melbourne', $footer);
    }

    public function test_f4_mobile_menu_provides_accessible_drawer(): void
    {
        $mobileMenu = file_get_contents(resource_path('js/Components/Navigation/MobileMenu.jsx'));
        $this->assertStringContainsString('aria-label="Mobile Navigation"', $mobileMenu);
        $this->assertStringContainsString('navItems', $mobileMenu);
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 5: Core UI Primitives
    |--------------------------------------------------------------------------
    */

    public function test_f5_button_supports_primary_secondary_and_outline_variants(): void
    {
        $button = file_get_contents(resource_path('js/Components/UI/Button.jsx'));
        $this->assertStringContainsString('primary', $button);
        $this->assertStringContainsString('secondary', $button);
        $this->assertStringContainsString('outline', $button);
    }

    public function test_f5_badge_supports_foliage_and_frosted_variants(): void
    {
        $badge = file_get_contents(resource_path('js/Components/UI/Badge.jsx'));
        $this->assertStringContainsString('foliage', $badge);
        $this->assertStringContainsString('neutral', $badge, 'Badge must support neutral variant');
        $this->assertStringContainsString('outline', $badge, 'Badge must support outline variant');
    }

    public function test_f5_project_card_preserves_anti_cls_aspect_ratio(): void
    {
        $card = file_get_contents(resource_path('js/Components/Portfolio/ProjectCard.jsx'));
        $this->assertStringContainsString('aspect-[16/10]', $card, 'ProjectCard must enforce aspect-[16/10] to prevent CLS');
    }

    public function test_f5_modal_implements_backdrop_blur_and_close_handler(): void
    {
        $modal = file_get_contents(resource_path('js/Components/UI/Modal.jsx'));
        $this->assertStringContainsString('backdrop-blur', $modal);
        $this->assertStringContainsString('onClose', $modal);
    }

    public function test_f5_filter_bar_declares_search_and_sort_controls(): void
    {
        $filterBar = file_get_contents(resource_path('js/Components/Portfolio/FilterBar.jsx'));
        $this->assertStringContainsString('onSearchChange', $filterBar);
        $this->assertStringContainsString('onSortChange', $filterBar);
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 6: Home Page Redesign
    |--------------------------------------------------------------------------
    */

    public function test_f6_home_returns_200_and_renders_home_index_component(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Home/Index'));
    }

    public function test_f6_home_delivers_featured_projects_prop(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects', 3)
        );
    }

    public function test_f6_home_delivers_studio_statistics_prop(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('statistics', 4)
            ->where('statistics.0.value', '15+')
            ->where('statistics.1.value', '48+')
        );
    }

    public function test_f6_home_delivers_philosophy_highlights_prop(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('philosophyHighlights', 3)
            ->where('philosophyHighlights.0.number', '01')
        );
    }

    public function test_f6_home_view_wraps_in_public_layout(): void
    {
        $homeIndex = file_get_contents(resource_path('js/Pages/Home/Index.jsx'));
        $this->assertStringContainsString('PublicLayout', $homeIndex);
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 7: Projects Index Page Redesign
    |--------------------------------------------------------------------------
    */

    public function test_f7_projects_index_returns_200_and_renders_inertia(): void
    {
        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Projects/Index'));
    }

    public function test_f7_projects_index_delivers_projects_collection(): void
    {
        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_f7_projects_index_delivers_category_counts(): void
    {
        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('categoryCounts.all')
            ->has('categoryCounts.design')
            ->has('categoryCounts.construction')
        );
    }

    public function test_f7_projects_index_delivers_filters_state(): void
    {
        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('filters.search')
            ->has('filters.sort')
        );
    }

    public function test_f7_projects_index_preserves_responsive_grid_layout(): void
    {
        $projectsIndex = file_get_contents(resource_path('js/Pages/Projects/Index.jsx'));
        $this->assertTrue(
            str_contains($projectsIndex, 'grid-cols-1') && str_contains($projectsIndex, 'lg:grid-cols-3'),
            'Projects index must preserve responsive 3-column grid'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 8: Projects Show Page Redesign
    |--------------------------------------------------------------------------
    */

    public function test_f8_projects_show_returns_200_for_seeded_slug(): void
    {
        $project = Project::firstOrFail();
        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Projects/Show'));
    }

    public function test_f8_projects_show_delivers_project_attributes(): void
    {
        $project = Project::firstOrFail();
        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('project', fn (Assert $p) => $p
                ->where('slug', $project->slug)
                ->where('title', $project->title)
                ->has('area_size')
                ->has('location')
                ->etc()
            )
        );
    }

    public function test_f8_projects_show_delivers_images_collection(): void
    {
        $project = Project::firstOrFail();
        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('images')
        );
    }

    public function test_f8_projects_show_delivers_related_projects(): void
    {
        $project = Project::firstOrFail();
        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('relatedProjects')
        );
    }

    public function test_f8_projects_show_preserves_metadata_grid_layout(): void
    {
        $showJsx = file_get_contents(resource_path('js/Pages/Projects/Show.jsx'));
        $this->assertStringContainsString('grid-cols-2', $showJsx, 'Projects show must declare 2-column mobile metadata grid');
        $this->assertStringContainsString('md:grid-cols-4', $showJsx, 'Projects show must declare 4-column desktop metadata grid');
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 9: Studio Page Redesign
    |--------------------------------------------------------------------------
    */

    public function test_f9_studio_page_returns_200_and_renders_inertia(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Studio/Index'));
    }

    public function test_f9_studio_page_delivers_studio_profile(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('studio.name', 'Kode Landscape Studio')
            ->where('studio.founded', 2025)
            ->where('studio.location', 'Bali, Indonesia')
        );
    }

    public function test_f9_studio_page_delivers_four_philosophy_pillars(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->has('philosophy', 4)
            ->where('philosophy.0.number', '01')
            ->where('philosophy.1.number', '02')
        );
    }

    public function test_f9_studio_page_delivers_five_concept_steps(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->has('conceptSteps', 5)
            ->where('conceptSteps.0.title', 'Contact Us')
            ->where('conceptSteps.4.title', 'Design & Build')
        );
    }

    public function test_f9_studio_page_delivers_team_and_accolades(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->has('team', 4)
            ->has('accolades', 3)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 10: Contact Page Redesign
    |--------------------------------------------------------------------------
    */

    public function test_f10_contact_page_returns_200_and_renders_inertia(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Contact/Index'));
    }

    public function test_f10_contact_page_delivers_three_ateliers(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('studios', 3)
            ->where('studios.0.city', 'Kyoto Atelier')
            ->where('studios.1.city', 'Zurich Studio')
            ->where('studios.2.city', 'Melbourne Office')
        );
    }

    public function test_f10_contact_page_delivers_consultation_details(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('consultationDetails.leadTime')
            ->has('consultationDetails.minimumScale')
        );
    }

    public function test_f10_inquiry_post_stores_new_inquiry_record(): void
    {
        $payload = [
            'name' => 'Claire Delacroix',
            'email' => 'claire@delacroix.ch',
            'message' => 'Requesting botanical landscape assessment for alpine villa.',
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Claire Delacroix',
            'email' => 'claire@delacroix.ch',
            'status' => 'new',
        ]);
    }

    public function test_f10_inquiry_post_redirects_back_with_flash_success(): void
    {
        $payload = [
            'name' => 'Alain Vane',
            'email' => 'alain@vane.com',
            'message' => 'Inquiry regarding courtyard landscape design.',
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 11: Strict Data Preservation
    |--------------------------------------------------------------------------
    */

    public function test_f11_canonical_studio_name_and_location_preserved(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('studio.name', 'Kode Landscape Studio')
            ->where('studio.location', 'Bali, Indonesia')
        );
    }

    public function test_f11_canonical_statistics_labels_and_values_preserved(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->where('statistics.0.label', 'Years Experience')
            ->where('statistics.1.label', 'Curated Sanctuaries')
            ->where('statistics.2.label', 'Design Accolades')
            ->where('statistics.3.label', 'Ecological Integrity')
        );
    }

    public function test_f11_canonical_five_step_methodology_titles_preserved(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('conceptSteps.0.title', 'Contact Us')
            ->where('conceptSteps.1.title', 'Project Meeting & Site Assessment')
            ->where('conceptSteps.2.title', 'Moodboard & Landscape Concept')
            ->where('conceptSteps.3.title', 'Project Collaboration Agreement')
            ->where('conceptSteps.4.title', 'Design & Build')
        );
    }

    public function test_f11_canonical_project_attributes_preserved(): void
    {
        $projects = Project::published()->get();
        $this->assertNotEmpty($projects, 'Database seeder must provision projects');
        foreach ($projects as $project) {
            $this->assertNotEmpty($project->title);
            $this->assertNotEmpty($project->slug);
            $this->assertNotEmpty($project->location);
        }
    }

    public function test_f11_canonical_atelier_addresses_preserved(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->where('studios.0.email', 'kyoto@studiokode.design')
            ->where('studios.1.email', 'zurich@studiokode.design')
            ->where('studios.2.email', 'melbourne@studiokode.design')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 12: Build & Test Integrity
    |--------------------------------------------------------------------------
    */

    public function test_f12_package_json_defines_build_and_dev_scripts(): void
    {
        $packageJson = json_decode(file_get_contents(base_path('package.json')), true);
        $this->assertIsArray($packageJson);
        $this->assertArrayHasKey('scripts', $packageJson);
        $this->assertArrayHasKey('build', $packageJson['scripts']);
        $this->assertStringContainsString('vite build', $packageJson['scripts']['build']);
    }

    public function test_f12_vite_config_exists_and_loads_plugins(): void
    {
        $viteConfig = file_get_contents(base_path('vite.config.js'));
        $this->assertStringContainsString('laravel-vite-plugin', $viteConfig);
        $this->assertStringContainsString('@vitejs/plugin-react', $viteConfig);
    }

    public function test_f12_database_seeder_executes_idempotently(): void
    {
        // Calling seeder again within the transaction should succeed cleanly
        $this->seed(DatabaseSeeder::class);
        $this->assertGreaterThan(0, Project::count());
    }

    public function test_f12_phpunit_configuration_uses_sqlite_in_memory(): void
    {
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_f12_root_blade_invokes_vite_entrypoints(): void
    {
        $blade = file_get_contents(resource_path('views/app.blade.php'));
        $this->assertStringContainsString("@vite(['resources/css/app.css', 'resources/js/app.jsx'])", $blade);
    }
}
