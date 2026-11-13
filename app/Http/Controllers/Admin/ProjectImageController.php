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

        $image->caption = isset($validated['caption']) && trim((string) $validated['caption']) !== ''
            ? trim((string) $validated['caption'])
            : null;
        $image->save();

        return back()->with('success', 'Takarir foto berhasil diperbarui.');
    }

    /**
     * Delete an individual gallery image and its physical storage file.
     */
    public function destroy(ProjectImage $image): RedirectResponse
    {
        if (!empty($image->image_path) && !str_starts_with($image->image_path, 'http') && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }
}
