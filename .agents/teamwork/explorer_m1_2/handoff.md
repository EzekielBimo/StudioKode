# Handoff Report: Frontend Login View & Indonesian Localization (Milestone 1 — M1.2)

**Author**: Explorer M1.2  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_2`  
**Milestone**: M1 (`admin_auth_foundation`)  
**Timestamp**: 2026-11-09T18:58:00Z  

---

### Executive Summary
This report delivers the complete architectural, visual, and behavioral specification for the frontend administrative login view `resources/js/Pages/Admin/Auth/Login.jsx` and the foundational Indonesian localization dictionary `resources/js/lib/id.js`.

The solution combines React 18, `@inertiajs/react` `useForm`, Tailwind CSS 3.4 matching the StudioKode botanical & landscape architecture aesthetic (`#F9F8F6` off-white base, `#1C2A21` deep foliage green, `#E6E2DD` sandstone borders, Cormorant Garamond display serif, and Plus Jakarta Sans geometric sans-serif), Framer Motion 11 smooth entrance animations without layout shift, accessible form controls, and full Indonesian UI text.

---

## 1. Observation

Direct observations from repository files, configuration, and build environment:

1. **Frontend Dependencies (`package.json`)**:
   - Lines 8–16:
     ```json
     "dependencies": {
         "@inertiajs/react": "^1.2.0",
         "clsx": "^2.1.1",
         "framer-motion": "^11.0.0",
         "lucide-react": "^0.359.0",
         "react": "^18.2.0",
         "react-dom": "^18.2.0",
         "tailwind-merge": "^2.5.4"
     }
     ```
   - Lines 18–25:
     ```json
     "devDependencies": {
         "@vitejs/plugin-react": "^4.2.1",
         "autoprefixer": "^10.4.18",
         "axios": "^1.6.4",
         "laravel-vite-plugin": "^1.0",
         "postcss": "^8.4.35",
         "tailwindcss": "^3.4.1",
         "vite": "^5.0"
     }
     ```
   - Confirms React 18, `@inertiajs/react`, `framer-motion`, and `lucide-react` are installed and available.

2. **Tailwind Design System (`tailwind.config.js`)**:
   - Lines 11–28:
     ```javascript
     colors: {
         studio: {
             offwhite: '#F9F8F6',
             foliage: '#1C2A21',
             'foliage-light': '#283c2f',
             stone: '#E6E2DD',
             'stone-dark': '#d8d3cd',
             charcoal: '#1A1A1A',
             muted: '#6E6B65',
         },
     },
     fontFamily: {
         serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
         sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
     },
     letterSpacing: {
         'widest-plus': '0.25em',
     },
     ```
   - Confirms exact design tokens for background, typography, borders, and buttons.

3. **Inertia Bootstrap & Page Resolution (`resources/js/app.jsx`)**:
   - Lines 28–39:
     ```javascript
     createInertiaApp({
         title: (title) => `${title} - ${appName}`,
         resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
         page: initialPage,
         setup({ el, App, props }) {
             const root = createRoot(el);
             root.render(<App {...props} />);
         },
         progress: {
             color: '#1C2A21',
         },
     });
     ```
   - Any page component placed at `resources/js/Pages/Admin/Auth/Login.jsx` is automatically resolved when backend returns `Inertia::render('Admin/Auth/Login')`.

4. **Shared Props Contract (`app/Http/Middleware/HandleInertiaRequests.php`)**:
   - Lines 36–45:
     ```php
     public function share(Request $request): array
     {
         return array_merge(parent::share($request), [
             'appName' => config('app.name', 'StudioKode'),
             'flash' => [
                 'success' => fn () => $request->session()->get('success'),
                 'error' => fn () => $request->session()->get('error'),
             ],
         ]);
     }
     ```
   - `flash.error` and `flash.success` are shared across Inertia responses, allowing `Login.jsx` to render session error and status banners directly.

