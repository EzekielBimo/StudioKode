# Handoff Report: Explorer M3.2 — Gallery Management & Drag-and-Drop Sort Reordering Architecture

## 1. Observation

### 1.1 Test Suite Analysis (`tests/Feature/Admin/AdminGalleryTest.php`)
Direct observation of `tests/Feature/Admin/AdminGalleryTest.php`:
- **Line 28**: Storage is faked on setup: `Storage::fake('public');`.
- **Lines 34-45 (`updateImage` helper)**:
  ```php
  protected function updateImage(ProjectImage $image, array $payload)
  {
      if (Route::has('admin.images.update')) {
          return $this->actingAs($this->admin)->put(route('admin.images.update', $image), $payload);
      }

      $res = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", $payload);
      if ($res->status() === 404) {
          return $this->actingAs($this->admin)->put("/admin/images/{$image->id}", $payload);
      }
      return $res;
  }
  ```
  The test expects either route name `admin.images.update` or path `/admin/project-images/{image}` or `/admin/images/{image}`.
- **Lines 50-61 (`deleteImage` helper)**:
  ```php
  protected function deleteImage(ProjectImage $image)
  {
      if (Route::has('admin.images.destroy')) {
          return $this->actingAs($this->admin)->delete(route('admin.images.destroy', $image));
      }

      $res = $this->actingAs($this->admin)->delete("/admin/project-images/{$image->id}");
      if ($res->status() === 404) {
          return $this->actingAs($this->admin)->delete("/admin/images/{$image->id}", []);
      }
      return $res;
  }
  ```
  The test expects either route name `admin.images.destroy` or path `/admin/project-images/{image}` or `/admin/images/{image}`.
- **Lines 73-79 (`test_gallery_upload_requires_authentication`)**:
  Unauthenticated `POST /admin/projects/{project->id}/images` must return `302` redirect to `/admin/login`.
- **Lines 85-115 (`test_gallery_multi_upload_persists_images_and_captions`)**:
  Payload:
  ```php
  $payload = [
      'images' => [$image1, $image2],
      'captions' => [
          'Pavilion entrance with stone paving',
          'Reflective koi pond at twilight',
      ],
  ];
  ```
  Endpoint: `POST /admin/projects/{$this->project->id}/images`
  Asserts: `status(302)`, `sessionHas('success')`, database count is 2, image 1 has `sort_order => 1`, image 2 has `sort_order => 2`.
- **Lines 121-140 (`test_gallery_image_caption_updates_successfully`)**:
  Payload: `['caption' => 'Updated botanical terrace perspective']`.
  Endpoint: `PUT /admin/project-images/{image->id}` (or `admin.images.update`).
  Asserts: `status(302)`, `sessionHas('success')`, database has updated caption.
- **Lines 146-172 (`test_gallery_batch_reordering_updates_sort_orders`)**:
  Payload:
  ```php
  $payload = [
      'orders' => [
          ['id' => $img3->id, 'sort_order' => 1],
          ['id' => $img1->id, 'sort_order' => 2],
          ['id' => $img2->id, 'sort_order' => 3],
      ],
      'image_ids' => [$img3->id, $img1->id, $img2->id],
  ];
  ```
  Endpoint: `POST /admin/projects/{$this->project->id}/images/reorder`.
  Asserts: `status(302)`, `sessionHas('success')`, `$img3->fresh()->sort_order == 1`, `$img1->fresh()->sort_order == 2`, `$img2->fresh()->sort_order == 3`.
- **Lines 178-190 (`test_gallery_image_deletes_successfully`)**:
  Endpoint: `DELETE /admin/project-images/{image->id}` (or `admin.images.destroy`).
  Asserts: `status(302)`, `sessionHas('success')`, `assertDatabaseMissing('project_images', ['id' => $image->id])`.
- **Lines 202-217 (`test_gallery_upload_rejects_non_image_files`)**:
  Non-image file (PHP script) sent in `images: [$invalidFile]`.
  Asserts: `status(302)`, `assertSessionHasErrors(['images.0'])`, database count 0.
