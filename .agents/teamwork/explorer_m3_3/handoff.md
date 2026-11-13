# Handoff Report: Explorer M3.3 (Admin UI & Indonesian Localization)

## Observation

### 1. Existing Admin Frontend Architecture & Codebase Inspection
- **Admin Shell Layout (`resources/js/Layouts/AdminLayout.jsx`)**:
  - Employs StudioKode botanical aesthetic: Sidebar in `#1C2A21` (Foliage), background `#F9F8F6` (Off-white), borders `#283c2f` and `#E6E2DD` (Stone).
  - Navigation items defined on lines 24-43:
    - `/admin` (`id.nav.dashboard`)
    - `/admin/projects` (`id.nav.projects` with `active: currentUrl.startsWith('/admin/projects')`)
    - `/admin/inquiries` (`id.nav.inquiries`)
  - Global flash alerts handled on lines 237-268 for `flash.success` and `flash.error`.
  - User session state shared via Inertia props (`auth.user`).
- **Inquiries Table & Filter Patterns (`resources/js/Pages/Admin/Inquiries/Index.jsx`)**:
  - Filter tabs (lines 110-165) with badge counters: `all`, `baru`, `dihubungi`, `diarsipkan`.
  - Search input with clear button (lines 168-189) querying via `router.get(..., { preserveState: true, replace: true })`.
  - Data table with date formatting (`Intl.DateTimeFormat('id-ID')`), contact details, status badges, and action buttons (lines 208-288).
  - Pagination links renderer (lines 292-317) using Inertia `Link` with `dangerouslySetInnerHTML={{ __html: link.label }}`.
- **Indonesian Localization Dictionary (`resources/js/lib/id.js`)**:
  - Contains sections: `brand`, `auth`, `nav`, `common`, `status`, `dashboard`, `inquiries`, `validation`, `flash`.
  - Currently missing dedicated sections for `projects` (Feature 16-19) and `gallery` (Feature 20-22).
- **Projects Admin Directory**:
  - Directory `resources/js/Pages/Admin/Projects/` currently does not exist yet. Routes in `routes/web.php` line 56-63 currently render stubs `Admin/Projects/Index` and `Admin/Projects/Create` which fail or return placeholder views.
- **Installed Frontend Libraries (`package.json`)**:
  - `@inertiajs/react`: `^1.2.0`
  - `framer-motion`: `^11.0.0` (Exports `Reorder.Group` and `Reorder.Item` confirmed via `node -e "const { Reorder } = require('framer-motion');"`)
  - `lucide-react`: `^0.359.0`
  - `react`: `^18.2.0`
  - `tailwindcss`: `^3.4.1` with extended colors `studio.foliage (#1C2A21)`, `studio.offwhite (#F9F8F6)`, `studio.stone (#E6E2DD)`.
- **Existing Test Contracts**:
  - `tests/Feature/Admin/AdminProjectTest.php` expects:
    - `GET /admin/projects` -> renders `Admin/Projects/Index` with `projects` prop (paginated or collection), filtering by `search` and `featured`.
    - `GET /admin/projects/create` -> renders `Admin/Projects/Create`.
    - `POST /admin/projects` -> validates mandatory fields, auto-generates slug if omitted, uploads `cover_image` and `masterplan_image`, redirects with flash `success`.
    - `GET /admin/projects/{id}/edit` -> renders `Admin/Projects/Edit` with `project` prop including eager-loaded `images` relation.
    - `PUT /admin/projects/{id}` -> updates attributes, retains images if omitted, redirects with flash `success`.
    - `DELETE /admin/projects/{id}` -> deletes project and cascades to all child `project_images`.
  - `tests/Feature/Admin/AdminGalleryTest.php` expects:
    - `POST /admin/projects/{project}/images` -> multi-file upload (`images.*`) with captions (`captions.*`).
    - `POST /admin/projects/{project}/images/reorder` -> accepts `orders` array of `[{id, sort_order}]` or `image_ids` array.
    - `PUT /admin/project-images/{image}` -> caption update.
    - `DELETE /admin/project-images/{image}` -> deletes image and storage file.
  - `tests/Feature/Admin/AdminPerformanceTest.php` expects:
    - Zero Cumulative Layout Shift (CLS): images wrapped with explicit `aspect-[...]` container classes and `loading="lazy"`.

