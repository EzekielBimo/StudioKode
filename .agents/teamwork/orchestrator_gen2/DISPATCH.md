## 2026-11-09T11:42:14Z

Build a production-ready, highly optimized admin dashboard suite and website for StudioKode landscape architecture, allowing administrators to manage and perform full CRUD operations on all site content with the interface, labels, forms, and messages localized in Bahasa Indonesia, ensuring smooth, lag-free performance and optimized production assets.

Working directory: c:\laragon\www\StudioKode
Integrity mode: development

## Requirements

### R1. Autentikasi Admin & Proteksi Rute
Sediakan sistem autentikasi admin dan proteksi rute sehingga hanya admin terautentikasi yang dapat mengakses dashboard dan endpoint manajemen, serta mengarahkan pengunjung tanpa autentikasi ke halaman login admin.

### R2. Manajemen Proyek (Projects CRUD)
Sediakan manajemen CRUD lengkap untuk proyek (judul/title, slug, kutipan/excerpt, deskripsi, klien, lokasi, tahun, luas area/area size, gambar sampul/cover image, gambar masterplan, status unggulan/featured toggle, dan tanggal publikasi), termasuk fitur pencarian dan penyaringan data.

### R3. Manajemen Galeri Foto Proyek (Gallery Images)
Sediakan manajemen galeri foto untuk setiap proyek yang memungkinkan admin mengunggah, mengurutkan (reorder/sort), mengubah takarir (caption), dan menghapus foto galeri proyek.

### R4. Manajemen Pesan Masuk / Konsultasi (Inquiries Management)
Sediakan antarmuka manajemen pesan konsultasi/inquiry untuk melihat detail pesan, menyaring berdasarkan status (baru/new, dihubungi/contacted, diarsipkan/archived), memperbarui status, dan menghapus pesan masuk.

### R5. Ringkasan Dashboard, Navigasi, & Lokalisasi Bahasa Indonesia
Sediakan ringkasan metrik utama, proyek terbaru, dan pesan konsultasi terbaru yang selaras dengan estetika arsitektur StudioKode. Seluruh antarmuka admin (menu navigasi, label form, tombol aksi, pesan validasi form, status badge, dan notifikasi flash) disajikan dalam Bahasa Indonesia.

### R6. Optimasi Performa Frontend & Backend
Optimalkan performa frontend dan backend agar aplikasi berjalan lancar dan responsif tanpa lag: jalankan build produksi Vite yang teroptimasi dengan pemisahan kode (code-splitting), pemuatan lambat (lazy-loading) untuk gambar dan komponen non-kritis, animasi antarmuka yang mulus tanpa jank/layout shift, serta pemuatan relasi database (eager loading) untuk menghindari masalah query N+1 pada data proyek dan galeri foto.