- **Lines 223-237 (`test_gallery_upload_rejects_oversized_images`)**:
  File exceeding 5MB (6000KB) sent in `images: [$oversizedImage]`.
  Asserts: `status(302)`, `assertSessionHasErrors(['images.0'])`.
- **Lines 243-253 (`test_gallery_image_delete_returns_404_for_non_existent_image`)**:
  Request: `DELETE /admin/project-images/999999`.
  Asserts: `status(404)`.
- **Lines 265-274 (`test_gallery_images_retrieved_in_ascending_sort_order`)**:
  Tests `$project->fresh()->images` relationship orders by `sort_order ASC`.
- **Lines 280-301 (`test_gallery_upload_increments_sort_order_from_existing_maximum`)**:
  Existing image has `sort_order => 5`.
  Uploads new image without captions.
  Asserts: `latestImage->sort_order > 5` (i.e. starts at 6).
- **Lines 314-365 (`test_gallery_management_end_to_end_lifecycle`)**:
  Uploads 3 images -> reorders sequence -> updates caption on 1 image -> deletes 1 image -> verifies database integrity.

### 1.2 Model & Route Binding Observation
- `app/Models/Project.php` line 92:
  ```php
  public function getRouteKeyName(): string
  {
      return 'slug';
  }
  ```
  `Project` model overrides `getRouteKeyName()` to return `'slug'`.
  However, in `AdminGalleryTest.php`:
  Calls are made to `/admin/projects/{$this->project->id}/images` and `/admin/projects/{$this->project->id}/images/reorder` where ID is an integer (e.g. `1`).
  Therefore, routes in `routes/web.php` MUST use `{project:id}` parameter scoping, e.g.:
  `Route::post('/projects/{project:id}/images', ...)`
  `Route::post('/projects/{project:id}/images/reorder', ...)`
- `app/Models/ProjectImage.php`:
  `$fillable = ['project_id', 'image_path', 'caption', 'sort_order'];`
  `$casts = ['sort_order' => 'integer'];`
  Has `belongsTo(Project::class)`.
- `database/migrations/2024_01_01_000002_create_project_images_table.php`:
  Columns: `id`, `project_id` (foreign key constrained on `projects`, `cascadeOnDelete()`), `image_path` (string), `caption` (nullable string), `sort_order` (integer default 0, index), `timestamps()`.

### 1.3 Execution Baseline
Command: `php artisan test tests/Feature/Admin/AdminGalleryTest.php`
Result:
- Passed: 2 (`test_gallery_image_delete_returns_404_for_non_existent_image`, `test_gallery_images_retrieved_in_ascending_sort_order`).
- Failed: 9 (Expected status 302 but received 404 due to missing controller and routes).

### 1.4 Frontend Dependencies & Ecosystem Observation
- `package.json` contains:
  `"framer-motion": "^11.0.0"`, `"@inertiajs/react": "^1.2.0"`, `"lucide-react": "^0.359.0"`, `"react": "^18.2.0"`.
- Framer Motion 11 exports `Reorder.Group`, `Reorder.Item`, and `useDragControls`.
- `PROJECT.md` line 137 lists `Pages/Admin/Projects/Gallery.jsx (M3)`.
- `AdminProjectTest.php` line 136 checks that `Admin/Projects/Edit` renders with eager-loaded `project.images`.

---

## 2. Logic Chain

1. **Route Resolution Logic**:
   - Because `Project::getRouteKeyName()` is `'slug'`, if a route is defined as `/projects/{project}/images`, Laravel will query `WHERE slug = ?`. But `AdminGalleryTest.php` passes `{$this->project->id}`.
   - Defining the parameter explicitly as `{project:id}` forces Laravel route model binding to query `WHERE id = ?`.
   - For image update and deletion, `AdminGalleryTest.php` checks either route names (`admin.images.update`, `admin.images.destroy`) or direct URI `/admin/project-images/{image}` with a fallback check on `/admin/images/{image}`.
   - Therefore, registering `/project-images/{image}` with names `images.update` and `images.destroy` inside the `Route::prefix('admin')->name('admin.')->group(...)` block, and adding `/images/{image}` as secondary route aliases, ensures 100% route contract fulfillment under all conditions.