---

## Logic Chain

1. **Routing & Parameter Binding**:
   - `Project.php` defines `getRouteKeyName() => 'slug'`.
   - In `AdminProjectTest.php`, routes are tested with numeric IDs: `/admin/projects/{$project->id}/edit` and `/admin/projects/{$project->id}`.
   - Therefore, in `routes/web.php`, the admin resource must use `Route::resource('projects', ProjectController::class)->parameters(['projects' => 'project:id'])` or explicit id-based routes.

2. **Inertia Multipart PUT Limitation & Solution**:
   - Standard PHP and Inertia cannot parse `multipart/form-data` on native HTTP `PUT` or `PATCH` requests (PHP only parses `$_FILES` for `POST`).
   - For `Admin/Projects/Edit.jsx`, when replacing `cover_image` or `masterplan_image`, form submission must use method spoofing:
     `router.post('/admin/projects/' + project.id, { ...data, _method: 'PUT' }, { forceFormData: true })` or `useForm` with `_method: 'PUT'` posted to `/admin/projects/${project.id}`.
   - If no new image is selected, `data.cover_image` and `data.masterplan_image` remain `null`, and backend preserves existing database paths.

3. **Image Resolution Helper**:
   - Seeded project images use external URLs (`https://images.unsplash.com/...`), while uploaded images are stored locally (`projects/...` on `public` disk).
   - A unified helper `resolveImageUrl(path)` is required:
     ```javascript
     export function resolveImageUrl(path) {
         if (!path) return '';
         if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
             return path;
         }
         if (path.startsWith('/storage/')) {
             return path;
         }
         if (path.startsWith('storage/')) {
             return `/${path}`;
         }
         return `/storage/${path}`;
     }
     ```

4. **Gallery Management Integration (Edit vs Standalone)**:
   - `AdminProjectTest.php` asserts that `GET /admin/projects/{id}/edit` eager loads `project.images` (count 3).
   - Therefore, integrating the Gallery Management directly into `Admin/Projects/Edit.jsx` (with tabs: "Informasi Proyek" and "Galeri Foto Proyek") delivers the best UX while fulfilling all test assertions.
   - To also support direct navigation from the Index table's "Kelola Galeri" button, `Admin/Projects/Edit.jsx` can read URL hash `#gallery` or query `?tab=gallery` to automatically focus on the gallery tab. In addition, `Admin/Projects/Gallery.jsx` should be created as a dedicated standalone view sharing the same gallery manager component for route `/admin/projects/{project}/gallery`.

5. **Framer Motion Drag-to-Reorder Optimization**:
   - `framer-motion` v11 `<Reorder.Group axis="y" values={items} onReorder={setItems}>` provides lag-free drag-and-drop sorting.
   - Using a vertical list card view with `GripVertical` drag handle prevents layout jumping associated with multi-column grid dragging.
   - When drag ends, payload `{ orders: items.map((img, i) => ({ id: img.id, sort_order: i + 1 })), image_ids: items.map(img => img.id) }` is dispatched to `POST /admin/projects/{project.id}/images/reorder` via `router.post(..., { preserveScroll: true, preserveState: true })`.

---

## Detailed Component Specifications & Code Blueprints

### A. Additions to `resources/js/lib/id.js`
The following dictionary entries must be appended to `resources/js/lib/id.js`:

