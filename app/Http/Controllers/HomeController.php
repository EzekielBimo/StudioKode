<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the studio home page.
     */
    public function index(): Response
    {
        // Retrieve top featured projects with fallback to latest published projects in a single weighted query, cached for 1 hour
        $featuredProjects = Cache::remember('home.featured_projects', 3600, function () {
            return Project::published()
                ->orderByDesc('is_featured')
                ->orderByDesc('year')
                ->orderByDesc('id')
                ->take(3)
                ->get();
        });

        // Studio key metrics
        $statistics = [
            ['value' => '15+', 'label' => 'Years Experience'],
            ['value' => '48+', 'label' => 'Curated Sanctuaries'],
            ['value' => '12', 'label' => 'Design Accolades'],
            ['value' => '100%', 'label' => 'Ecological Integrity'],
        ];

        // Core architectural pillars for hero and philosophy sections
        $philosophyHighlights = [
            [
                'number' => '01',
                'title' => 'Topographical Symbiosis',
                'description' => 'Every architectural intervention begins with meticulous reading of preexisting contours, hydrology, and microclimates.',
            ],
            [
                'number' => '02',
                'title' => 'Botanical Longevity',
                'description' => 'Curating resilient, self-sustaining plant communities that mature gracefully into generational living tapestries.',
            ],
            [
                'number' => '03',
                'title' => 'Tactile Materiality',
                'description' => 'Dry-stacked regional stone, charred timber, and raw metals patinated naturally by regional sun and rainwater.',
            ],
        ];

        return Inertia::render('Home/Index', [
            'featuredProjects' => $featuredProjects,
            'statistics' => $statistics,
            'philosophyHighlights' => $philosophyHighlights,
        ]);
    }
}