2. **Upload & Incremental Order Calculation Logic**:
   - The test `test_gallery_upload_increments_sort_order_from_existing_maximum` requires that new uploads continue from `(max_sort_order ?? 0) + 1`.
   - If a project currently has images with `sort_order` 5, `$max = (int) ($project->images()->max('sort_order') ?? 0);` returns 5.
   - During the upload loop, `$max++` increments to 6, 7, etc., ensuring strict sequential appending without collision.
   - Files are stored using `$file->store('projects/gallery', 'public')` which stores in `storage/app/public/projects/gallery/` and records `projects/gallery/<hash>.<ext>` in `project_images.image_path`.
   - Validation requires `images` (array, min 1), `images.*` (file, image, mimes: jpg, jpeg, png, webp, max: 5120KB), and `captions.*` (nullable string max 255).
   - If an invalid file or oversized file is passed at index 0, Laravel generates validation errors keyed as `images.0`, satisfying `assertSessionHasErrors(['images.0'])`.

3. **Reordering Payload Adaptability Logic**:
   - `AdminGalleryTest.php` supports two payload structures for `/admin/projects/{project:id}/images/reorder`:
     1. Key-value objects: `'orders' => [['id' => 3, 'sort_order' => 1], ...]`.
     2. Sequential ID array: `'image_ids' => [3, 1, 2]`.
   - In frontend drag-and-drop, Framer Motion produces an array of reordered items, making `image_ids` the natural payload. External API or tests may pass `orders`.
   - The controller will inspect:
     - If `orders` is present and valid, iterate and update `sort_order` directly.
     - Else if `image_ids` is present, iterate `$index => $id` and update `sort_order = $index + 1`.
   - All updates are scoped to `where('project_id', $project->id)` and wrapped in `DB::transaction(...)` for atomicity and cross-tenant security.

4. **Deletion & Disk Cleanup Logic**:
   - When an image is deleted via `DELETE /admin/project-images/{image}`, the controller must clean up both storage and database.
   - `if (!empty($image->image_path) && Storage::disk('public')->exists($image->image_path)) { Storage::disk('public')->delete($image->image_path); }` deletes the physical file.
   - `$image->delete()` deletes the database record.
   - A non-existent ID (e.g. 999999) triggers Laravel's `ModelNotFoundException`, returning HTTP 404, directly satisfying `test_gallery_image_delete_returns_404_for_non_existent_image`.

5. **Framer Motion Drag-and-Drop Zero-Jank Architecture**:
   - Using Framer Motion `<Reorder.Group>` and `<Reorder.Item>` provides GPU-accelerated FLIP animations.
   - If `dragListener={true}` (default), dragging starts anywhere on the element, which collides with text selection in `<input>` (for caption edits) and tap triggers on buttons.
   - By setting `dragListener={false}` on `<Reorder.Item>` and binding `useDragControls()` to a dedicated `<div onPointerDown={(e) => controls.start(e)}>` grab handle, dragging is only initiated when grabbing the handle.
   - The server sync must happen on `onDragEnd` (when the drag gesture completes), NOT during `onReorder` (which fires every animation frame). This prevents network flooding, layout shifts, and race conditions.

---

## 3. Caveats

- **Caveat 1**: Image storage in production requires `php artisan storage:link` so that `public/storage` maps to `storage/app/public`. In tests, `Storage::fake('public')` intercepts calls in-memory.
- **Caveat 2**: If an admin deletes a project as a whole, database foreign key `cascadeOnDelete()` removes `project_images` rows automatically. Milestone 3.1 handles project deletion and storage cleanup for project cover/masterplan.
- **Caveat 3**: Framer Motion `<Reorder.Group axis="y">` works on 1D vertical lists. In modern dashboard design, a vertical list item with thumbnail, sequence badge, caption input, drag handle, and actions offers the cleanest drag experience, highest touch precision, and zero layout bugs.

---

## 4. Conclusion & Implementation Plan