```javascript
    // Projects Portfolio Management (Milestone 3)
    projects: {
        title: 'Manajemen Proyek',
        subtitle: 'Kelola portofolio arsitektur lanskap, karya botani, dan masterplan StudioKode.',
        create_title: 'Tambah Proyek Baru',
        create_subtitle: 'Lengkapi informasi proyek lanskap dan unggah aset visual untuk portofolio.',
        edit_title: 'Ubah Proyek',
        edit_subtitle: 'Perbarui rincian proyek, ganti gambar sampul, atau kelola galeri foto.',
        gallery_title: 'Galeri Foto Proyek',
        gallery_subtitle: 'Unggah, urutkan, dan kelola takarir foto galeri karya arsitektur.',
        create_button: 'Tambah Proyek',
        save_button: 'Simpan Proyek',
        saving_button: 'Menyimpan Proyek...',
        update_button: 'Perbarui Proyek',
        updating_button: 'Memperbarui Proyek...',
        manage_gallery: 'Kelola Galeri',
        search_placeholder: 'Cari proyek berdasarkan judul, lokasi, atau klien...',
        filter_all_status: 'Semua Status Publikasi',
        filter_published: 'Terbit',
        filter_draft: 'Draf',
        filter_all_featured: 'Semua Status Unggulan',
        filter_featured: 'Hanya Unggulan',
        filter_standard: 'Standar (Bukan Unggulan)',
        empty_state: 'Belum ada proyek yang terdaftar di portofolio.',
        empty_filtered: 'Tidak ada proyek yang sesuai dengan kriteria pencarian atau penyaringan.',
        tabs: {
            details: 'Informasi Proyek',
            gallery: 'Galeri Foto',
        },
        table: {
            thumbnail: 'Pratinjau',
            title: 'Judul & Klien',
            location: 'Lokasi & Tahun',
            area: 'Luas Area',
            status: 'Publikasi',
            featured: 'Unggulan',
            images_count: 'Galeri',
            actions: 'Aksi',
        },
        fields: {
            title: 'Judul Proyek',
            title_placeholder: 'Contoh: Taman Bambu Sastra',
            slug: 'Slug URL',
            slug_placeholder: 'taman-bambu-sastra',
            auto_slug: 'Otomatis dari judul',
            manual_slug: 'Kustomisasi manual',
            client: 'Nama Klien / Pemilik',
            client_placeholder: 'Contoh: PT Sastra Sanctuary (Opsional)',
            location: 'Lokasi Lahan',
            location_placeholder: 'Contoh: Ubud, Bali',
            year: 'Tahun Proyek',
            year_placeholder: 'Contoh: 2024',
            area_size: 'Luas Area',
            area_size_placeholder: 'Contoh: 850 m²',
            excerpt: 'Kutipan Ringkas (Excerpt)',
            excerpt_placeholder: 'Ringkasan singkat proyek untuk kartu pratinjau...',
            description: 'Deskripsi Lengkap Arsitektur',
            description_placeholder: 'Jelaskan konsep ekologis, vegetasi botani, dan filosofi lanskap...',
            cover_image: 'Gambar Sampul (Cover Image)',
            cover_image_help: 'Format JPG, PNG, WEBP. Maksimal 5MB. Rasio lanskap disarankan.',
            masterplan_image: 'Gambar Masterplan / Denah',
            masterplan_image_help: 'Format JPG, PNG, WEBP. Maksimal 5MB. Opsional.',
            is_featured: 'Jadikan Proyek Unggulan',
            is_featured_hint: 'Proyek unggulan akan disorot di halaman beranda dan portofolio utama.',
            published_at: 'Jadwal / Tanggal Publikasi',
            published_at_help: 'Kosongkan tanggal jika proyek ingin disimpan sebagai Draf.',
            publish_now: 'Terbitkan Sekarang',
            set_as_draft: 'Simpan sebagai Draf',
            current_image: 'Gambar saat ini:',
            replace_image: 'Ganti berkas gambar:',
            browse_file: 'Pilih Berkas',
            no_file_chosen: 'Belum ada berkas dipilih',
        },
        confirm_delete_title: 'Hapus Proyek',
        confirm_delete_message: 'Apakah Anda yakin ingin menghapus proyek ini? Seluruh gambar galeri yang terhubung juga akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.',
        deleted_flash: 'Proyek berhasil dihapus beserta seluruh galeri terkait.',
        created_flash: 'Proyek berhasil dibuat.',
        updated_flash: 'Data proyek berhasil diperbarui.',
    },

    // Gallery Management (Milestone 3)
    gallery: {
        title: 'Manajemen Galeri Foto',
        subtitle: 'Unggah berkas foto secara massal, susun urutan tampilan dengan seret-lepas (drag & drop), dan ubah takarir.',
        upload_title: 'Unggah Foto Baru',
        dropzone_prompt: 'Tarik & lepas foto di sini, atau klik untuk memilih berkas',
        file_hint: 'Pilih satu atau beberapa berkas (JPG, PNG, WEBP, maks 5MB per foto)',
        selected_queue: 'Berkas Siap Diunggah',
        caption_label: 'Takarir (Caption)',
        caption_placeholder: 'Tuliskan takarir arsitektural...',
        upload_button: 'Unggah Foto Terpilih',
        uploading_button: 'Mengunggah Foto...',
        reorder_title: 'Urutan Tampilan Galeri',
        reorder_instruction: 'Seret ikon pegangan untuk mengatur urutan tampilan foto di website publik.',
        save_order_button: 'Simpan Urutan Baru',
        saving_order_button: 'Menyimpan Urutan...',
        order_saved_flash: 'Urutan foto galeri berhasil diperbarui.',
        caption_save: 'Simpan',
        caption_saved_flash: 'Takarir foto berhasil diperbarui.',
        delete_confirm_title: 'Hapus Foto Galeri',
        delete_confirm_message: 'Apakah Anda yakin ingin menghapus foto ini dari galeri proyek?',
        deleted_flash: 'Foto galeri berhasil dihapus.',
        empty_state: 'Belum ada foto galeri untuk proyek ini. Silakan unggah beberapa foto di atas.',
        total_photos: 'Total Foto:',
    },
```

