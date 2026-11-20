<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of all garden & landscape architecture projects.
     */
    public function index(Request $request): Response
    {
        $query = Project::query()->published();

        // Category filter: "all", "design", "construction", "Project Design", or "Project Construction"
        $rawCategory = $request->input('category', 'all');
        $categoryParam = is_string($rawCategory) ? trim($rawCategory) : 'all';
        if (!empty($categoryParam) && strtolower($categoryParam) !== 'all') {
            $query->category($categoryParam);
        }

        // Search by keyword, title, location, client, or description
        $rawSearch = $request->input('search');
        if (is_string($rawSearch) && trim($rawSearch) !== '') {
            $search = trim($rawSearch);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('client', 'like', "%{$search}%");
            });
        }

        // Sort handling: newest, oldest, or largest area size in m²
        $sort = is_string($request->input('sort')) ? (string) $request->input('sort') : 'newest';
        switch ($sort) {
            case 'oldest':
                $query->orderBy('year', 'asc')->orderBy('id', 'asc');
                break;
            case 'area_desc':
                // Cross-database numeric area sorting (PostgreSQL, MySQL, and SQLite)
                if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
                    $query->orderByRaw("CAST(NULLIF(REGEXP_REPLACE(area_size, '[^0-9]', '', 'g'), '') AS INTEGER) DESC");
                } else {
                    $query->orderByRaw("(REPLACE(REPLACE(REPLACE(area_size, ' m²', ''), ',', ''), ' ', '') + 0) DESC");
                }
                break;
            case 'newest':
            default:
                $query->orderBy('year', 'desc')->orderBy('id', 'desc');
                break;
        }

        $projects = $query->get();

        $totalPublished = Project::published()->count();
        $designCount = Project::published()->where('category', Project::CATEGORY_DESIGN)->count();
        $constructionCount = Project::published()->where('category', Project::CATEGORY_CONSTRUCTION)->count();

        $filters = [
            'search' => is_string($rawSearch) ? $rawSearch : '',
            'sort' => $sort,
        ];

        if ($request->has('category') && is_string($rawCategory)) {
            $filters['category'] = $categoryParam;
        }

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $filters,
            'activeCategory' => $categoryParam ?: 'all',
            'categoryCounts' => [
                'all' => $totalPublished,
                'design' => $designCount,
                'construction' => $constructionCount,
            ],
            'totalCount' => $totalPublished,
        ]);
    }

    /**
     * Display the specified project detail view.
     */
    public function show(string $slug): Response
    {
        $project = Project::where('slug', $slug)->firstOrFail();
        $images = $project->images()->orderBy('sort_order', 'asc')->get();

        // 2–3 related projects
        $relatedProjects = Project::where('id', '!=', $project->id)
            ->published()
            ->latest('published_at')
            ->take(3)
            ->select([
                'id',
                'title',
                'slug',
                'category',
                'location',
                'area_size',
                'year',
                'excerpt',
                'cover_image',
                'is_featured',
                'published_at',
            ])
            ->get();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'images' => $images,
            'relatedProjects' => $relatedProjects,
        ]);
    }
}
