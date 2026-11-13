<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with search, filter, and eager loaded image counts.
     */
    public function index(Request $request): Response
    {
        $query = Project::query()->withCount('images');

        // Filter by featured status toggle (?featured=1 or ?featured=0)
        if ($request->has('featured') && $request->query('featured') !== '' && $request->query('featured') !== null) {
            $featured = $request->query('featured');
            if ($featured === '1' || $featured === 'true' || $featured === 1 || $featured === true) {
                $query->where('is_featured', true);
            } elseif ($featured === '0' || $featured === 'false' || $featured === 0 || $featured === false) {
                $query->where('is_featured', false);
            }
        }

        // Full-text search across project title and location
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }
        }

        // Paginate results ordered by latest id
        $projects = $query->latest('id')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'search' => $request->query('search', ''),
                'featured' => $request->query('featured', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Create');
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Auto-generate slug from title if slug is empty or omitted
        $rawSlug = $request->input('slug');
        if (empty(trim((string) $rawSlug)) && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        } elseif (!empty(trim((string) $rawSlug))) {
            $request->merge(['slug' => Str::slug($rawSlug)]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')],
            'excerpt' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'client' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'area_size' => ['required', 'string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
            'masterplan_image' => ['nullable', 'image', 'max:10240'],
            'is_featured' => ['nullable'],
            'published_at' => ['nullable'],
        ], [
            'title.required' => 'Judul proyek wajib diisi.',
            'description.required' => 'Deskripsi proyek wajib diisi.',
            'location.required' => 'Lokasi proyek wajib diisi.',
            'year.required' => 'Tahun proyek wajib diisi.',
            'area_size.required' => 'Luas area proyek wajib diisi.',
            'slug.unique' => 'Slug proyek sudah digunakan. Silakan gunakan slug lain.',
            'cover_image.image' => 'File gambar sampul harus berupa berkas gambar yang valid.',
            'masterplan_image.image' => 'File gambar masterplan harus berupa berkas gambar yang valid.',
        ]);

        // Upload cover_image to public disk (projects/ directory)
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        } else {
            $validated['cover_image'] = 'projects/default-cover.jpg';
        }

        // Upload masterplan_image to public disk (projects/ directory) if provided
        if ($request->hasFile('masterplan_image')) {
            $validated['masterplan_image'] = $request->file('masterplan_image')->store('projects', 'public');
        } else {
            $validated['masterplan_image'] = null;
        }

        // Ensure slug exists if not in validated array
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle booleans and timestamps
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['published_at'] = $request->filled('published_at')
            ? Carbon::parse($request->input('published_at'))
            : null;

        Project::create($validated);

        Cache::forget('home.featured_projects');

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil ditambahkan.');
    }

    /**
     * Show the specified project or redirect to edit.
     */
    public function show(Project $project): RedirectResponse
    {
        return redirect()->route('admin.projects.edit', $project);
    }

    /**
     * Show the form for editing the specified project with eager-loaded gallery images.
     */
    public function edit(Project $project): Response
    {
        $project->load(['images' => fn ($q) => $q->orderBy('sort_order', 'asc')]);

        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        // Auto-generate slug from title if empty
        $rawSlug = $request->input('slug');
        if (empty(trim((string) $rawSlug)) && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        } elseif (!empty(trim((string) $rawSlug))) {
            $request->merge(['slug' => Str::slug($rawSlug)]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($project->id)],
            'excerpt' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'client' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'area_size' => ['required', 'string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
            'masterplan_image' => ['nullable', 'image', 'max:10240'],
            'is_featured' => ['nullable'],
            'published_at' => ['nullable'],
        ], [
            'title.required' => 'Judul proyek wajib diisi.',
            'description.required' => 'Deskripsi proyek wajib diisi.',
            'location.required' => 'Lokasi proyek wajib diisi.',
            'year.required' => 'Tahun proyek wajib diisi.',
            'area_size.required' => 'Luas area proyek wajib diisi.',
            'slug.unique' => 'Slug proyek sudah digunakan oleh proyek lain.',
            'cover_image.image' => 'File gambar sampul harus berupa berkas gambar yang valid.',
            'masterplan_image.image' => 'File gambar masterplan harus berupa berkas gambar yang valid.',
        ]);

        // Retain existing cover_image if no new file is uploaded
        if ($request->hasFile('cover_image')) {
            if ($project->cover_image && !str_starts_with($project->cover_image, 'http') && Storage::disk('public')->exists($project->cover_image)) {
                Storage::disk('public')->delete($project->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        } else {
            unset($validated['cover_image']);
        }

        // Retain existing masterplan_image if no new file is uploaded
        if ($request->hasFile('masterplan_image')) {
            if ($project->masterplan_image && !str_starts_with($project->masterplan_image, 'http') && Storage::disk('public')->exists($project->masterplan_image)) {
                Storage::disk('public')->delete($project->masterplan_image);
            }
            $validated['masterplan_image'] = $request->file('masterplan_image')->store('projects', 'public');
        } else {
            unset($validated['masterplan_image']);
        }

        // Ensure slug if present or update
        if (isset($validated['slug']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle booleans and timestamps
        if ($request->has('is_featured')) {
            $validated['is_featured'] = $request->boolean('is_featured');
        }
        if ($request->has('published_at')) {
            $validated['published_at'] = $request->filled('published_at')
                ? Carbon::parse($request->input('published_at'))
                : null;
        }

        $project->update($validated);

        Cache::forget('home.featured_projects');

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil diperbarui.');
    }

    /**
     * Remove the specified project from storage and delete associated files.
     */
    public function destroy(Project $project): RedirectResponse
    {
        // Clean up project cover and masterplan files from storage
        if ($project->cover_image && !str_starts_with($project->cover_image, 'http') && Storage::disk('public')->exists($project->cover_image)) {
            Storage::disk('public')->delete($project->cover_image);
        }
        if ($project->masterplan_image && !str_starts_with($project->masterplan_image, 'http') && Storage::disk('public')->exists($project->masterplan_image)) {
            Storage::disk('public')->delete($project->masterplan_image);
        }

        // Clean up child gallery image files from storage
        foreach ($project->images as $galleryImage) {
            if ($galleryImage->image_path && !str_starts_with($galleryImage->image_path, 'http') && Storage::disk('public')->exists($galleryImage->image_path)) {
                Storage::disk('public')->delete($galleryImage->image_path);
            }
        }

        // Cascade delete via Eloquent / DB foreign key cascade
        $project->delete();

        Cache::forget('home.featured_projects');

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil dihapus.');
    }
}