---

### B. `Admin/Projects/Index.jsx` Component Specification
- **Path**: `resources/js/Pages/Admin/Projects/Index.jsx`
- **Props**:
  - `projects`: Paginated object (`{ data: [...], links: [...], current_page, ... }`) or Array.
  - `filters`: Object `{ search: '', featured: '', status: '' }`.
- **UI Structure**:
  1. Header:
     - Title: `id.projects.title` ("Manajemen Proyek")
     - Subtitle: `id.projects.subtitle`
     - Action button: "+ Tambah Proyek" (`Link href="/admin/projects/create"`) in Studio Foliage (`#1C2A21`).
  2. Filter Toolbar Card:
     - Search input with clear button (filters title, location, client).
     - Status Publikasi dropdown: Semua (`all`), Terbit (`published`), Draf (`draft`).
     - Status Unggulan dropdown: Semua (`all`), Unggulan (`1`), Standar (`0`).
     - On change: updates via `router.get('/admin/projects', params, { preserveState: true, replace: true })`.
  3. Table:
     - **Foto**: Aspect ratio `aspect-[4/3] w-16 h-12 rounded overflow-hidden bg-stone-100 border border-[#E6E2DD]` with `loading="lazy" decoding="async"`.
     - **Judul & Klien**: Title in bold text, link to public slug `/projects/{slug}`, client name underneath.
     - **Lokasi & Tahun**: `MapPin` icon, location string, and year badge.
     - **Luas Area**: Formatted `area_size` (e.g. `850 m²`).
     - **Status Publikasi**: Badge `Terbit` (`bg-emerald-50 text-emerald-800 border-emerald-200`) or `Draf` (`bg-stone-100 text-stone-600 border-stone-200`).
     - **Status Unggulan**: Badge `Unggulan` with `Sparkles` icon (`bg-amber-50 text-amber-800 border-amber-200`) if `is_featured`.
     - **Galeri**: Badge `${project.images_count || 0} Foto` with `Images` icon.
     - **Aksi**:
       - `Ubah`: `Link href={/admin/projects/${project.id}/edit}` with `Edit3` icon.
       - `Kelola Galeri`: `Link href={/admin/projects/${project.id}/edit#gallery}` with `Images` icon.
       - `Hapus`: Destructive button opening styled Confirmation Modal.
  4. Confirmation Modal:
     - Framer Motion animated modal (`Modal.jsx`) confirming cascade deletion with clear Indonesian warning.
  5. Pagination Controls:
     - Rendered when `links.length > 3`, consistent with `Inquiries/Index.jsx`.

---

### C. `Admin/Projects/Create.jsx` Component Specification
- **Path**: `resources/js/Pages/Admin/Projects/Create.jsx`
- **Hook**: `useForm({ title: '', slug: '', excerpt: '', description: '', client: '', location: '', year: new Date().getFullYear(), area_size: '', cover_image: null, masterplan_image: null, is_featured: false, published_at: '' })`
- **Auto-Slug Generator**:
  - State: `autoSlugEnabled` (default `true`).
  - Helper:
    ```javascript
    const slugify = (text) => text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
    ```
  - When `title` updates and `autoSlugEnabled` is active, automatically updates `slug`.
  - Checkbox toggle switch allowing manual override.
