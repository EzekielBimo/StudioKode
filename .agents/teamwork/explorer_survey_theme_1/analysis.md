# Architectural Survey & Styling Analysis: Smoked Obsidian / Dark Moss Glass

**Project**: StudioKode Portfolio & Admin Transformation  
**Phase**: Survey & Architecture (Explorer Theme 1)  
**Date**: 2026-11-10  
**Scope**: Root template (`app.blade.php`), global styling (`tailwind.config.js`, `app.css`), layouts (`PublicLayout.jsx`, `AdminLayout.jsx`), and navigation (`Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`).

---

## 1. Executive Summary & Design System Identity

The StudioKode platform is an architectural landscape portfolio and administration suite. The current visual implementation uses a light editorial palette dominated by warm off-white canvas (`#F9F8F6`), deep charcoal typography (`#1A1A1A`), stone borders (`#E6E2DD`), and deep botanical foliage green (`#1C2A21`).

The goal of this transformation is to convert the entire application into the **"Smoked Obsidian / Dark Moss Glass"** aesthetic adhering strictly to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` design principles:
- **Smoked Obsidian Base**: Deep geological mineral depths (`#0B0E0C` canvas, `#0E1310` card surface, `#121814` elevated elements).
- **Dark Moss Glass Surfaces**: Translucent, tactile glass panels with restrained tinting (`rgba(20, 32, 24, 0.65)` to `rgba(14, 22, 17, 0.75)` with `backdrop-blur-md` / `backdrop-blur-lg`).
- **Frosted Glass Borders**: Whisper-thin borders (`border-white/10` or `border-emerald-500/15`) providing tactile definition without visual clutter.
- **Luminous Moss & Warm Gold Accents**: Restrained focal accents (`#34D399` emerald-400 / `#22C55E` emerald-500, warm gold `#C2A649` / `#D4AF37`) capped at key moments of action or status rather than scattered uniformly.
- **Editorial High-Contrast Typography**: Off-white/cream primary text (`#F4F6F0`), muted sage-stone secondary text (`#A7ACA0` / `#C5C8C0`), and deep tertiary text (`#7C8375`), preserving the typographic pairing of *Cormorant Garamond* (display serif) and *Plus Jakarta Sans* (geometric sans).
- **Strict Structural & Responsive Invariance**: Zero alterations to grid definitions, padding/margin layouts, flex hierarchies, break points, or responsive drawers.

---

## 2. Root Template Investigation (`resources/views/app.blade.php`)

### 2.1 Current Implementation
```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'StudioKode') }}</title>

        <!-- Google Fonts: Cormorant Garamond (Luxury Display Serif) & Plus Jakarta Sans (Geometric Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#F9F8F6] text-[#1A1A1A] selection:bg-[#1C2A21] selection:text-[#F9F8F6] min-h-screen flex flex-col">
        @inertia
        <script>
            (function() {
                var el = document.getElementById('app');
                var script = document.querySelector('script[data-page="app"]') || document.querySelector('script[data-page]');
                if (el && script && !el.dataset.page) {
                    el.dataset.page = script.textContent;
                }
            })();
        </script>
    </body>
</html>
```

### 2.2 Observations & Findings
1. **Missing Theme Color Meta**: Line 7 currently has no `<meta name="theme-color">`. For mobile Safari and Chrome address bar immersion in dark mode, `<meta name="theme-color" content="#0B0E0C">` is required.
2. **Body Classes**: Currently specifies `bg-[#F9F8F6] text-[#1A1A1A] selection:bg-[#1C2A21] selection:text-[#F9F8F6]`.
3. **HTML Class**: `class="scroll-smooth bg-[#0B0E0C]"`. Adding the root background to `html` prevents white flashes during inertial overscroll on macOS / iOS.
4. **Selection Styling**: Currently `selection:bg-[#1C2A21] selection:text-[#F9F8F6]`. In the dark moss theme, selection should be deep moss emerald `selection:bg-[#1C3024] selection:text-[#F4F6F0]`.

### 2.3 Concrete Recommendation for `app.blade.php`
- Add `<meta name="theme-color" content="#0B0E0C">` into `<head>`.
- Update `<html>` tag to:
  ```html
  <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-[#0B0E0C]">
  ```
