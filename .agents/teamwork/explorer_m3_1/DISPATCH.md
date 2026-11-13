## 2026-11-09T12:44:15Z
You are Explorer M3.1 (Projects Controller & Models Explorer).
Your State Folder: c:\laragon\www\StudioKode\.agents\teamwork\explorer_m3_1\
Working Directory: c:\laragon\www\StudioKode

You are a read-only exploration agent. Do NOT modify any code.
You MUST read c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md and c:\laragon\www\StudioKode\PROJECT.md first.

Investigate the backend architecture for Project CRUD (Features 16-19):
1. Read `tests/Feature/Admin/AdminProjectTest.php` to understand every contract:
   - `GET /admin/projects`: filter by `featured`, `search` (in title and location), eager loading `withCount('images')`, pagination.
   - `GET /admin/projects/create`: render Inertia `Admin/Projects/Create`.
   - `POST /admin/projects`: validate mandatory fields (`title`, `description`, `location`, `year`, `area_size`), slug uniqueness, auto-slug when empty via `Str::slug`, upload `cover_image` and `masterplan_image` to `public` disk (`projects/` folder), store, flash Indonesian message `session('success', '...')`.
   - `GET /admin/projects/{project:id}/edit`: eager load `$project->load(['images' => fn($q) => $q->orderBy('sort_order', 'asc')])`, render Inertia `Admin/Projects/Edit`.
   - `PUT /admin/projects/{project:id}`: update fields, validate unique slug ignoring `$project->id`, retain existing cover/masterplan if no new file is uploaded, flash Indonesian message.
   - `DELETE /admin/projects/{project:id}`: delete project, verify cascade deletion of images and storage files cleanup.
2. Inspect `app/Models/Project.php`, `database/migrations/*projects*.php`. Note that `Project::getRouteKeyName()` is `'slug'`, so in `routes/web.php` admin resource route must be configured with `->parameters(['projects' => 'project:id'])`.
3. Design the exact implementation plan for `app/Http/Controllers/Admin/ProjectController.php` and `routes/web.php`.
4. Write your detailed report to `c:\laragon\www\StudioKode\.agents\teamwork\explorer_m3_1\handoff.md`.
5. Send a concise message back to caller (parent orchestrator) when done.