### 4.1 Backend Implementation Plan: `app/Http/Controllers/Admin/ProjectImageController.php`

Create `app/Http/Controllers/Admin/ProjectImageController.php` with the following implementation:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectImageController extends Controller
{
    /**
     * Store newly uploaded gallery images for a project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:255'],
        ], [
            'images.required' => 'Minimal satu berkas gambar galeri harus dipilih.',
            'images.array' => 'Format unggahan gambar tidak valid.',
            'images.*.required' => 'Berkas gambar wajib diunggah.',
            'images.*.image' => 'Berkas harus berupa gambar yang valid.',
            'images.*.mimes' => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
            'images.*.max' => 'Ukuran gambar tidak boleh melebihi 5 MB (5120 KB).',
            'captions.array' => 'Format takarir gambar tidak valid.',
            'captions.*.string' => 'Takarir gambar harus berupa teks.',
            'captions.*.max' => 'Takarir gambar maksimal 255 karakter.',
        ]);

        $uploadedFiles = $request->file('images', []);
        $captions = $request->input('captions', []);

        // Start incrementing sort_order from existing maximum
        $maxSortOrder = (int) ($project->images()->max('sort_order') ?? 0);

        foreach ($uploadedFiles as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $file->store('projects/gallery', 'public');
            $caption = isset($captions[$index]) && trim((string) $captions[$index]) !== ''
                ? trim((string) $captions[$index])
                : null;
            $maxSortOrder++;

            $project->images()->create([
                'image_path' => $path,
                'caption' => $caption,
                'sort_order' => $maxSortOrder,
            ]);
        }

        return back()->with('success', 'Foto galeri berhasil diunggah.');
    }

    /**
     * Batch update sort orders of gallery images for a project.
     */
    public function reorder(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'orders' => ['nullable', 'array'],
            'orders.*.id' => ['required_with:orders', 'integer'],
            'orders.*.sort_order' => ['required_with:orders', 'integer'],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['required_with:image_ids', 'integer'],
        ], [
            'orders.array' => 'Format data urutan gambar tidak valid.',
            'image_ids.array' => 'Format daftar ID gambar tidak valid.',
        ]);

        DB::transaction(function () use ($request, $project) {
            if ($request->has('orders') && is_array($request->input('orders'))) {
                foreach ($request->input('orders') as $item) {
                    if (isset($item['id'], $item['sort_order'])) {
                        ProjectImage::where('id', $item['id'])
                            ->where('project_id', $project->id)
                            ->update(['sort_order' => (int) $item['sort_order']]);
                    }
                }
            } elseif ($request->has('image_ids') && is_array($request->input('image_ids'))) {
                foreach ($request->input('image_ids') as $index => $imageId) {
                    ProjectImage::where('id', $imageId)
                        ->where('project_id', $project->id)
                        ->update(['sort_order' => $index + 1]);
                }
            }
        });

        return back()->with('success', 'Urutan foto galeri berhasil diperbarui.');
    }

    /**
     * Update the caption of a specific gallery image.
     */
    public function update(Request $request, ProjectImage $image): RedirectResponse
    {
        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
        ], [
            'caption.string' => 'Takarir foto harus berupa teks.',
            'caption.max' => 'Takarir foto maksimal 255 karakter.',
        ]);

        $image->caption = $validated['caption'] ?? null;
        $image->save();

        return back()->with('success', 'Takarir foto berhasil diperbarui.');
    }

    /**
     * Delete an individual gallery image and its physical storage file.
     */
    public function destroy(ProjectImage $image): RedirectResponse
    {
        if (!empty($image->image_path) && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }
}
```

### 4.2 Route Definitions Plan (`routes/web.php`)

Add the following inside `Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(...)`:

```php
use App\Http\Controllers\Admin\ProjectImageController;

// Gallery Management (Features 20-22)
Route::post('/projects/{project:id}/images', [ProjectImageController::class, 'store'])->name('projects.images.store');
Route::post('/projects/{project:id}/images/reorder', [ProjectImageController::class, 'reorder'])->name('projects.images.reorder');