5. **Existing Motion Patterns (`resources/js/Components/UI/InView.jsx`)**:
   - Lines 4–20:
     ```javascript
     const defaultVariants = {
         hidden: { opacity: 0, y: 24, filter: 'blur(4px)' },
         visible: { opacity: 1, y: 0, filter: 'blur(0px)' },
     };
     const defaultTransition = {
         duration: 0.7,
         ease: [0.21, 0.47, 0.32, 0.98],
     };
     ```
   - Motion easing `[0.21, 0.47, 0.32, 0.98]` with subtle blur and y-translation is the standard StudioKode aesthetic for fluid 60fps transitions without layout shift.

6. **Current Build & Test State**:
   - `npm run build`: builds in 4.65s without warnings or errors.
   - `php artisan test`: 83 tests passing (711 assertions).

---

## 2. Logic Chain

1. **Route & Form Endpoint Contract**:
   - Based on Explorer M1.1's backend route specification, the guest login route is `GET /admin/login` (named `login` and `admin.login`), and the submission endpoint is `POST /admin/login` (named `admin.login.store`).
   - Therefore, the Inertia form inside `Login.jsx` must submit via `post('/admin/login', ...)`.
   - On submission failure (HTTP 422), Inertia automatically populates the `errors` object (e.g. `errors.email`, `errors.password`).
   - The form submission options must specify:
     ```javascript
     post('/admin/login', {
         preserveScroll: true,
         onFinish: () => reset('password'),
     });
     ```
     Resetting the password field on completion ensures that failed attempts clear sensitive password data from the form state while preserving the typed email address.

2. **Visual Hierarchy & StudioKode Brand Identity**:
   - The admin login page must evoke the serene, refined architectural identity of StudioKode rather than a generic SaaS login.
   - Container background: `bg-[#F9F8F6]` (`studio-offwhite`).
   - Brand Wordmark: "StudioKode" in `font-serif text-3xl sm:text-4xl text-[#1A1A1A] tracking-tight` with subtitle "Landscape Architecture & Botanical Masterplans" in `text-[10px] uppercase tracking-[0.25em] text-[#1A1A1A]/60 font-medium`.
   - Card container: `w-full max-w-md bg-white border border-[#E6E2DD] shadow-sm p-8 sm:p-10`.
   - Buttons: Primary button in `bg-[#1C2A21] text-[#F9F8F6] hover:bg-[#283c2f] transition-all duration-300`, uppercase with `tracking-widest`.
   - Input borders: `border-[#E6E2DD] focus:border-[#1C2A21] focus:ring-1 focus:ring-[#1C2A21]`.
   - Contrast check: Text `#1C2A21` on `#F9F8F6` yields a contrast ratio > 14:1 (exceeding WCAG AAA). Text `#1A1A1A` on `#FFFFFF` yields ~18:1. Validation error `#DC2626` (red-600) on `#FFFFFF` yields ~4.5:1.

3. **Indonesian UI Localization (`resources/js/lib/id.js`)**:
   - Requirement R5 and Feature 9 require all admin interface strings, labels, validation messages, and notifications to be in Bahasa Indonesia.
   - Organizing all translations centrally in `resources/js/lib/id.js` creates a single source of truth for Milestone 1 (Login/Auth) and future Milestones (Dashboard, Inquiries, Projects, Gallery).
   - Core Indonesian strings for Login:
     - Heading: "Masuk ke Panel Admin StudioKode"
     - Subtitle: "Masukkan kredensial Anda untuk mengakses panel pengelolaan dasbor dan portofolio."
     - Form labels: "Alamat Email", "Kata Sandi", "Ingat Saya"
     - Placeholders: "admin@studiokode.com", "••••••••"
     - Submit button: "Masuk ke Dasbor"
     - Loading state: "Memproses Masuk..."
     - Return link: "← Kembali ke Situs Utama"
     - Security footnote: "Akses terbatas khusus untuk administrator dan arsitek berwenang StudioKode."