- **Form Layout**:
  - Two-column responsive layout (`grid grid-cols-1 lg:grid-cols-3 gap-8`):
    - **Main Col (2 cols)**:
      - Title input (required)
      - Slug input with auto-generate badge
      - Excerpt textarea (3 rows)
      - Description textarea (6 rows, required)
      - 2x2 grid: Client, Location (required), Year (required), Area Size (required, e.g. "850 m²")
    - **Sidebar Col (1 col)**:
      - Featured toggle switch (`is_featured`) with botanical badge preview
      - Publication schedule input (`published_at`) with "Terbitkan Sekarang" (now) and "Simpan sebagai Draf" (clears value) quick triggers
      - Cover Image Dropzone (required): file input with live preview, aspect-ratio placeholder (`aspect-[16/10]`), size warning (< 5MB)
      - Masterplan Image Dropzone (optional): file input with live preview, aspect-ratio placeholder (`aspect-video`)
      - Submit button with loading state (`processing ? id.projects.saving_button : id.projects.save_button`)
      - Cancel button linking back to `/admin/projects`.
- **Submission**:
  - `post('/admin/projects', { forceFormData: true })`.
  - Field-level error messages displayed using `errors[fieldName]` in `text-xs text-red-600 font-medium`.

---

### D. `Admin/Projects/Edit.jsx` Component Specification
- **Path**: `resources/js/Pages/Admin/Projects/Edit.jsx`
- **Props**:
  - `project`: Project object with attributes and eager-loaded `images` relation.
- **Hook & Method Spoofing**:
  - `const { data, setData, post, processing, errors } = useForm({ ...project_fields, cover_image: null, masterplan_image: null, _method: 'PUT' });`
  - Submit calls `post('/admin/projects/' + project.id, { forceFormData: true })`.
- **Tabs Interface**:
  - Tab 1: `id.projects.tabs.details` ("Informasi Proyek")
  - Tab 2: `id.projects.tabs.gallery` (`Galeri Foto (${project.images?.length || 0})`)
  - URL hash `#gallery` automatically activates Tab 2 on page load.
- **Image Replacement UX**:
  - For `cover_image`: displays existing image thumbnail (`resolveImageUrl(project.cover_image)`). File input allows choosing a new file. If chosen, preview is displayed with a "Batal Ganti" button.
  - For `masterplan_image`: displays existing masterplan (if present). File input allows replacing or uploading new file.
- **Tab 2 (Embedded Gallery Manager)**:
  - Embeds `<ProjectGalleryManager project={project} images={project.images || []} />`.

---

### E. `Admin/Projects/Gallery.jsx` & `ProjectGalleryManager` Specification
- **Path**: `resources/js/Pages/Admin/Projects/Gallery.jsx` & reusable component `resources/js/Components/Admin/ProjectGalleryManager.jsx`
- **Features**:
  1. **Multi-File Upload Dropzone**:
     - Drag-over event handlers (`onDragOver`, `onDragLeave`, `onDrop`).
     - Accepts `images/*` (`.jpg, .jpeg, .png, .webp`), max 5120KB per file.
     - Queued file previews with individual caption input for each queued photo.
     - Batch upload trigger: sends `FormData` with `images[]` and `captions[]` to `POST /admin/projects/${project.id}/images`.
  2. **Framer Motion Drag-to-Reorder List**:
     - Imports `{ Reorder } from 'framer-motion'`.
     - State `items` initialized to `images`.
     - `<Reorder.Group axis="y" values={items} onReorder={setItems} className="space-y-3">`:
       - `<Reorder.Item value={item} key={item.id} className="...">`:
         - Drag Handle: `<GripVertical className="cursor-grab active:cursor-grabbing w-5 h-5 text-[#6E6B65]" />`
         - Sort Badge: `<span className="font-mono text-xs text-[#6E6B65]">#{index + 1}</span>`
         - Aspect Ratio Thumbnail: `<div className="w-20 h-15 aspect-[4/3] rounded overflow-hidden bg-stone-100 border border-[#E6E2DD] shrink-0"><img src={resolveImageUrl(item.image_path)} className="w-full h-full object-cover" loading="lazy" decoding="async" /></div>`
         - Inline Caption Edit: Input field with "Simpan" button or blur trigger calling `router.put('/admin/project-images/' + item.id, { caption: newCaption }, { preserveScroll: true })`.
         - Delete Button: Trash icon triggering modal and calling `router.delete('/admin/project-images/' + item.id, { preserveScroll: true })`.
  3. **Batch Reorder Submission**:
     - Visual badge indicating "Urutan Berubah" when `items` order differs from initial.
     - "Simpan Urutan Baru" button (and/or on-drag-end flush) posting to `/admin/projects/${project.id}/images/reorder`:
       ```javascript
       router.post(`/admin/projects/${project.id}/images/reorder`, {
           orders: items.map((item, idx) => ({ id: item.id, sort_order: idx + 1 })),
           image_ids: items.map(item => item.id),
       }, { preserveScroll: true, preserveState: true });
       ```