- Update `<body>` class to:
  ```html
  <body class="font-sans antialiased bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] selection:text-[#F4F6F0] min-h-screen flex flex-col">
  ```

---

## 3. Styling Configuration Investigation

### 3.1 `tailwind.config.js`
The current Tailwind configuration extends the theme as follows:
```javascript
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
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
        },
    },
    plugins: [],
};
```

### 3.2 Codebase Hex Usage Census
Investigation into actual usage shows that JSX files overwhelmingly use explicit Tailwind arbitrary hex classes rather than the `studio-*` classes:
- `#1A1A1A` (180 occurrences) — Charcoal text, headers, dark icons.
- `#E6E2DD` (173 occurrences) — Stone borders, subtle container backgrounds, image placeholders.
- `#1C2A21` (149 occurrences) — Foliage green headers, buttons, accents, footer background.
- `#6E6B65` (100 occurrences) — Muted secondary text, labels, metadata.
- `#F9F8F6` (50 occurrences) — Off-white canvas and cards, light buttons, selection text.
- `#283c2f` (14 occurrences) — Foliage hover and border accents.

### 3.3 Proposed Token & Color Mapping Matrix

| Role / Intent | Current Token / Hex | "Smoked Obsidian / Dark Moss Glass" Equivalent | Description |
|---|---|---|---|
| **Root Canvas** | `#F9F8F6` (`bg-[#F9F8F6]`) | `#0B0E0C` (`bg-[#0B0E0C]`) | Deepest smoked obsidian ground. Zero pure-black flatness; subtle deep mineral warmth. |
| **Card / Surface** | `#FFFFFF` or `#F9F8F6` | `#0E1310` (`bg-[#0E1310]`) | Primary card/surface background. |
| **Elevated Surface** | `#E6E2DD` | `#121814` (`bg-[#121814]`) | Elevated containers, hover backgrounds, input fields, image placeholders. |
| **Deep Glass Surface** | `#F9F8F6/90` or `#1C2A21` | `rgba(20, 32, 24, 0.65)` (`bg-[#142018]/65` or `bg-[#0E1310]/80`) | Translucent dark moss glass with `backdrop-blur-md` / `backdrop-blur-lg`. |
| **Primary Typography** | `#1A1A1A` (`text-[#1A1A1A]`) | `#F4F6F0` (`text-[#F4F6F0]`) | High-contrast editorial cream/off-white (WCAG AAA compliant on `#0B0E0C`). |
| **Secondary Typography** | `#6E6B65` (`text-[#6E6B65]`) | `#A7ACA0` (`text-[#A7ACA0]`) | Refined sage stone for subtitles, metadata, and timestamps. |
| **Muted / Tertiary Text**| `#1A1A1A/50` - `#1A1A1A/60` | `#7C8375` or `text-white/45` | Subtle archival stamps, copyright, footnotes. |
| **Subtle Frosted Borders**| `#E6E2DD` (`border-[#E6E2DD]`) | `border-white/10` or `border-emerald-500/15` | Tactile frosted glass perimeter. |
| **Foliage Primary Accent**| `#1C2A21` | Emerald `#34D399` (accent) / `#1C3024` (surface) | Luminous moss indicators and buttons. |
| **Warm Gold Accent** | Light amber / stone | `#C2A649` / `#D4AF37` / `text-amber-300` | Featured badge, award medals, status marks. |
| **Dividers & Separators**| `#E6E2DD` / `#283c2f` | `border-white/10` or `border-[#1A261E]` | Clean horizontal and vertical lines. |

### 3.4 `resources/css/app.css` Analysis & Recommendation
Current content:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
    html {
        scroll-behavior: smooth;
        background-color: #F9F8F6;
        color: #1A1A1A;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    }

    ::selection {
        background-color: #1C2A21;
        color: #F9F8F6;
    }

    h1, h2, h3, h4, .font-serif {
        font-family: 'Cormorant Garamond', Georgia, serif;
    }
}
```

Recommended update in `resources/css/app.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
    html {
        scroll-behavior: smooth;
        background-color: #0B0E0C;
        color: #F4F6F0;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    }

    ::selection {
        background-color: #1C3024;
        color: #F4F6F0;
    }

    h1, h2, h3, h4, .font-serif {
        font-family: 'Cormorant Garamond', Georgia, serif;
    }
}