4. **Component State & UX Ergonomics**:
   - **Password Visibility Toggle**: An eye toggle (`Eye` / `EyeOff` from `lucide-react`) allows the admin to verify typed passwords, especially for long random administrator passwords.
   - **Accessible Checkbox**: "Ingat Saya" with native checkbox wrapped in styled accessible container with `id="remember"` and `htmlFor="remember"`.
   - **Alert Banners**:
     - `flash.error`: When the session contains a general error (or redirect error), an alert banner with `AlertCircle` icon appears at the top.
     - `flash.success`: When an admin logs out or a session flash is passed, a success banner with `CheckCircle2` appears.
     - Form-level error: If `errors.email` contains the credential failure message ("Kredensial yang diberikan tidak cocok dengan data kami."), it is cleanly rendered in red text below the email input or in the top alert banner.

5. **Framer Motion Performance & No-Jank Animation**:
   - To prevent Cumulative Layout Shift (CLS) and stutter (Requirement R6), the login card uses an entrance transition using `motion.div`:
     - `initial={{ opacity: 0, y: 16, filter: 'blur(4px)' }}`
     - `animate={{ opacity: 1, y: 0, filter: 'blur(0px)' }}`
     - `transition={{ duration: 0.6, ease: [0.21, 0.47, 0.32, 0.98] }}`
   - CSS `will-change: transform, opacity` is applied smoothly by Framer Motion, ensuring 60fps hardware acceleration.

---

## 3. Implementation Code Specifications

### A. Localization Dictionary: `resources/js/lib/id.js`

```javascript
/**
 * StudioKode Administrative Suite — Indonesian Localization Dictionary (Bahasa Indonesia)
 * Single source of truth for admin labels, buttons, navigation, status badges, flash notices, and errors.
 */

export const id = {
    // Brand & General Metadata
    brand: {
        name: 'StudioKode',
        tagline: 'Arsitektur Lanskap & Masterplan Botani',
        admin_panel: 'Panel Administrasi StudioKode',
    },

    // Authentication & Authorization (Milestone 1)
    auth: {
        title: 'Masuk ke Panel Admin',
        heading: 'Masuk ke Panel Admin StudioKode',
        subtitle: 'Masukkan kredensial Anda untuk mengakses panel pengelolaan dasbor dan portofolio.',
        email_label: 'Alamat Email',
        email_placeholder: 'admin@studiokode.com',
        password_label: 'Kata Sandi',
        password_placeholder: '••••••••',
        remember_me: 'Ingat Saya',
        submit_button: 'Masuk ke Dasbor',
        submitting_button: 'Memproses Masuk...',
        toggle_show_password: 'Lihat kata sandi',
        toggle_hide_password: 'Sembunyikan kata sandi',
        back_to_site: 'Kembali ke Situs Utama',
        security_notice: 'Akses terbatas khusus untuk administrator dan arsitek berwenang StudioKode.',
        logout_button: 'Keluar',
        logout_confirm: 'Apakah Anda yakin ingin keluar dari sesi admin?',
        failed: 'Kredensial yang diberikan tidak cocok dengan data kami.',
        logged_out: 'Anda telah berhasil keluar dari sesi admin.',
        unauthorized: 'Silakan masuk terlebih dahulu untuk mengakses area admin.',
    },

    // Navigation (Milestone 2+)
    nav: {
        dashboard: 'Dasbor',
        projects: 'Manajemen Proyek',
        inquiries: 'Pesan Masuk',
        settings: 'Pengaturan',
        view_site: 'Lihat Situs Utama',
    },

    // Common Actions & Form Controls
    common: {
        actions: 'Aksi',
        create: 'Tambah Baru',
        edit: 'Ubah',
        update: 'Perbarui',
        delete: 'Hapus',
        cancel: 'Batal',
        save: 'Simpan',
        saving: 'Menyimpan...',
        search: 'Cari...',
        filter: 'Saring',
        all: 'Semua',
        loading: 'Memuat data...',
        empty_state: 'Tidak ada data ditemukan.',
        confirm_delete_title: 'Konfirmasi Penghapusan',
        confirm_delete_message: 'Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin melanjutkan?',
        back: 'Kembali',
    },

    // Status Badges
    status: {
        new: 'Baru',
        contacted: 'Dihubungi',
        archived: 'Diarsipkan',
        featured: 'Unggulan',
        standard: 'Standar',
        published: 'Terbit',
        draft: 'Draf',
    },

    // Validation Errors
    validation: {
        required: 'Kolom ini wajib diisi.',
        email: 'Format alamat email tidak valid.',
        min_password: 'Kata sandi minimal harus 8 karakter.',
    },

    // Flash Alerts & Toast Notifications
    flash: {
        success: 'Berhasil',
        error: 'Kesalahan',
        warning: 'Peringatan',
        info: 'Informasi',
        saved: 'Data berhasil disimpan.',
        updated: 'Data berhasil diperbarui.',
        deleted: 'Data berhasil dihapus.',
    },
};

export default id;
```