---

## Caveats

1. **Route Parameter Name Constraint**:
   - `Project::getRouteKeyName()` is configured as `'slug'`. In `routes/web.php`, the admin resource must explicitly declare `parameters(['projects' => 'project:id'])` so that `{project}` route parameter resolves numeric IDs rather than slugs for `/admin/projects/{id}/edit` and `/admin/projects/{id}`.
2. **Inertia Method Spoofing**:
   - When uploading files during update (`Edit.jsx`), do NOT use `router.put()`. Must use `router.post(url, { ...data, _method: 'PUT' }, { forceFormData: true })` or `useForm({ ...data, _method: 'PUT' }).post(url, { forceFormData: true })`.
3. **Gallery Route Naming & Endpoints**:
   - `AdminGalleryTest.php` supports both `/admin/project-images/{id}` and `/admin/images/{id}` for caption updates and deletions. The implementer should ensure named routes `admin.images.update` and `admin.images.destroy` are bound to avoid any 404 mismatches.

---

## Conclusion

The architecture, component layout, and Indonesian localization dictionary for Milestone 3 (Projects & Gallery CRUD) are fully specified and aligned with StudioKode's botanical aesthetic, existing test contracts (`AdminProjectTest`, `AdminGalleryTest`, `AdminPerformanceTest`), and performance requirements (CLS prevention with aspect-ratio wrappers, lazy loading, and code-splitting).

The implementation requires:
1. Adding `id.projects` and `id.gallery` dictionaries to `resources/js/lib/id.js`.
2. Creating `resources/js/Pages/Admin/Projects/Index.jsx`.
3. Creating `resources/js/Pages/Admin/Projects/Create.jsx`.
4. Creating `resources/js/Pages/Admin/Projects/Edit.jsx`.
5. Creating `resources/js/Pages/Admin/Projects/Gallery.jsx` and `resources/js/Components/Admin/ProjectGalleryManager.jsx`.
6. Adding image URL resolver utility in `resources/js/lib/utils.js` (or inline in helper file).

---

## Verification Method

### 1. Test Suite Verification
Run the admin test suites to verify that all routes, props, and mutations function correctly:
```powershell
php artisan test --filter=AdminProjectTest
php artisan test --filter=AdminGalleryTest
php artisan test --filter=AdminPerformanceTest
```
Expected result: 100% pass on all 3 test files (36+ tests).

### 2. Frontend Production Asset Build
Verify that React components, Framer Motion drag-and-drop, and Lucide icons compile cleanly without syntax errors or missing dependencies:
```powershell
npm run build
```
Expected result: Exit code 0, assets bundled cleanly in `public/build/assets/`.

### 3. Visual & Functional Inspection Points
- Navigate to `/admin/projects`:
  - Verify table displays thumbnail previews, titles, locations, area sizes, `Unggulan` badge, `Terbit` / `Draf` status badge, image count badge, and action buttons.
  - Verify search bar and filter dropdowns filter projects cleanly without full-page reloads.
- Navigate to `/admin/projects/create`:
  - Verify auto-slug generator works when typing project title.
  - Verify file upload previews for cover and masterplan images.
  - Verify validation errors display in Indonesian when submitting an empty form.
- Navigate to `/admin/projects/{id}/edit`:
  - Verify existing data is populated, existing images are displayed, and file replacement works.
  - Navigate to the Gallery tab, test multi-file upload, drag items with `GripVertical` handle to reorder, edit captions inline, and delete individual images.
