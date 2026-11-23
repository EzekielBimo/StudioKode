<?php

namespace Tests\Feature\E2E;

use App\Models\Inquiry;
use App\Models\Project;
use Inertia\Testing\AssertableInertia as Assert;

class Tier4RealWorldScenariosTest extends E2ETestCase
{
    /**
     * Scenario 1: Prospective Client Commission Inquiry Journey.
     * Flow: / -> /contact -> POST /inquiries -> Database verification.
     */
    public function test_scenario_1_prospective_client_commission_journey(): void
    {
        // 1. Landing on Home
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects')
            ->has('statistics')
        );

        // 2. Navigating to Contact page
        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(200);
        $contactResponse->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('studios', 3)
            ->has('consultationDetails.leadTime')
        );

        // 3. Submitting commission brief
        $briefPayload = [
            'name' => 'Lady Genevieve Sterling',
            'email' => 'g.sterling@highland-estates.co.uk',
            'phone' => '+44 20 7946 0912',
            'site_location' => 'Inverness-shire, Scottish Highlands',
            'estimated_area' => '12,500 m²',
            'budget_range' => '£250,000 – £500,000',
            'message' => 'Seeking a contemplative lochside sanctuary incorporating native heather, dry-stone granite terraces, and dark timber pavilions.',
        ];

        $postResponse = $this->from('/contact')->post('/inquiries', $briefPayload);
        $postResponse->assertRedirect('/contact');
        $postResponse->assertSessionHas('success');

        // 4. Inspecting redirected Contact page with flash
        $redirectResponse = $this->get('/contact');
        $redirectResponse->assertStatus(200);
        $redirectResponse->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->has('flash.success')
        );

        // 5. Database record verification
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Lady Genevieve Sterling',
            'email' => 'g.sterling@highland-estates.co.uk',
            'site_location' => 'Inverness-shire, Scottish Highlands',
            'status' => 'new',
        ]);
    }

    /**
     * Scenario 2: Architectural Portfolio Exploration & Filtering Journey.
     * Flow: /projects -> filter category -> search keyword -> view detail -> related link.
     */
    public function test_scenario_2_portfolio_exploration_and_filtering_journey(): void
    {
        // 1. Visit portfolio index
        $indexResponse = $this->get('/projects');
        $indexResponse->assertStatus(200);
        $totalPublished = Project::published()->count();
        $indexResponse->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('totalCount', $totalPublished)
        );

        // 2. Filter by category 'Project Design'
        $categoryResponse = $this->get('/projects?category=design');
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('activeCategory', 'design')
        );

        // 3. Search for Kyoto
        $searchResponse = $this->get('/projects?category=design&search=Kyoto');
        $searchResponse->assertStatus(200);
        $searchResponse->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'moss-stone-sanctuary')
        );

        // 4. View project detail
        $showResponse = $this->get('/projects/moss-stone-sanctuary');
        $showResponse->assertStatus(200);
        $showResponse->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.slug', 'moss-stone-sanctuary')
            ->has('images')
            ->has('relatedProjects')
        );

        // 5. Follow related project link
        $related = Project::published()->where('slug', '!=', 'moss-stone-sanctuary')->firstOrFail();
        $relatedResponse = $this->get('/projects/' . $related->slug);
        $relatedResponse->assertStatus(200);
        $relatedResponse->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.slug', $related->slug)
        );
    }

    /**
     * Scenario 3: Studio Practice, Accolades & Credo Verification Journey.
     * Flow: /studio -> inspect manifesto, pillars, team, accolades -> CTA /contact.
     */
    public function test_scenario_3_studio_practice_credo_and_accolades_journey(): void
    {
        // 1. Visit studio page
        $studioResponse = $this->get('/studio');
        $studioResponse->assertStatus(200);

        // 2-6. Verify studio profile, philosophy, concept steps, team, accolades
        $studioResponse->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('studio.name', 'Kode Landscape Studio')
            ->where('studio.founded', 2025)
            ->has('philosophy', 4)
            ->has('conceptSteps', 5)
            ->has('team', 4)
            ->has('accolades', 3)
            ->where('team.0.name', 'Kaelen Voss')
            ->where('team.1.name', 'Soraya Chen')
            ->where('team.2.name', 'Mateo Aris')
            ->where('team.3.name', 'Elena Rostova')
        );

        // 7. CTA links to contact page
        $contactResponse = $this->get('/contact');
        $contactResponse->assertStatus(200);
        $contactResponse->assertInertia(fn (Assert $page) => $page->component('Contact/Index'));
    }

    /**
     * Scenario 4: Brand Aesthetic, Typography & Token Harmony Audit.
     * Audits all 5 public endpoints for meta tags, Google Fonts, and token consistency.
     */
    public function test_scenario_4_brand_aesthetic_and_token_harmony_audit(): void
    {
        $project = Project::firstOrFail();
        $routes = [
            '/' => 'Home/Index',
            '/projects' => 'Projects/Index',
            '/projects/' . $project->slug => 'Projects/Show',
            '/studio' => 'Studio/Index',
            '/contact' => 'Contact/Index',
        ];

        foreach ($routes as $route => $component) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page->component($component));

            // Verify dark theme meta and font preconnects in rendered HTML
            $response->assertSee('<meta name="color-scheme" content="dark">', false);
            $response->assertSee('<meta name="theme-color"', false);
            $response->assertSee('Instrument+Serif', false);
        }

        // Verify redesign tokens in Tailwind and CSS individually using E2ETestCase helper
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertContainsRedesignTokens($tailwind, 'tailwind.config.js');
        $this->assertContainsRedesignTokens($css, 'resources/css/app.css');
        $this->assertStringContainsString('Instrument Serif', $tailwind);
        $this->assertStringContainsString('Instrument Serif', $css);
    }

    /**
     * Scenario 5: Mobile Viewport Chrome & Consultation Journey.
     * Emulates mobile client headers traversing routes and submitting brief.
     */
    public function test_scenario_5_mobile_viewport_and_consultation_journey(): void
    {
        $mobileHeaders = [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
        ];

        // 1. Mobile visit to Home
        $home = $this->withHeaders($mobileHeaders)->get('/');
        $home->assertStatus(200);

        // 2. Mobile visit to Projects
        $projects = $this->withHeaders($mobileHeaders)->get('/projects');
        $projects->assertStatus(200);

        // 3. Mobile visit to Contact
        $contact = $this->withHeaders($mobileHeaders)->get('/contact');
        $contact->assertStatus(200);

        // 4. Mobile brief submission
        $mobileBrief = [
            'name' => 'Karin Lindqvist',
            'email' => 'karin@nordic-estates.se',
            'site_location' => 'Stockholm Archipelago, Sweden',
            'message' => 'Consultation for rocky shoreline retreat with native pine and moss terraces.',
        ];

        $post = $this->withHeaders($mobileHeaders)->from('/contact')->post('/inquiries', $mobileBrief);
        $post->assertRedirect('/contact');
        $post->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Karin Lindqvist',
            'email' => 'karin@nordic-estates.se',
            'status' => 'new',
        ]);
    }

    /**
     * Scenario 6: Strict Data Preservation Regression Guard.
     * Validates 100% data preservation across all public models and controller props.
     */
    public function test_scenario_6_strict_data_preservation_regression_guard(): void
    {
        // 1. Home statistics and philosophy highlights verbatim
        $home = $this->get('/');
        $home->assertStatus(200);
        $home->assertInertia(fn (Assert $page) => $page
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

        // 2. Studio profile, manifesto, and 5 concept steps verbatim
        $studio = $this->get('/studio');
        $studio->assertStatus(200);
        $studio->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
            ->where('studio.name', 'Kode Landscape Studio')
            ->where('studio.founded', 2025)
            ->where('studio.location', 'Bali, Indonesia')
            ->where('conceptSteps.0.title', 'Contact Us')
            ->where('conceptSteps.1.title', 'Project Meeting & Site Assessment')
            ->where('conceptSteps.2.title', 'Moodboard & Landscape Concept')
            ->where('conceptSteps.3.title', 'Project Collaboration Agreement')
            ->where('conceptSteps.4.title', 'Design & Build')
        );

        // 3. Contact ateliers verbatim
        $contact = $this->get('/contact');
        $contact->assertStatus(200);
        $contact->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
            ->where('studios.0.city', 'Kyoto Atelier')
            ->where('studios.0.email', 'kyoto@studiokode.design')
            ->where('studios.1.city', 'Zurich Studio')
            ->where('studios.1.email', 'zurich@studiokode.design')
            ->where('studios.2.city', 'Melbourne Office')
            ->where('studios.2.email', 'melbourne@studiokode.design')
        );

        // 4. Seeded database projects count and published attributes intact
        $projects = Project::published()->get();
        $this->assertGreaterThanOrEqual(6, $projects->count(), 'All canonical seeded projects must be present');
    }
}