---

### B. Login View Component: `resources/js/Pages/Admin/Auth/Login.jsx`

```jsx
import React, { useState } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Lock, Mail, Eye, EyeOff, AlertCircle, CheckCircle2, ArrowLeft, Shield } from 'lucide-react';
import { id } from '../../../lib/id';

export default function Login() {
    const { flash } = usePage().props;
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        clearErrors();
        post('/admin/login', {
            preserveScroll: true,
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="min-h-screen bg-[#F9F8F6] text-[#1A1A1A] flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8 selection:bg-[#1C2A21] selection:text-[#F9F8F6]">
            <Head title={`${id.auth.title} — ${id.brand.name}`} />

            {/* Back to public site header button */}
            <div className="w-full max-w-md mb-6 flex justify-between items-center">
                <Link
                    href="/"
                    className="inline-flex items-center text-xs uppercase tracking-widest text-[#1A1A1A]/70 hover:text-[#1C2A21] font-medium transition-colors group"
                >
                    <ArrowLeft className="w-3.5 h-3.5 mr-2 transition-transform group-hover:-translate-x-1" />
                    {id.auth.back_to_site}
                </Link>
                <div className="flex items-center space-x-1 text-xs text-[#1A1A1A]/50">
                    <Shield className="w-3.5 h-3.5 text-[#1C2A21]" />
                    <span className="text-[10px] uppercase tracking-wider font-semibold">SSL Terproteksi</span>
                </div>
            </div>

            {/* Login Card with Framer Motion Entrance */}
            <motion.div
                initial={{ opacity: 0, y: 16, filter: 'blur(4px)' }}
                animate={{ opacity: 1, y: 0, filter: 'blur(0px)' }}
                transition={{ duration: 0.6, ease: [0.21, 0.47, 0.32, 0.98] }}
                className="w-full max-w-md bg-white border border-[#E6E2DD] shadow-sm p-8 sm:p-10 relative"
            >
                {/* Brand Header */}
                <div className="text-center mb-8">
                    <Link href="/" className="inline-block group mb-3">
                        <span className="font-serif text-3xl sm:text-4xl tracking-tight text-[#1A1A1A] group-hover:text-[#1C2A21] transition-colors block">
                            {id.brand.name}
                        </span>
                        <span className="text-[9px] uppercase tracking-[0.25em] text-[#1A1A1A]/60 font-medium block mt-0.5">
                            {id.brand.tagline}
                        </span>
                    </Link>
                    <div className="w-12 h-px bg-[#1C2A21] mx-auto my-3" />
                    <h1 className="font-serif text-xl sm:text-2xl text-[#1A1A1A] font-medium">
                        {id.auth.heading}
                    </h1>
                    <p className="mt-2 text-xs text-[#1A1A1A]/70 leading-relaxed font-light">
                        {id.auth.subtitle}
                    </p>
                </div>

                {/* Session Flash Alert (Error) */}
                {flash?.error && (
                    <motion.div
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="mb-6 p-3.5 bg-red-50 border border-red-200 text-red-800 text-xs flex items-start gap-2.5"
                        role="alert"
                    >
                        <AlertCircle className="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
                        <span className="font-medium">{flash.error}</span>
                    </motion.div>
                )}

                {/* Session Flash Alert (Success / Logged out) */}
                {flash?.success && (
                    <motion.div
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="mb-6 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-2.5"
                        role="status"
                    >
                        <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                        <span className="font-medium">{flash.success}</span>
                    </motion.div>
                )}

                {/* Login Form */}
                <form onSubmit={handleSubmit} className="space-y-5" noValidate>
                    {/* Email Field */}
                    <div>
                        <label
                            htmlFor="email"
                            className="block text-xs uppercase tracking-wider font-medium text-[#1A1A1A] mb-1.5"
                        >
                            {id.auth.email_label} <span className="text-red-600">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#1A1A1A]/40">
                                <Mail className="w-4 h-4" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                required
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder={id.auth.email_placeholder}
                                aria-invalid={errors.email ? 'true' : 'false'}
                                aria-describedby={errors.email ? 'email-error' : undefined}
                                className={`w-full pl-10 pr-4 py-2.5 bg-white border text-sm text-[#1A1A1A] transition-colors placeholder:text-[#1A1A1A]/35 focus:outline-none focus:ring-1 ${
                                    errors.email
                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                        : 'border-[#E6E2DD] focus:border-[#1C2A21] focus:ring-[#1C2A21]'
                                }`}
                            />
                        </div>
                        {errors.email && (
                            <p id="email-error" className="mt-1.5 text-xs text-red-600 font-medium">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    {/* Password Field */}
                    <div>
                        <label
                            htmlFor="password"
                            className="block text-xs uppercase tracking-wider font-medium text-[#1A1A1A] mb-1.5"
                        >
                            {id.auth.password_label} <span className="text-red-600">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#1A1A1A]/40">
                                <Lock className="w-4 h-4" />
                            </div>
                            <input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                autoComplete="current-password"
                                required
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder={id.auth.password_placeholder}
                                aria-invalid={errors.password ? 'true' : 'false'}
                                aria-describedby={errors.password ? 'password-error' : undefined}
                                className={`w-full pl-10 pr-10 py-2.5 bg-white border text-sm text-[#1A1A1A] transition-colors placeholder:text-[#1A1A1A]/35 focus:outline-none focus:ring-1 ${
                                    errors.password
                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                        : 'border-[#E6E2DD] focus:border-[#1C2A21] focus:ring-[#1C2A21]'
                                }`}
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#1A1A1A]/40 hover:text-[#1C2A21] transition-colors focus:outline-none"
                                aria-label={showPassword ? id.auth.toggle_hide_password : id.auth.toggle_show_password}
                            >
                                {showPassword ? (
                                    <EyeOff className="w-4 h-4" />
                                ) : (
                                    <Eye className="w-4 h-4" />
                                )}
                            </button>
                        </div>
                        {errors.password && (
                            <p id="password-error" className="mt-1.5 text-xs text-red-600 font-medium">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    {/* Remember Me Toggle */}
                    <div className="flex items-center justify-between pt-1">
                        <label htmlFor="remember" className="flex items-center cursor-pointer select-none">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="w-4 h-4 rounded-none border-[#E6E2DD] text-[#1C2A21] focus:ring-[#1C2A21] accent-[#1C2A21] cursor-pointer"
                            />
                            <span className="ml-2.5 text-xs text-[#1A1A1A]/80 font-normal">
                                {id.auth.remember_me}
                            </span>
                        </label>
                    </div>

                    {/* Submit Button */}
                    <div className="pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full inline-flex items-center justify-center bg-[#1C2A21] text-[#F9F8F6] hover:bg-[#283c2f] transition-all duration-300 py-3 text-xs uppercase tracking-widest font-medium border border-transparent shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C2A21] focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none select-none"
                        >
                            {processing ? (
                                <>
                                    <svg
                                        className="animate-spin -ml-1 mr-2.5 h-4 w-4 text-[#F9F8F6]"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                    <span>{id.auth.submitting_button}</span>
                                </>
                            ) : (
                                <span>{id.auth.submit_button}</span>
                            )}
                        </button>
                    </div>
                </form>

                {/* Security Footer Notice */}
                <div className="mt-8 pt-6 border-t border-[#E6E2DD]/80 text-center">
                    <p className="text-[11px] text-[#1A1A1A]/50 leading-relaxed font-light">
                        {id.auth.security_notice}
                    </p>
                </div>
            </motion.div>
        </div>
    );
}
```

