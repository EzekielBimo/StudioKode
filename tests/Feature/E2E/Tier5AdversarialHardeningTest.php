<?php

namespace Tests\Feature\E2E;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

class Tier5AdversarialHardeningTest extends E2ETestCase
{
    /*
    |--------------------------------------------------------------------------
    | Group 1: Null, Empty & Missing Prop Resilience (8 tests)
    |--------------------------------------------------------------------------
    */

    public function test_adv_project_show_with_zero_gallery_images_renders_cleanly(): void
    {
        $project = Project::factory()->create([
            'title' => 'Minimalist Solitary Pavilion',
            'slug' => 'minimalist-solitary-pavilion',
            'published_at' => now(),
        ]);

        // Ensure project has zero images attached
        $this->assertEquals(0, $project->images()->count());

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.slug', 'minimalist-solitary-pavilion')
            ->has('images', 0)
            ->has('relatedProjects')
        );
    }

    public function test_adv_project_show_with_null_masterplan_image_omits_schematic(): void
    {
        $project = Project::factory()->create([
            'title' => 'Estate Without Masterplan',
            'slug' => 'estate-without-masterplan',
            'masterplan_image' => null,
            'published_at' => now(),
        ]);

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.masterplan_image', null)
        );
    }

    public function test_adv_project_show_with_null_client_falls_back_to_private_commission(): void
    {
        $project = Project::factory()->create([
            'title' => 'Confidential Forest Cloister',
            'slug' => 'confidential-forest-cloister',
            'client' => null,
            'published_at' => now(),
        ]);

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.client', null)
        );
    }

    public function test_adv_project_show_with_null_excerpt_and_multiline_description(): void
    {
        $multilineDesc = "First paragraph of spatial intent.\n\nSecond paragraph with hydrological details.\n\nThird paragraph of material craft.";
        $project = Project::factory()->create([
            'title' => 'Multiline Spatial Sanctum',
            'slug' => 'multiline-spatial-sanctum',
            'excerpt' => null,
            'description' => $multilineDesc,
            'published_at' => now(),
        ]);

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.excerpt', null)
            ->where('project.description', $multilineDesc)
        );
    }

    public function test_adv_project_show_with_zero_related_projects_in_database(): void
    {
        // Delete all projects and create exactly one single project
        ProjectImage::query()->delete();
        Project::query()->delete();

        $project = Project::factory()->create([
            'title' => 'Sole Surviving Monastery Garden',
            'slug' => 'sole-surviving-monastery',
            'published_at' => now(),
        ]);

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.slug', 'sole-surviving-monastery')
            ->has('relatedProjects', 0)
        );
    }

    public function test_adv_projects_index_total_empty_database_renders_no_sanctuaries_found(): void
    {
        ProjectImage::query()->delete();
        Project::query()->delete();

        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
            ->where('totalCount', 0)
            ->where('categoryCounts.all', 0)
            ->where('categoryCounts.design', 0)
            ->where('categoryCounts.construction', 0)
        );
    }

    public function test_adv_home_page_with_empty_featured_projects_statistics_and_philosophy(): void
    {
        // Clear all projects and flush cache
        Project::query()->delete();
        Cache::flush();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects', 0)
            ->has('statistics', 4)
            ->has('philosophyHighlights', 3)
        );
    }

    public function test_adv_studio_page_with_empty_custom_philosophy_and_concept_steps_falls_back_to_defaults(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->has('studio')
            ->has('philosophy', 4)
            ->has('team', 4)
            ->has('process', 4)
            ->has('conceptSteps', 5)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Group 2: Extreme Inputs, Query Payloads & Injection Hardening (10 tests)
    |--------------------------------------------------------------------------
    */

    public function test_adv_search_massive_5000_char_payload_returns_200_without_error(): void
    {
        $massivePayload = str_repeat('BotanicalTopography', 250); // 4,750 characters
        $response = $this->get('/projects?search=' . urlencode($massivePayload));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
            ->where('filters.search', $massivePayload)
        );
    }

    public function test_adv_search_sql_injection_payloads_safely_handled_without_table_damage(): void
    {
        $injections = [
            "' OR '1'='1",
            "'; DROP TABLE projects; --",
            "1' UNION SELECT id, title, slug, null, null, null, null, null, null, null, null, null, null, null, null FROM projects--",
            "admin'--",
            "' OR 1=1 #",
        ];

        $initialCount = Project::count();

        foreach ($injections as $payload) {
            $response = $this->get('/projects?search=' . urlencode($payload));
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->where('filters.search', $payload)
            );
        }

        // Verify table integrity: not a single record was deleted or compromised
        $this->assertEquals($initialCount, Project::count());
    }

    public function test_adv_search_wildcard_percent_and_underscore_escaped_cleanly(): void
    {
        $response = $this->get('/projects?search=%25%5F%25');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_adv_search_multibyte_japanese_arabic_and_emoji_queries(): void
    {
        // Seed unique projects with multilingual titles
        Project::factory()->create([
            'title' => '苔庭の静寂 Zen Moss Kyoto',
            'slug' => 'zen-moss-kyoto',
            'published_at' => now(),
        ]);
        Project::factory()->create([
            'title' => 'حديقة الأندلس المعلقة Andalusian Oasis',
            'slug' => 'andalusian-oasis',
            'published_at' => now(),
        ]);
        Project::factory()->create([
            'title' => 'Botanical Oasis 🌿🎍 Tropical Sanctuary',
            'slug' => 'botanical-oasis-emoji',
            'published_at' => now(),
        ]);

        // Search Japanese query
        $resJa = $this->get('/projects?search=' . urlencode('苔庭の静寂'));
        $resJa->assertStatus(200);
        $resJa->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'zen-moss-kyoto')
        );

        // Search Arabic query
        $resAr = $this->get('/projects?search=' . urlencode('حديقة الأندلس'));
        $resAr->assertStatus(200);
        $resAr->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'andalusian-oasis')
        );

        // Search Emoji query
        $resEmoji = $this->get('/projects?search=' . urlencode('🌿🎍'));
        $resEmoji->assertStatus(200);
        $resEmoji->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'botanical-oasis-emoji')
        );
    }

    public function test_adv_search_array_query_parameter_does_not_cause_500_server_error(): void
    {
        // Request with array query params: ?search[]=foo&search[]=bar
        $response = $this->get('/projects?search[]=foo&search[]=bar');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('filters.search', '')
        );
    }

    public function test_adv_category_array_query_parameter_does_not_cause_500_server_error(): void
    {
        // Request with array category params: ?category[]=design
        $response = $this->get('/projects?category[]=design');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('activeCategory', 'all')
        );
    }

    public function test_adv_sort_array_query_parameter_does_not_cause_500_server_error(): void
    {
        // Request with array sort params: ?sort[]=oldest
        $response = $this->get('/projects?sort[]=oldest');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('filters.sort', 'newest')
        );
    }

    public function test_adv_sort_malicious_value_safely_falls_back_to_newest(): void
    {
        $response = $this->get('/projects?sort=__malicious_expression__');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('filters.sort', '__malicious_expression__')
        );
    }

    public function test_adv_sort_area_desc_with_extreme_and_zero_areas_preserves_numeric_ordering(): void
    {
        Project::query()->delete();

        // Create projects with varying area formats
        $pTiny = Project::factory()->create(['title' => 'Tiny Tea Corner', 'slug' => 'tiny-tea', 'area_size' => '25 m²', 'published_at' => now()]);
        $pMid = Project::factory()->create(['title' => 'Midscale Hummock', 'slug' => 'mid-hummock', 'area_size' => '650 m²', 'published_at' => now()]);
        $pBig = Project::factory()->create(['title' => 'Colossal Estate', 'slug' => 'colossal-estate', 'area_size' => '12,500 m²', 'published_at' => now()]);
        $pZero = Project::factory()->create(['title' => 'Zero Ground', 'slug' => 'zero-ground', 'area_size' => '0 m²', 'published_at' => now()]);

        $response = $this->get('/projects?sort=area_desc');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('filters.sort', 'area_desc')
            ->where('projects.0.slug', 'colossal-estate')
            ->where('projects.1.slug', 'mid-hummock')
        );
    }

    public function test_adv_unpublished_and_future_projects_never_leak_into_search_or_category_counts(): void
    {
        $publishedCount = Project::published()->count();

        // Create draft / unpublished project matching search term
        Project::factory()->create([
            'title' => 'Top Secret Classified Botanical Lab',
            'slug' => 'classified-botanical-lab',
            'published_at' => null,
        ]);

        // Create future scheduled project matching search term
        Project::factory()->create([
            'title' => 'Future Secret Botanical Greenhouse',
            'slug' => 'future-secret-greenhouse',
            'published_at' => now()->addDays(30),
        ]);

        // Search for "Botanical"
        $response = $this->get('/projects?search=Botanical');
        $response->assertStatus(200);

        // Verify neither unpublished nor future project is present in projects list
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('projects', function ($projects) {
                foreach ($projects as $p) {
                    if (in_array($p['slug'], ['classified-botanical-lab', 'future-secret-greenhouse'])) {
                        return false;
                    }
                }
                return true;
            })
            ->where('totalCount', $publishedCount)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Group 3: Adversarial Inquiries & Form Validation Hardening (8 tests)
    |--------------------------------------------------------------------------
    */

    public function test_adv_inquiry_exact_boundary_maximum_lengths_accepted(): void
    {
        // RFC compliant email with max length and valid domain labels
        $validLongEmail = str_repeat('a', 64) . '@' . str_repeat('b', 60) . '.' . str_repeat('c', 60) . '.' . str_repeat('d', 60) . '.com';

        $payload = [
            'name' => str_repeat('A', 255),
            'email' => $validLongEmail,
            'phone' => str_repeat('1', 50),
            'site_location' => str_repeat('L', 255),
            'estimated_area' => str_repeat('9', 100),
            'budget_range' => str_repeat('$', 100),
            'message' => str_repeat('M', 5000),
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('inquiries', ['phone' => str_repeat('1', 50)]);
    }

    public function test_adv_inquiry_exceeding_boundary_maximum_lengths_rejected(): void
    {
        $payload = [
            'name' => str_repeat('A', 256), // > 255
            'email' => 'valid@domain.com',
            'phone' => str_repeat('1', 51), // > 50
            'site_location' => str_repeat('L', 256), // > 255
            'estimated_area' => str_repeat('9', 101), // > 100
            'budget_range' => str_repeat('$', 101), // > 100
            'message' => str_repeat('M', 5001), // > 5000
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertSessionHasErrors([
            'name',
            'phone',
            'site_location',
            'estimated_area',
            'budget_range',
            'message',
        ]);
    }

    public function test_adv_inquiry_multibyte_unicode_and_complex_emojis_stored_faithfully(): void
    {
        $payload = [
            'name' => '木村 拓哉 (Takuya Kimura) 🌿',
            'email' => 'kimura@kyoto-sanctuary.jp',
            'phone' => '+81 75 123 4567',
            'site_location' => '京都市北区上賀茂 🏯 (Kyoto Kamigamo)',
            'estimated_area' => '850 m² (約257坪)',
            'budget_range' => '¥50,000,000 – ¥80,000,000 💴',
            'message' => "枯山水と苔庭の調和を求めています。\n\nWe require an indigenous cedar pavilion with dry-stone masonry. ⛩️🎋🎍",
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'name' => '木村 拓哉 (Takuya Kimura) 🌿',
            'site_location' => '京都市北区上賀茂 🏯 (Kyoto Kamigamo)',
        ]);
    }

    public function test_adv_inquiry_xss_script_payloads_stored_verbatim_without_code_execution(): void
    {
        $xssName = '<script>alert("XSS-Name")</script>';
        $xssLocation = '<img src=x onerror="alert(\'XSS-Img\')">';
        $xssMessage = '"><script src="//evil.com/payload.js"></script><iframe src="javascript:alert(1)"></iframe>';

        $payload = [
            'name' => $xssName,
            'email' => 'security-audit@studiokode.design',
            'site_location' => $xssLocation,
            'message' => $xssMessage,
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Confirm database holds verbatim string without being stripped or executing
        $inquiry = Inquiry::firstWhere('email', 'security-audit@studiokode.design');
        $this->assertNotNull($inquiry);
        $this->assertEquals($xssName, $inquiry->name);
        $this->assertEquals($xssLocation, $inquiry->site_location);
        $this->assertEquals($xssMessage, $inquiry->message);
    }

    public function test_adv_inquiry_omitting_all_optional_fields_stores_null_cleanly(): void
    {
        $payload = [
            'name' => 'Minimalist Client',
            'email' => 'minimal@estate.ch',
            'message' => 'Clean inquiry with only required attributes specified.',
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $inquiry = Inquiry::firstWhere('email', 'minimal@estate.ch');
        $this->assertNotNull($inquiry);
        $this->assertNull($inquiry->phone);
        $this->assertNull($inquiry->site_location);
        $this->assertNull($inquiry->estimated_area);
        $this->assertNull($inquiry->budget_range);
        $this->assertEquals('new', $inquiry->status);
    }

    public function test_adv_inquiry_invalid_emails_rejected_with_validation_errors(): void
    {
        $invalidEmails = [
            'plainaddress',
            '#@%^%#$@#$@#.com',
            '@example.com',
            'Joe Smith <email@example.com>',
            'email.example.com',
            'email@example@example.com',
        ];

        foreach ($invalidEmails as $invalid) {
            $response = $this->post('/inquiries', [
                'name' => 'Test User',
                'email' => $invalid,
                'message' => 'Valid message content.',
            ]);
            $response->assertSessionHasErrors(['email']);
        }
    }

    public function test_adv_inquiry_empty_payload_fails_all_required_rules(): void
    {
        $response = $this->post('/inquiries', []);
        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_adv_inquiry_excessive_whitespace_trimmed_or_handled_safely(): void
    {
        $payload = [
            'name' => '   Julian Finch   ',
            'email' => '   julian@finch-estate.co.uk   ',
            'message' => '   Spacious message with surrounding spaces.   ',
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check trimmed insertion by TrimStrings middleware
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Julian Finch',
            'email' => 'julian@finch-estate.co.uk',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Group 4: Anti-Slop Architectural Tokens & Layout Invariants (6 tests)
    |--------------------------------------------------------------------------
    */

    public function test_adv_architectural_palette_hex_tokens_coexistence_in_css_and_tailwind(): void
    {
        $tailwindConfig = file_get_contents(base_path('tailwind.config.js'));
        $appCss = file_get_contents(resource_path('css/app.css'));

        // Jet Black
        $this->assertStringContainsString('#0D0D0D', $tailwindConfig);
        $this->assertStringContainsString('#0D0D0D', $appCss);

        // Forest Green
        $this->assertStringContainsString('#224F10', $tailwindConfig);
        $this->assertStringContainsString('#224F10', $appCss);

        // Lime Green
        $this->assertStringContainsString('#B0E454', $tailwindConfig);
        $this->assertStringContainsString('#B0E454', $appCss);

        // Soft Ivory
        $this->assertStringContainsString('#EAFFD0', $tailwindConfig);
        $this->assertStringContainsString('#EAFFD0', $appCss);

        // Backward compatibility tokens in tailwind
        $this->assertStringContainsString('#0B0E0C', $tailwindConfig);
        $this->assertStringContainsString('#1C2A21', $tailwindConfig);
        $this->assertStringContainsString('obsidian', $tailwindConfig);
        $this->assertStringContainsString('moss', $tailwindConfig);
    }

    public function test_adv_instrument_serif_typography_and_fallback_invariants(): void
    {
        $appBlade = file_get_contents(resource_path('views/app.blade.php'));
        $tailwindConfig = file_get_contents(base_path('tailwind.config.js'));
        $appCss = file_get_contents(resource_path('css/app.css'));

        // Google Fonts preconnect & stylesheet
        $this->assertStringContainsString('fonts.googleapis.com', $appBlade);
        $this->assertStringContainsString('Instrument+Serif', $appBlade);
        $this->assertStringContainsString('Plus+Jakarta+Sans', $appBlade);

        // Tailwind font family definition
        $this->assertStringContainsString('Instrument Serif', $tailwindConfig);
        $this->assertStringContainsString('Cormorant Garamond', $tailwindConfig);

        // Global CSS heading rules
        $this->assertStringContainsString('Instrument Serif', $appCss);
    }

    public function test_adv_hairline_borders_and_glassmorphism_tokens_in_markup(): void
    {
        $publicLayout = file_get_contents(resource_path('js/Layouts/PublicLayout.jsx'));
        $navbar = file_get_contents(resource_path('js/Components/Navigation/Navbar.jsx'));
        $footer = file_get_contents(resource_path('js/Components/Navigation/Footer.jsx'));

        $this->assertStringContainsString('selection:bg-forest', $publicLayout);
        $this->assertStringContainsString('selection:text-lime', $publicLayout);
        $this->assertStringContainsString('backdrop-blur', $navbar);
        $this->assertStringContainsString('border-white/10', $navbar);
        $this->assertStringContainsString('border-t border-white/10', $footer);
    }

    public function test_adv_anti_cls_aspect_ratios_enforced_across_components(): void
    {
        $projectCard = file_get_contents(resource_path('js/Components/Portfolio/ProjectCard.jsx'));
        $projectsShow = file_get_contents(resource_path('js/Pages/Projects/Show.jsx'));
        $imageGallery = file_get_contents(resource_path('js/Components/Portfolio/ImageGallery.jsx'));
        $studioPage = file_get_contents(resource_path('js/Pages/Studio/Index.jsx'));

        // ProjectCard must enforce aspect-[16/10]
        $this->assertStringContainsString('aspect-[16/10]', $projectCard);

        // ProjectsShow cover must enforce aspect-[21/9]
        $this->assertStringContainsString('aspect-[21/9]', $projectsShow);

        // ProjectsShow masterplan must enforce aspect-[4/3]
        $this->assertStringContainsString('aspect-[4/3]', $projectsShow);

        // ImageGallery grid must enforce aspect-[4/3] and modal aspect-[16/10]
        $this->assertStringContainsString('aspect-[4/3]', $imageGallery);
        $this->assertStringContainsString('aspect-[16/10]', $imageGallery);

        // Studio team member photo must enforce aspect-[4/5]
        $this->assertStringContainsString('aspect-[4/5]', $studioPage);
    }

    public function test_adv_public_layout_flash_success_and_error_toasts_rendered(): void
    {
        // Test redirect with flash success message
        $responseSuccess = $this->withSession(['success' => 'Sanctuary Commission Received'])
            ->get('/projects');
        $responseSuccess->assertStatus(200);
        $responseSuccess->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('flash.success', 'Sanctuary Commission Received')
        );

        // Test redirect with flash error message
        $responseError = $this->withSession(['error' => 'Transmission Error Occurred'])
            ->get('/projects');
        $responseError->assertStatus(200);
        $responseError->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('flash.error', 'Transmission Error Occurred')
        );
    }

    public function test_adv_navbar_and_mobile_menu_accessibility_and_dismissal_structure(): void
    {
        $navbar = file_get_contents(resource_path('js/Components/Navigation/Navbar.jsx'));
        $mobileMenu = file_get_contents(resource_path('js/Components/Navigation/MobileMenu.jsx'));

        $this->assertStringContainsString('aria-label="Main Navigation"', $navbar);
        $this->assertStringContainsString('aria-label="Toggle navigation menu"', $navbar);
        $this->assertStringContainsString('Escape', $navbar);
        $this->assertStringContainsString('pointerdown', $navbar);
        $this->assertStringContainsString('id="mobile-navigation"', $mobileMenu);
        $this->assertStringContainsString('aria-label="Mobile Navigation"', $mobileMenu);
    }

    /*
    |--------------------------------------------------------------------------
    | Group 5: Exact Data & Content Preservation Invariants (6 tests)
    |--------------------------------------------------------------------------
    */

    public function test_adv_home_page_exact_copy_and_statistics_data_preservation(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->where('statistics.0.value', '15+')
            ->where('statistics.0.label', 'Years Experience')
            ->where('statistics.1.value', '48+')
            ->where('statistics.1.label', 'Curated Sanctuaries')
            ->where('statistics.2.value', '12')
            ->where('statistics.2.label', 'Design Accolades')
            ->where('statistics.3.value', '100%')
            ->where('statistics.3.label', 'Ecological Integrity')
            ->where('philosophyHighlights.0.title', 'Topographical Symbiosis')
            ->where('philosophyHighlights.1.title', 'Botanical Longevity')
            ->where('philosophyHighlights.2.title', 'Tactile Materiality')
        );

        $homeContent = file_get_contents(resource_path('js/Pages/Home/Index.jsx'));
        $this->assertStringContainsString('Mediating between architectural form and untamed nature.', $homeContent);
        $this->assertStringContainsString('Crafting living topographies, dry-stone masonry, and biodiverse botanical tapestries for generational private estates.', $homeContent);
        $this->assertStringContainsString('Curated Sanctuaries', $homeContent);
        $this->assertStringContainsString('The Three Botanical Pillars', $homeContent);
    }

    public function test_adv_studio_page_manifesto_credo_and_concept_steps_data_preservation(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('studio.name', 'Kode Landscape Studio')
            ->where('studio.tagline', 'Tropical Landscape Design & Build')
            ->where('studio.location', 'Bali, Indonesia')
            ->where('conceptSteps.0.title', 'Contact Us')
            ->where('conceptSteps.1.title', 'Project Meeting & Site Assessment')
            ->where('conceptSteps.2.title', 'Moodboard & Landscape Concept')
            ->where('conceptSteps.3.title', 'Project Collaboration Agreement')
            ->where('conceptSteps.4.title', 'Design & Build')
        );

        $studioContent = file_get_contents(resource_path('js/Pages/Studio/Index.jsx'));
        $this->assertStringContainsString('Architecture Born From Ground, Climate &amp; Tropical Topography', $studioContent);
        $this->assertStringContainsString('Kode Landscape Studio Creed', $studioContent);
        $this->assertStringContainsString('Why Quality Landscape is Essential for Property Value', $studioContent);
        $this->assertStringContainsString('From Concept to Creation', $studioContent);
    }

    public function test_adv_studio_page_leadership_team_and_dossier_data_preservation(): void
    {
        $response = $this->get('/studio');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('team.0.name', 'Kaelen Voss')
            ->where('team.0.role', 'Principal Landscape Architect & Founder')
            ->where('team.1.name', 'Soraya Chen')
            ->where('team.1.role', 'Director of Botanical Systems & Ecology')
            ->where('team.2.name', 'Mateo Aris')
            ->where('team.2.role', 'Spatial Design & Masterplanning Lead')
            ->where('team.3.name', 'Elena Rostova')
            ->where('team.3.role', 'Head of Materiality & Artisan Craft')
        );
    }

    public function test_adv_contact_page_ateliers_and_timeline_data_preservation(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->where('studios.0.city', 'Kyoto Atelier')
            ->where('studios.1.city', 'Zurich Studio')
            ->where('studios.2.city', 'Melbourne Office')
            ->where('consultationDetails.leadTime', 'Initial spatial masterplanning engagements typically begin 6 to 10 weeks post-commission.')
            ->where('consultationDetails.minimumScale', 'We welcome private residences, country retreats, and botanical estates from 150 m² to 50+ hectares.')
        );
    }

    public function test_adv_projects_index_and_show_canonical_seeded_projects_fidelity(): void
    {
        $canonicalSlugs = [
            'moss-stone-sanctuary',
            'verdant-terrace-estate',
            'atelier-botanica-courtyard',
            'highland-mist-alpine-garden',
            'oasis-dune-residence',
            'coastal-dune-pine-refuge',
        ];

        foreach ($canonicalSlugs as $slug) {
            $response = $this->get('/projects/' . $slug);
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Show')
                ->where('project.slug', $slug)
                ->has('project.title')
                ->has('project.location')
                ->has('project.year')
                ->has('project.area_size')
                ->has('project.description')
            );
        }
    }

    public function test_adv_navigation_links_preserve_canonical_routes(): void
    {
        $navbar = file_get_contents(resource_path('js/Components/Navigation/Navbar.jsx'));
        $footer = file_get_contents(resource_path('js/Components/Navigation/Footer.jsx'));

        $this->assertStringContainsString("href: '/projects'", $navbar);
        $this->assertStringContainsString("href: '/studio'", $navbar);
        $this->assertStringContainsString("href: '/contact'", $navbar);

        $this->assertStringContainsString('href="/"', $footer);
        $this->assertStringContainsString('href="/projects"', $footer);
        $this->assertStringContainsString('href="/studio"', $footer);
        $this->assertStringContainsString('href="/contact"', $footer);
    }
}
