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

        // Search by keyword, title, location, client, or description
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('client', 'like', "%{$search}%");
                });
            }
        }

        // Sort handling: newest, oldest, or largest area size in m²
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('year', 'asc')->orderBy('id', 'asc');
                break;
            case 'area_desc':
                // Cross-database numeric area sorting (works on MySQL and SQLite)
                $query->orderByRaw("(REPLACE(REPLACE(REPLACE(area_size, ' m²', ''), ',', ''), ' ', '') + 0) DESC");
                break;
            case 'newest':
            default:
                $query->orderBy('year', 'desc')->orderBy('id', 'desc');
                break;
        }

        $projects = $query->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort' => $sort,
            ],
            'totalCount' => Project::published()->count(),
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