---

## 4. Caveats

1. **Vite Dynamic Page Resolution**:
   - `resources/js/app.jsx` utilizes `import.meta.glob('./Pages/**/*.jsx')`. When `Login.jsx` is created at `resources/js/Pages/Admin/Auth/Login.jsx`, Vite will automatically pick it up without requiring modifications to `app.jsx`.
2. **Backend Redirection & Session Revalidation**:
   - The frontend assumes the backend `AuthController@store` sets session flash error or returns validation errors as standard JSON/Inertia props. Explorer M1.1 must ensure `AuthController@store` executes `$request->session()->regenerate()` and catches authentication failure with `throw ValidationException::withMessages(['email' => id.auth.failed])` or `back()->withErrors(['email' => '...'])`.
3. **Remember Me Cookie Lifespan**:
   - The boolean `data.remember` sent to `Auth::attempt($credentials, $request->boolean('remember'))` determines whether Laravel issues the persistent `remember_web_*` session cookie.
4. **No Code Modification Constraint**:
   - As an Explorer agent operating in read-only investigation mode, no files were modified in `resources/js/` during this turn. All proposed files and snippets are documented above for the worker agent to implement cleanly.

---

## 5. Conclusion

1. `resources/js/Pages/Admin/Auth/Login.jsx` provides an exact visual and technical fit for StudioKode:
   - Built on React 18 and `@inertiajs/react` `useForm`.
   - Adheres strictly to the architectural botanical design language (`#F9F8F6`, `#1C2A21`, `#E6E2DD`, `#1A1A1A`, Cormorant Garamond, Plus Jakarta Sans).
   - Features full Indonesian text for headers, inputs, placeholders, buttons, and error messages.
   - Implements Framer Motion entrance animation using proven easing `[0.21, 0.47, 0.32, 0.98]`.
   - Accessible keyboard navigation, ARIA attributes, and password reveal toggle.
2. `resources/js/lib/id.js` provides an extensible, modular dictionary for Milestone 1 through Milestone 5, guaranteeing consistent Indonesian vocabulary across the entire dashboard.

---

## 6. Verification Method

Once the implementer (Worker M1) creates `resources/js/lib/id.js` and `resources/js/Pages/Admin/Auth/Login.jsx`, verify with the following commands:

1. **Verify Frontend Build & Syntax**:
   ```powershell
   npm run build
   ```
   *Expected outcome*: Vite compiles successfully, generating chunk `public/build/assets/Login-*.js` and updating `public/build/manifest.json`.

2. **Verify File Existence**:
   ```powershell
   Test-Path "resources/js/Pages/Admin/Auth/Login.jsx"
   Test-Path "resources/js/lib/id.js"
   ```
   *Expected outcome*: Both return `True`.

3. **Verify Inertia Route Contract via Feature Test**:
   ```powershell
   php artisan test --filter=AdminAuthTest
   ```
   *Expected outcome*: HTTP GET `/admin/login` returns status 200 and renders component `Admin/Auth/Login`.

4. **Verify Overall Test Suite Integrity**:
   ```powershell
   php artisan test
   ```
   *Expected outcome*: 83+ tests pass with 0 failures.