// Image Caption Update & Deletion (Supports both /admin/project-images/{image} and /admin/images/{image})
Route::put('/project-images/{image}', [ProjectImageController::class, 'update'])->name('images.update');
Route::put('/images/{image}', [ProjectImageController::class, 'update']);

Route::delete('/project-images/{image}', [ProjectImageController::class, 'destroy'])->name('images.destroy');
Route::delete('/images/{image}', [ProjectImageController::class, 'destroy']);
```

### 4.3 Frontend Drag-and-Drop Implementation Plan

Create reusable component `resources/js/Components/Admin/GalleryManager.jsx` and page `resources/js/Pages/Admin/Projects/Gallery.jsx`.

#### Architecture of `GalleryManager.jsx`:
- **Imports**:
  - `import { Reorder, useDragControls } from 'framer-motion';`
  - `import { router } from '@inertiajs/react';`
  - `import { GripVertical, UploadCloud, Edit2, Trash2, Check, X, Image as ImageIcon } from 'lucide-react';`
  - `import { id } from '@/lib/id';`
- **State**:
  - `items`: State array tracking the gallery image sequence.
  - `uploading`: Loading state during multi-image POST.
  - `stagedFiles`: Local queue of selected files with `URL.createObjectURL` previews and editable captions prior to upload.
  - `editingId`: Current image ID in inline caption editing mode.
  - `isSyncingOrder`: Transient indicator showing order sync status.
- **Drag Handling Protocol**:
  1. `<Reorder.Group axis="y" values={items} onReorder={setItems}>`: Updates local visual list synchronously as user drags.
  2. Each `<Reorder.Item value={item} dragListener={false} dragControls={controls} onDragEnd={handleDragEnd}>`:
     - Disables item-wide drag listener to prevent conflict with inputs/buttons.
     - Attaches `controls` to `<div onPointerDown={(e) => controls.start(e)} className="cursor-grab active:cursor-grabbing touch-none">`.
     - When user releases drag, `onDragEnd` triggers a single batch sync request via Inertia `router.post`:
       ```javascript
       const handleDragEnd = () => {
           const orders = items.map((item, index) => ({
               id: item.id,
               sort_order: index + 1,
           }));
           const image_ids = items.map((item) => item.id);

           router.post(
               `/admin/projects/${project.id}/images/reorder`,
               { orders, image_ids },
               {
                   preserveScroll: true,
                   preserveState: true,
               }
           );
       };
       ```
- **Inline Caption Update Protocol**:
  - Clicking `Edit2` activates inline form with auto-focus.
  - Submits to `PUT /admin/project-images/{image.id}` with `{ caption: newCaption }`.
  - On success, exits edit mode and updates UI with zero reload.
- **Delete Item Protocol**:
  - Confirms action in Indonesian (`"Apakah Anda yakin ingin menghapus foto galeri ini?"`).
  - Calls `router.delete('/admin/project-images/' + item.id, { preserveScroll: true })`.

---

## 5. Verification Method

To independently verify the implementation once applied:

1. **Feature Test Verification**:
   ```powershell
   php artisan test tests/Feature/Admin/AdminGalleryTest.php
   ```
   **Pass Condition**: 11 passed, 0 failed, 33 assertions.

2. **Performance Query Budget Verification**:
   ```powershell
   php artisan test --filter test_project_edit_view_eager_loads_gallery_images
   ```
   **Pass Condition**: Executes in `<= 4` queries.

3. **Frontend Build Verification**:
   ```powershell
   npm run build
   ```
   **Pass Condition**: Exit code 0, Framer Motion chunks compiled with zero bundle errors.

4. **Invalidation Conditions**:
   - If route parameter in `routes/web.php` is defined as `{project}` instead of `{project:id}`, route model binding will attempt to find project by slug and return 404.
   - If `reorder` only supports `orders` and rejects `image_ids`, API calls or frontend payloads passing array of IDs will fail validation.
   - If `destroy` omits `Storage::disk('public')->delete(...)`, orphaned files will remain on disk after record deletion.