@layer utilities {
    .glass-moss {
        background-color: rgba(20, 32, 24, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .glass-obsidian {
        background-color: rgba(14, 19, 16, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
}
```

---

## 4. Layouts & Navigation Investigation

### 4.1 `resources/js/Layouts/PublicLayout.jsx`
- **Current State**:
  - Root container: `<div className="min-h-screen flex flex-col bg-[#F9F8F6] text-[#1A1A1A] font-sans selection:bg-[#1C2A21] selection:text-[#F9F8F6]">`
  - Success flash: `<div className="bg-[#1C2A21] text-[#F9F8F6] px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider animate-in fade-in duration-300">`
  - Error flash: `<div className="bg-red-800 text-white px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider">`
  - Body container: `<main className="flex-1">{children}</main>`
- **Preservation Verification**:
  - The flex structure (`min-h-screen flex flex-col`, `<main className="flex-1">`) is preserved without alteration.
- **Transformation Target**:
  - Root container: `min-h-screen flex flex-col bg-[#0B0E0C] text-[#F4F6F0] font-sans selection:bg-[#1C3024] selection:text-[#F4F6F0]`
  - Success flash: `bg-[#142018]/90 text-emerald-300 border-b border-emerald-500/20 backdrop-blur-md px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider animate-in fade-in duration-300`
  - Error flash: `bg-red-950/90 text-red-200 border-b border-red-500/30 backdrop-blur-md px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider`

### 4.2 `resources/js/Layouts/AdminLayout.jsx`
- **Current State**:
  - Root container: `min-h-screen bg-[#F9F8F6] text-[#1A1A1A] font-sans flex flex-col md:flex-row selection:bg-[#1C2A21] selection:text-[#F9F8F6]`
  - Mobile top bar: `md:hidden bg-[#1C2A21] text-[#F9F8F6] border-b border-[#283c2f] px-4 py-3 flex items-center justify-between sticky top-0 z-40`
  - Mobile slide-over drawer: `bg-[#1C2A21] text-[#F9F8F6] p-6` with `backdrop-blur-sm` backdrop
  - Desktop sidebar: `w-64 bg-[#1C2A21] text-[#F9F8F6] border-r border-[#283c2f] shrink-0 sticky top-0 h-screen justify-between p-6 z-30`
  - Sidebar links:
    - Active: `bg-white/15 text-white shadow-sm`
    - Inactive: `text-[#E6E2DD]/70 hover:bg-white/10 hover:text-white`
  - Main area: `flex-1 flex flex-col min-w-0 overflow-y-auto` -> `<main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">`
  - Flash banners: success (`bg-[#1C2A21] border-[#283c2f]`) and error (`bg-red-800`).
- **Preservation Verification**:
  - The responsive drawer logic, state `mobileOpen`, desktop `w-64 sticky top-0 h-screen`, and main scroll container `min-w-0 overflow-y-auto` remain 100% intact.
- **Transformation Target**:
  - Root container: `min-h-screen bg-[#0B0E0C] text-[#F4F6F0] font-sans flex flex-col md:flex-row selection:bg-[#1C3024] selection:text-[#F4F6F0]`
  - Mobile top bar: `md:hidden bg-[#0E1310]/95 backdrop-blur-md text-[#F4F6F0] border-b border-white/10 px-4 py-3 flex items-center justify-between sticky top-0 z-40`
  - Mobile drawer: `bg-[#0E1310] border-r border-white/10 text-[#F4F6F0] p-6`
  - Desktop sidebar: `w-64 bg-[#0E1310] text-[#F4F6F0] border-r border-white/10 shrink-0 sticky top-0 h-screen justify-between p-6 z-30`
  - Nav links:
    - Active: `bg-emerald-500/15 text-emerald-300 border border-emerald-500/20 shadow-sm`
    - Inactive: `text-[#A7ACA0] hover:bg-white/5 hover:text-white`
  - Main area: maintains `max-w-7xl w-full mx-auto` on `#0B0E0C` background.
  - Flash banners:
    - Success: `bg-[#142018] text-[#F4F6F0] border-b border-emerald-500/20`
    - Error: `bg-red-950 text-red-200 border-b border-red-500/30`

### 4.3 `resources/js/Components/Navigation/Navbar.jsx`
- **Current State**:
  - Header: `sticky top-0 z-40 bg-[#F9F8F6]/90 backdrop-blur-md border-b border-[#E6E2DD] transition-all`
  - Brand wordmark: `font-serif text-2xl tracking-tight text-[#1A1A1A] group-hover:text-[#1C2A21]`
  - Brand tagline: `text-[9px] uppercase tracking-[0.25em] text-[#1A1A1A]/60 font-medium`
  - Nav links:
    - Inactive: `text-[#1A1A1A]/80 hover:text-[#1C2A21]`
    - Active: `text-[#1C2A21] font-semibold` with underline `h-[1.5px] bg-[#1C2A21]`
  - Desktop CTA: `px-4 py-2 border border-[#1C2A21] text-xs uppercase tracking-widest text-[#1C2A21] hover:bg-[#1C2A21] hover:text-[#F9F8F6]`
  - Mobile trigger: `p-2 text-[#1A1A1A] hover:text-[#1C2A21]`
- **Transformation Target**:
  - Header: `sticky top-0 z-40 bg-[#0B0E0C]/85 backdrop-blur-md border-b border-white/10 transition-all`
  - Brand wordmark: `font-serif text-2xl tracking-tight text-[#F4F6F0] group-hover:text-emerald-300`
  - Brand tagline: `text-[9px] uppercase tracking-[0.25em] text-emerald-400/70 font-medium`
  - Nav links:
    - Inactive: `text-[#C5C8C0] hover:text-white`
    - Active: `text-emerald-400 font-semibold` with underline `h-[1.5px] bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.4)]`
  - Desktop CTA: `px-4 py-2 border border-emerald-500/30 text-xs uppercase tracking-widest text-emerald-300 hover:bg-emerald-500/15 hover:border-emerald-400 hover:text-white transition-all font-medium`
  - Mobile trigger: `p-2 text-[#F4F6F0] hover:text-emerald-400 transition-colors`

### 4.4 `resources/js/Components/Navigation/MobileMenu.jsx`
- **Current State**:
  - Container: `md:hidden border-b border-[#E6E2DD] bg-[#F9F8F6] px-6 py-8 overflow-hidden shadow-lg`
  - Links:
    - Active: `text-[#1C2A21] font-bold border-l-2 border-[#1C2A21] pl-3`
    - Inactive: `text-[#1A1A1A]/80 hover:text-[#1C2A21]`
  - Divider & Location: `border-t border-[#E6E2DD] text-[#1A1A1A]/60`
  - CTA button: `bg-[#1C2A21] text-[#F9F8F6]`
- **Transformation Target**:
  - Container: `md:hidden border-b border-white/10 bg-[#0E1310]/95 backdrop-blur-xl px-6 py-8 overflow-hidden shadow-2xl`
  - Links:
    - Active: `text-emerald-400 font-bold border-l-2 border-emerald-400 pl-3`
    - Inactive: `text-[#C5C8C0] hover:text-white`
  - Divider & Location: `border-t border-white/10 text-[#A7ACA0]/70`
  - CTA button: `bg-[#142018] border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/20 hover:text-white`

### 4.5 `resources/js/Components/Navigation/Footer.jsx`
- **Current State**:
  - Container: `mt-auto bg-[#1C2A21] text-[#F9F8F6] py-16 border-t border-[#283c2f]`
  - Manifesto text: `text-[#E6E2DD]/80 font-light`
  - Headings: `text-xs uppercase tracking-widest text-[#E6E2DD] font-semibold mb-4`
  - Links: `text-[#E6E2DD]/80 hover:text-white`
  - CTA button: `border border-[#E6E2DD] text-[#F9F8F6] hover:bg-[#F9F8F6] hover:text-[#1C2A21]`
  - Bottom bar: `border-t border-white/10 text-[#E6E2DD]/60`
- **Transformation Target**:
  - Container: `mt-auto bg-[#080B09] text-[#F4F6F0] py-16 border-t border-white/10`
  - Manifesto text: `text-[#A7ACA0] font-light leading-relaxed`
  - Headings: `text-xs uppercase tracking-widest text-emerald-400/90 font-semibold mb-4`
  - Links: `text-[#C5C8C0] hover:text-emerald-300 transition-colors`
  - CTA button: `border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/15 hover:border-emerald-400 hover:text-white transition-colors`
  - Bottom bar: `border-t border-white/5 text-[#7C8375]`

---

## 5. UI Primitives Survey & Mapping

| Component | Target Styling in "Smoked Obsidian / Dark Moss Glass" |
|---|---|
| **`Button.jsx`** | - `primary`: `bg-[#142018] text-emerald-300 hover:bg-emerald-900/40 border border-emerald-500/30 shadow-[0_0_12px_rgba(52,211,153,0.1)] focus:ring-emerald-400`<br>- `secondary`: `bg-[#161F1A] text-[#F4F6F0] hover:bg-[#1E2B24] border border-white/10`<br>- `outline`: `border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/15 hover:border-emerald-400 hover:text-white`<br>- `outline-light`: `border border-white/20 text-[#F4F6F0] hover:bg-white/10 hover:border-white/30`<br>- `ghost`: `text-[#C5C8C0] hover:text-white hover:bg-white/5` |
| **`Badge.jsx`** | - `neutral`: `bg-white/10 text-[#F4F6F0] border border-white/10`<br>- `foliage`: `bg-emerald-950/60 text-emerald-300 border border-emerald-500/25`<br>- `outline`: `border border-white/15 text-[#C5C8C0]`<br>- `outline-light`: `border border-white/20 text-[#F4F6F0]` |
| **`Input.jsx`** | - Base: `bg-[#0E1310] border border-white/15 text-[#F4F6F0] placeholder:text-white/30 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400`<br>- Label: `text-[#C5C8C0]`<br>- Error: `border-red-500/70 focus:border-red-400 text-red-400` |
| **`Modal.jsx`** | - Backdrop: `bg-black/80 backdrop-blur-md`<br>- Container: `bg-[#0E1310] border border-white/15 shadow-2xl text-[#F4F6F0]`<br>- Header divider: `border-b border-white/10`<br>- Close button: `text-white/60 hover:text-white hover:bg-white/10` |
| **`ProjectCard.jsx`** | - Image ground: `bg-[#121814]`<br>- Title: `text-[#F4F6F0] group-hover:text-emerald-300 transition-colors`<br>- Subtitle/Metadata: `text-[#A7ACA0]`<br>- Excerpt: `text-[#A7ACA0]/85 font-light` |
| **`FilterBar.jsx`** | - Container: `border-b border-white/10`<br>- Input: `bg-[#0E1310] border border-white/15 text-[#F4F6F0] placeholder:text-white/30 focus:border-emerald-400`<br>- Select: `bg-[#0E1310] border border-white/15 text-[#F4F6F0] focus:border-emerald-400`<br>- Label & Counter: `text-[#A7ACA0]` |
| **`ImageGallery.jsx`** | - Image container: `bg-[#121814]`<br>- Hover overlay: `bg-black/40`<br>- Lightbox modal container: `bg-[#080B09] border border-white/10` |

---

## 6. Layout Preservation & Responsive Integrity Guarantees

In accordance with user requirement **R1**, **R2**, and **R3**:
1. **Grid classes**: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8` (and variants across all pages) are preserved identically.
2. **Container boundaries**: `max-w-7xl mx-auto px-6`, `max-w-4xl`, and `max-w-2xl` remain untouched.
3. **Responsive padding**: `px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-24` maintain identical spatial rhythm.
4. **Navigation logic**: The mobile hamburger drawer toggle (`mobileOpen`), transitions (`framer-motion`), and active URL detection (`url.startsWith(path)`) remain functionally identical.
5. **No horizontal overflow**: All glass borders and backgrounds use standard box-sizing without additional pixel widths that could cause viewport spill at 320px–390px.

---

## 7. Baseline Verification Status
- **Vite Production Build**: Executed `npm run build` -> Clean production build in 14.75s, 0 errors, chunks generated correctly.
- **Automated Test Suite**: Executed `php artisan test` -> **389 tests passed (4,098 assertions)** with 100% pass rate.
