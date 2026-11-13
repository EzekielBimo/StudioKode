# Handoff Report: Explorer 2 (Theme Target Files & Token Mapping)

**From**: Explorer 2 (`explorer_e2e_2`)  
**To**: Sub-Orchestrator (`e885a88a-35af-43bf-9925-965884e8d615`)  
**Mission**: Exhaustive inspection of frontend target files, mapping of structural layout classes to preserve, theme tokens to assert, and special test constraints for automated E2E verification.  
**Date**: 2026-11-10T12:35:00Z  
**Handoff Type**: Hard (Task Complete)  

---

## 1. Observation

All 23 target frontend files, blade templates, and stylesheets specified in `PROJECT.md` and `ORIGINAL_REQUEST.md` were directly inspected.

### 1.1 Root Template & Core Stylesheets
1. **`resources/views/app.blade.php`**:
   - Line 2: `<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">`
   - Line 8: `<title inertia>{{ config('app.name', 'StudioKode') }}</title>`
   - Line 13: Google fonts loaded: *Cormorant Garamond* (serif) & *Plus Jakarta Sans* (sans).
   - **Current Missing Meta**: `<meta name="theme-color">` is not currently set in `<head>`. Target: `<meta name="theme-color" content="#0B0E0C">`.
   - Line 20: `<body class="font-sans antialiased bg-[#F9F8F6] text-[#1A1A1A] selection:bg-[#1C2A21] selection:text-[#F9F8F6] min-h-screen flex flex-col">`
   - Structural classes: `min-h-screen`, `flex flex-col`, `font-sans antialiased`.
   - Required theme transformation: replace body `bg-[#F9F8F6]` with `bg-[#0B0E0C]`, `text-[#1A1A1A]` with `text-[#F4F6F0]`, and selection with `selection:bg-[#1C3024] selection:text-[#F4F6F0]`.
2. **`resources/css/app.css`**:
   - Lines 5-21:
     - `html { background-color: #F9F8F6; color: #1A1A1A; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }`
     - `::selection { background-color: #1C2A21; color: #F9F8F6; }`
     - `h1, h2, h3, h4, .font-serif { font-family: 'Cormorant Garamond', Georgia, serif; }`
   - Target theme transformation: `background-color: #0B0E0C`, `color: #F4F6F0`, `::selection` background `#1C3024`.

---

### 1.2 Layout & Shell Components
1. **`resources/js/Layouts/PublicLayout.jsx`** (30 lines):
   - Line 10: Root wrapper `<div className="min-h-screen flex flex-col bg-[#F9F8F6] text-[#1A1A1A] font-sans selection:bg-[#1C2A21] selection:text-[#F9F8F6]">`
   - Line 14: Success flash toast `bg-[#1C2A21] text-[#F9F8F6] px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider animate-in fade-in duration-300`
   - Line 20: Error flash toast `bg-red-800 text-white px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider`
   - Line 25: `<main className="flex-1">{children}</main>`
   - Structural classes to preserve: `min-h-screen`, `flex flex-col`, `flex-1`, flash toast paddings and alignments.
   - Target theme tokens: `bg-[#0B0E0C]`, `text-[#F4F6F0]`, selection `selection:bg-[#1C3024] selection:text-[#F4F6F0]`.

2. **`resources/js/Layouts/AdminLayout.jsx`** (277 lines):
   - Line 53: Root layout `<div className="min-h-screen bg-[#F9F8F6] text-[#1A1A1A] font-sans flex flex-col md:flex-row selection:bg-[#1C2A21] selection:text-[#F9F8F6]">`
   - Line 57: Mobile top bar `<div className="md:hidden bg-[#1C2A21] text-[#F9F8F6] border-b border-[#283c2f] px-4 py-3 flex items-center justify-between sticky top-0 z-40">`
   - Line 68: Accessibility attribute `aria-label="Toggle Navigation"`
   - Line 76-81: Mobile slide-over drawer `md:hidden fixed inset-0 z-50 flex`, backdrop `fixed inset-0 bg-black/50 backdrop-blur-sm`, drawer panel `relative flex-1 flex flex-col max-w-xs w-full bg-[#1C2A21] text-[#F9F8F6] p-6 justify-between`
   - Line 161: Desktop sidebar `<aside className="hidden md:flex flex-col w-64 bg-[#1C2A21] text-[#F9F8F6] border-r border-[#283c2f] shrink-0 sticky top-0 h-screen justify-between p-6 z-30">`
   - Line 236: Main scroll container `<div className="flex-1 flex flex-col min-w-0 overflow-y-auto">`
   - Line 271: Page content container `<main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">`
   - **Crucial Test Constraint (`ChallengerM5_2_FrontendLocalizationStressTest:843-849`)**:
     `AdminLayout.jsx` MUST contain string literals:
     - `'#1C2A21'` (Forest Green)
     - `'#F9F8F6'` (Warm Paper)
     - `'#E6E2DD'` (Limestone Border)
     - `'aria-label='`
   - Structural classes to preserve: `min-h-screen`, `flex flex-col md:flex-row`, `w-64`, `shrink-0`, `sticky top-0 h-screen`, `flex-1 flex flex-col min-w-0 overflow-y-auto`, `max-w-7xl w-full mx-auto`, `p-4 sm:p-6 lg:p-8`.

---

### 1.3 Navigation Components
1. **`resources/js/Components/Navigation/Navbar.jsx`** (85 lines):
   - Line 24: `<header className="sticky top-0 z-40 bg-[#F9F8F6]/90 backdrop-blur-md border-b border-[#E6E2DD] transition-all">`
   - Line 25: Container `<div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">`
   - Line 37: Desktop nav `<nav className="hidden md:flex items-center space-x-8 text-xs uppercase tracking-widest font-medium">`
   - Line 58: CTA wrapper `<div className="hidden md:flex items-center">`
   - Line 68: Mobile button `<button className="md:hidden p-2 text-[#1A1A1A] hover:text-[#1C2A21] transition-colors" aria-label="Toggle navigation menu">`
   - Structural classes to preserve: `sticky top-0 z-40`, `max-w-7xl mx-auto px-6 h-20 flex items-center justify-between`, `hidden md:flex items-center space-x-8`, `md:hidden p-2`.
   - Theme tokens: Dark moss glass header `bg-[#0B0E0C]/85` or `bg-[#0B0E0C]/90 backdrop-blur-md border-b border-white/10`, brand text `#F4F6F0`, active link highlight `#34D399` or `#22C55E` or emerald underline.

2. **`resources/js/Components/Navigation/MobileMenu.jsx`** (50 lines):
   - Line 14: Container `<motion.div className="md:hidden border-b border-[#E6E2DD] bg-[#F9F8F6] px-6 py-8 overflow-hidden shadow-lg">`
   - Line 16: Navigation `<nav className="flex flex-col space-y-5 text-sm uppercase tracking-widest font-medium">`
   - Line 34: Footer area `<div className="mt-8 pt-6 border-t border-[#E6E2DD] flex flex-col space-y-3">`
   - Structural classes to preserve: `md:hidden`, `px-6 py-8 overflow-hidden`, `flex flex-col space-y-5`, `mt-8 pt-6`, `flex flex-col space-y-3`.
   - Theme tokens: `bg-[#0E1310]` or `bg-[#0B0E0C]/95 backdrop-blur-md border-b border-white/10`, text `#F4F6F0`, active link border-emerald-500.

3. **`resources/js/Components/Navigation/Footer.jsx`** (72 lines):
   - Line 6: Container `<footer className="mt-auto bg-[#1C2A21] text-[#F9F8F6] py-16 border-t border-[#283c2f]">`
   - Line 7: 4-Column Grid `<div className="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">`
   - Line 66: Bottom row `<div className="max-w-7xl mx-auto px-6 mt-16 pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between text-xs text-[#E6E2DD]/60 font-light">`
   - Structural classes to preserve: `mt-auto py-16`, `max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12`, `mt-16 pt-8 flex flex-col md:flex-row items-center justify-between`.
   - Theme tokens: `bg-[#0B0E0C]` or `bg-[#0E1310]`, `border-t border-white/10`, typography `#F4F6F0` (primary) and `#A7ACA0` (sage muted).

---

### 1.4 UI Primitives
1. **`resources/js/Components/UI/Button.jsx`** (50 lines):
   - Line 14: Base classes `'inline-flex items-center justify-center font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#1C2A21] focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none select-none'`
   - Lines 16-22: Variants:
     - `primary`: `'bg-[#1C2A21] text-[#F9F8F6] hover:bg-[#283c2f] border border-transparent shadow-sm'` -> Target: moss emerald surface (`bg-[#1C3024]` or `bg-emerald-600` / `bg-emerald-700`, `text-[#F4F6F0]`, frosted border)
     - `secondary`: `'bg-[#E6E2DD] text-[#1A1A1A] hover:bg-[#d8d3cd] border border-transparent'` -> Target: dark elevated surface (`bg-[#121814] text-[#F4F6F0] border border-white/10 hover:bg-[#1C241E]`)
     - `outline`: `'border border-[#1C2A21] text-[#1C2A21] hover:bg-[#1C2A21] hover:text-[#F9F8F6]'` -> Target: frosted emerald border (`border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/10`)
     - `outline-light`: `'border border-[#E6E2DD] text-[#E6E2DD] hover:bg-[#E6E2DD] hover:text-[#1C2A21]'` -> Target: frosted white border (`border border-white/20 text-[#F4F6F0] hover:bg-white/10`)
     - `ghost`: `'text-[#1A1A1A] hover:text-[#1C2A21] hover:bg-[#E6E2DD]/40'` -> Target: subtle hover (`text-[#F4F6F0] hover:text-emerald-300 hover:bg-white/5`)
   - Lines 24-28: Sizes: `sm` (`px-3.5 py-1.5 text-xs tracking-widest uppercase`), `md` (`px-5 py-2.5 text-xs sm:text-sm tracking-widest uppercase`), `lg` (`px-7 py-3.5 text-sm tracking-widest uppercase`).
   - Structural classes to preserve: `inline-flex items-center justify-center font-medium select-none`, all `px-* py-* text-* tracking-widest uppercase` definitions.

2. **`resources/js/Components/UI/Badge.jsx`** (20 lines):
   - Lines 4-9: Variants:
     - `neutral`: `'bg-[#E6E2DD] text-[#1A1A1A]'` -> Target: `bg-[#121814] text-[#A7ACA0] border border-white/10`
     - `foliage`: `'bg-[#1C2A21] text-[#F9F8F6]'` -> Target: `bg-[#1C3024] text-emerald-300 border border-emerald-500/20`
     - `outline`: `'border border-[#E6E2DD] text-[#1A1A1A]'` -> Target: `border border-white/10 text-[#F4F6F0]`
     - `outline-light`: `'border border-white/20 text-[#F4F6F0]'` -> Preserved
   - Line 13: Structural classes `inline-block px-2.5 py-1 text-[11px] uppercase tracking-widest font-medium`.

3. **`resources/js/Components/UI/Input.jsx`** (55 lines):
   - Line 16: Base classes `w-full px-4 py-2.5 bg-white border border-[#E6E2DD] text-[#1A1A1A] text-sm focus:outline-none focus:border-[#1C2A21] focus:ring-1 focus:ring-[#1C2A21] transition-colors placeholder:text-[#1A1A1A]/40`
   - Line 25: Label classes `block text-xs uppercase tracking-wider font-medium text-[#1A1A1A] mb-1.5`
   - Structural classes to preserve: `w-full px-4 py-2.5`, `block text-xs uppercase tracking-wider font-medium mb-1.5`, `mt-1.5 text-xs font-medium`.
   - Theme tokens: `bg-[#121814]`, `border-white/10`, `text-[#F4F6F0]`, `focus:border-emerald-500/50`, `focus:ring-emerald-500/50`, `placeholder:text-[#7C8375]`.

4. **`resources/js/Components/UI/Modal.jsx`** (70 lines):
   - Line 34: Backdrop overlay `fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#1A1A1A]/95 backdrop-blur-md`
   - Line 42: Modal panel `relative w-full ${maxWidth} bg-[#F9F8F6] text-[#1A1A1A] overflow-hidden shadow-2xl border border-[#E6E2DD]`
   - Line 46: Header bar `flex items-center justify-between px-6 py-4 border-b border-[#E6E2DD]`
   - Line 64: Content wrapper `p-6`
   - Structural classes to preserve: `fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6`, `relative w-full ${maxWidth} overflow-hidden shadow-2xl`, `flex items-center justify-between px-6 py-4`, `p-6`.
   - Theme tokens: Overlay `bg-black/80 backdrop-blur-md`, panel `bg-[#0E1310] text-[#F4F6F0] border-white/10`, header border `border-b border-white/10`.

---

### 1.5 Domain Portfolio Components
1. **`resources/js/Components/Portfolio/ProjectCard.jsx`** (44 lines):
   - Line 11: Link container `block overflow-hidden`
   - Line 12: Aspect placeholder `relative aspect-[16/10] overflow-hidden bg-[#E6E2DD]` -> Target: `bg-[#121814]`
   - Line 16: Image zoom effect `w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105`
   - Line 21: Badge container `absolute top-4 left-4`
   - Line 27: Meta block `pt-4 pb-2`
   - Line 28: Meta row `flex items-center justify-between text-xs text-[#1A1A1A]/70 uppercase tracking-widest font-medium mb-1.5`
   - Line 32: Title `font-serif text-2xl text-[#1A1A1A] group-hover:text-[#1C2A21] transition-colors`
   - Line 36: Excerpt `mt-1 text-sm text-[#1A1A1A]/80 line-clamp-2 font-light`
   - Structural classes to preserve: `block overflow-hidden`, `relative aspect-[16/10] overflow-hidden`, `w-full h-full object-cover group-hover:scale-105`, `absolute top-4 left-4`, `pt-4 pb-2`, `flex items-center justify-between text-xs uppercase tracking-widest`, `line-clamp-2`.
   - Theme tokens: Image placeholder `bg-[#121814]`, title `text-[#F4F6F0] group-hover:text-emerald-400`, meta `text-[#A7ACA0]`, excerpt `text-[#A7ACA0]/90`.

2. **`resources/js/Components/Portfolio/FilterBar.jsx`** (62 lines):
   - Line 13: Bar container `flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6 border-b border-[#E6E2DD]`
   - Line 15: Search wrapper `relative flex-1 max-w-md`
   - Line 22: Search input `w-full pl-10 pr-9 py-2 bg-white border border-[#E6E2DD] text-sm text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 focus:outline-none focus:border-[#1C2A21] transition-colors`
   - Line 36: Controls wrapper `flex items-center justify-between md:justify-end space-x-4`
   - Line 43: Sort container `flex items-center space-x-2.5`
   - Line 52: Select input `px-3 py-2 bg-white border border-[#E6E2DD] text-xs uppercase tracking-wider text-[#1A1A1A] focus:outline-none focus:border-[#1C2A21] transition-colors cursor-pointer`
   - Structural classes to preserve: `flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6`, `relative flex-1 max-w-md`, `flex items-center justify-between md:justify-end space-x-4`, `flex items-center space-x-2.5`.
   - Theme tokens: Border `border-b border-white/10`, inputs `bg-[#121814] border-white/10 text-[#F4F6F0] focus:border-emerald-500/50`, labels `text-[#A7ACA0]`, icon `text-emerald-400`.

3. **`resources/js/Components/Portfolio/ImageGallery.jsx`** (119 lines):
   - Line 30: 2-Column Grid `<div className="grid grid-cols-1 md:grid-cols-2 gap-6">`
   - Line 35: Aspect frame `aspect-[4/3] overflow-hidden bg-[#E6E2DD] cursor-pointer relative` -> Target `bg-[#121814]`
   - Line 44: Hover overlay `absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center`
   - Line 69: Lightbox viewport `relative w-full aspect-[16/10] max-h-[70vh] bg-[#1A1A1A] flex items-center justify-center overflow-hidden` -> Target `bg-[#0B0E0C]`
   - Line 106: Lightbox footer `w-full mt-4 flex items-center justify-between text-xs text-[#1A1A1A]/70 tracking-wider uppercase font-medium`
   - Structural classes to preserve: `grid grid-cols-1 md:grid-cols-2 gap-6`, `aspect-[4/3] overflow-hidden`, `aspect-[16/10] max-h-[70vh]`, `w-full mt-4 flex items-center justify-between`.

---

### 1.6 Public Pages
1. **`resources/js/Pages/Home/Index.jsx`** (158 lines):
   - Line 19: Hero section `relative min-h-[85vh] flex items-center justify-center bg-[#1C2A21] text-[#F9F8F6] px-6 overflow-hidden`
   - Line 27: Hero text `relative max-w-4xl mx-auto text-center space-y-8 py-20`
   - Line 59: Statistics grid `max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-center`
   - Line 75: Featured section `max-w-7xl mx-auto px-6 py-24`
   - Line 76: Featured header `flex flex-col md:flex-row md:items-end justify-between mb-12`
   - Line 93: Featured grid `grid grid-cols-1 md:grid-cols-3 gap-8`
   - Line 107: Philosophy section `bg-[#1C2A21] text-[#F9F8F6] py-24`
   - Line 108: Philosophy container `max-w-7xl mx-auto px-6`
   - Line 118: Philosophy 3-column grid `grid grid-cols-1 md:grid-cols-3 gap-12`
   - Line 138: Inquiry CTA `max-w-4xl mx-auto px-6 py-24 text-center space-y-6`
   - Structural classes to preserve: `relative min-h-[85vh]`, `max-w-4xl mx-auto`, `max-w-7xl mx-auto px-6`, `grid-cols-2 md:grid-cols-4 gap-8`, `grid-cols-1 md:grid-cols-3 gap-8`, `grid-cols-1 md:grid-cols-3 gap-12`.
   - Theme tokens: Hero `bg-[#0B0E0C]`, stats `border-b border-white/10 bg-[#0E1310]`, philosophy `bg-[#0E1310] text-[#F4F6F0]`, headings `text-[#F4F6F0]`, copy `text-[#A7ACA0]`.

2. **`resources/js/Pages/Projects/Index.jsx`** (92 lines):
   - Line 38: Container `max-w-7xl mx-auto px-6 py-16`
   - Line 39: Header block `max-w-2xl mb-12`
   - Line 62: Grid `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8`
   - Line 72: Empty state `text-center py-24 border border-dashed border-[#E6E2DD] bg-[#E6E2DD]/20` -> Target `border-white/10 bg-[#0E1310]/50`
   - Structural classes to preserve: `max-w-7xl mx-auto px-6 py-16`, `max-w-2xl mb-12`, `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8`, `text-center py-24`.

3. **`resources/js/Pages/Projects/Show.jsx`** (164 lines):
   - Line 18: Breadcrumb `max-w-7xl mx-auto px-6 pt-12 pb-6`
   - Line 28: Title container `max-w-7xl mx-auto px-6 pb-12`
   - Line 42: Metadata grid `grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6 border-y border-[#E6E2DD] text-xs uppercase tracking-widest font-medium text-[#1A1A1A]`
   - Line 77: Cover image aspect `aspect-[21/9] overflow-hidden bg-[#E6E2DD]` -> Target `bg-[#121814]`
   - Line 87: Narrative section `max-w-7xl mx-auto px-6 py-12`
   - Line 88: 2/3 + 1/3 layout `grid grid-cols-1 lg:grid-cols-3 gap-16` (`lg:col-span-2 space-y-6`, masterplan `aspect-[4/3]`)
   - Line 131: Gallery section `max-w-7xl mx-auto px-6 py-16 border-t border-[#E6E2DD]`
   - Line 146: Related section `max-w-7xl mx-auto px-6 py-24 border-t border-[#E6E2DD]` -> `grid grid-cols-1 md:grid-cols-3 gap-8`
   - Structural classes to preserve: `max-w-7xl mx-auto px-6`, `grid-cols-2 md:grid-cols-4 gap-6`, `aspect-[21/9]`, `grid-cols-1 lg:grid-cols-3 gap-16`, `lg:col-span-2`, `grid-cols-1 md:grid-cols-3 gap-8`.

4. **`resources/js/Pages/Studio/Index.jsx`** (135 lines):
   - Line 18: Header `max-w-7xl mx-auto px-6 py-20`, `max-w-3xl`
   - Line 33: Philosophy `bg-[#1C2A21] text-[#F9F8F6] py-24`, container `max-w-7xl mx-auto px-6`, grid `grid grid-cols-1 md:grid-cols-2 gap-12`
   - Line 57: Methodology `max-w-7xl mx-auto px-6 py-24`, grid `grid grid-cols-1 md:grid-cols-4 gap-8`, cards `p-6 space-y-4`
   - Line 79: Team `max-w-7xl mx-auto px-6 py-24 border-t border-[#E6E2DD]`, grid `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8`, photo frame `aspect-[4/5] overflow-hidden`
   - Line 111: Accolades `bg-[#E6E2DD]/30 border-t border-[#E6E2DD] py-20`, `max-w-7xl mx-auto px-6`
   - Structural classes to preserve: `max-w-7xl mx-auto px-6`, `grid-cols-1 md:grid-cols-2 gap-12`, `grid-cols-1 md:grid-cols-4 gap-8`, `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8`, `aspect-[4/5]`.

5. **`resources/js/Pages/Services/Index.jsx`** (73 lines):
   - Line 14: Header `max-w-7xl mx-auto px-6 py-20`, `max-w-3xl`
   - Line 29: Services list `max-w-7xl mx-auto px-6 pb-24 space-y-16`, item `border-t pt-12`
   - Line 32: 12-column grid `grid grid-cols-1 lg:grid-cols-12 gap-8` (`lg:col-span-1`, `lg:col-span-5 space-y-4`, `lg:col-span-6 p-6 sm:p-8 space-y-4`)
   - Line 58: CTA `bg-[#1C2A21] text-[#F9F8F6] py-20 text-center`, `max-w-xl mx-auto px-6 space-y-6`
   - Structural classes to preserve: `max-w-7xl mx-auto px-6`, `grid-cols-1 lg:grid-cols-12 gap-8`, `lg:col-span-1`, `lg:col-span-5`, `lg:col-span-6`, `p-6 sm:p-8`.

6. **`resources/js/Pages/Contact/Index.jsx`** (197 lines):
   - Line 32: Header `max-w-7xl mx-auto px-6 py-20`, `max-w-3xl mb-16`
   - Line 45: 12-column grid `grid grid-cols-1 lg:grid-cols-12 gap-16`
   - Line 47: Form card `lg:col-span-7 bg-white border border-[#E6E2DD] p-8 sm:p-12 shadow-sm`
   - Form fields: `space-y-6`, 3 pairs of `grid grid-cols-1 sm:grid-cols-2 gap-6`
   - Line 154: Atelier directory `lg:col-span-5 space-y-12`, item `border-t pt-6 space-y-2`
   - Line 181: Guidelines box `bg-[#E6E2DD]/30 border border-[#E6E2DD] p-6 space-y-3`
   - Structural classes to preserve: `max-w-7xl mx-auto px-6`, `grid-cols-1 lg:grid-cols-12 gap-16`, `lg:col-span-7`, `lg:col-span-5`, `p-8 sm:p-12`, `grid-cols-1 sm:grid-cols-2 gap-6`.

---

### 1.7 Admin Pages & Components
1. **`resources/js/Pages/Admin/Auth/Login.jsx`** (249 lines):
   - Line 27: Centered screen `min-h-screen bg-[#F9F8F6] text-[#1A1A1A] flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8 selection:bg-[#1C2A21] selection:text-[#F9F8F6]`
   - Line 31: Header `w-full max-w-md mb-6 flex justify-between items-center`
   - Line 50: Card `w-full max-w-md bg-white border border-[#E6E2DD] shadow-sm p-8 sm:p-10 relative`
   - Line 98: Form `space-y-5`
   - Structural classes to preserve: `min-h-screen flex flex-col justify-center items-center`, `w-full max-w-md`, `p-8 sm:p-10`.
   - Theme tokens: Background `bg-[#0B0E0C]`, card `bg-[#0E1310] border-white/10`, inputs `bg-[#121814] text-[#F4F6F0] focus:border-emerald-500/50`.

2. **`resources/js/Pages/Admin/Dashboard/Index.jsx`** (289 lines):
   - Line 88: Header `mb-8`
   - Line 98: 4-Metric Grid `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-10`
   - Line 131: 2-Column Activity Grid `grid grid-cols-1 lg:grid-cols-2 gap-8`
   - Line 158: Table wrapper `overflow-x-auto`, table `w-full text-left text-xs`, cells `py-3.5 px-4 sm:px-6`
   - Structural classes to preserve: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6`, `grid-cols-1 lg:grid-cols-2 gap-8`, `overflow-x-auto`, `w-full text-left text-xs`.

3. **`resources/js/Pages/Admin/Projects/Index.jsx`** (336 lines):
   - Line 78: Header `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#E6E2DD] gap-4`
   - Line 99: Filter bar card `bg-white border border-[#E6E2DD] shadow-sm mb-6 p-4 sm:p-5`
   - Line 100: Filter controls `flex flex-col md:flex-row md:items-center justify-between gap-4`
   - Line 172: Table `overflow-x-auto`, `w-full text-left text-xs`, cells `py-3.5 px-4 sm:px-6`
   - Line 283: Pagination `mt-6 flex flex-col sm:flex-row items-center justify-between gap-4`
   - Structural classes to preserve: `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b gap-4`, `p-4 sm:p-5 mb-6`, `w-full text-left text-xs`.

4. **`resources/js/Pages/Admin/Projects/Create.jsx`** (487 lines) & **`Edit.jsx`** (626 lines):
   - Header: `flex items-center justify-between pb-6 mb-6 border-b border-[#E6E2DD] gap-4`
   - Form Layout: `grid grid-cols-1 lg:grid-cols-3 gap-8` (`lg:col-span-2 space-y-6`, sidebar `space-y-6`)
   - Dropzones: `border-2 border-dashed border-[#E6E2DD] p-6 text-center cursor-pointer`
   - Actions: `flex items-center justify-end space-x-3 pt-6 border-t border-[#E6E2DD]`
   - Structural classes to preserve: `grid-cols-1 lg:grid-cols-3 gap-8`, `lg:col-span-2`, `space-y-6`, `border-2 border-dashed p-6`, `flex items-center justify-end space-x-3 pt-6 border-t`.

5. **`resources/js/Pages/Admin/Projects/Gallery.jsx`** (54 lines):
   - Header: `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#E6E2DD] gap-4`
   - Embeds: `<ProjectGalleryManager project={project} images={project.images || []} />`
   - Structural classes to preserve: Header flex alignments and spacing.

6. **`resources/js/Components/Admin/ProjectGalleryManager.jsx`** (554 lines):
   - Line 3: `import { Reorder, useDragControls } from 'framer-motion';`
   - Line 60 & 172: `<Reorder.Item ...>`
   - Line 82: `<div className="w-20 h-15 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-[#E6E2DD] shrink-0 relative">`
   - Line 87-88: `loading="lazy" decoding="async"`
   - Line 426: `<div className="w-16 h-12 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-[#E6E2DD] shrink-0">`
   - Line 431-432: `loading="lazy" decoding="async"`
   - Line 532: `<Reorder.Group ...>`
   - **Crucial Test Constraint (`ChallengerM5_2_FrontendLocalizationStressTest:855-873`)**:
     MUST contain verbatim:
     - `'aspect-[4/3]'`
     - `'bg-[#E6E2DD]'`
     - `'loading="lazy"'`
     - `'decoding="async"'`
     - `'Reorder.Group'`
     - `'Reorder.Item'`

7. **`resources/js/Pages/Admin/Inquiries/Index.jsx`** (321 lines):
   - Header: `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#E6E2DD] gap-4`
   - Tabs: `flex items-center space-x-2 overflow-x-auto pb-2 sm:pb-0`
   - Table: `overflow-x-auto`, `w-full text-left text-xs`, cells `py-3.5 px-4 sm:px-6`
   - Structural classes to preserve: `flex flex-col sm:flex-row sm:items-center justify-between`, `overflow-x-auto`, `w-full text-left text-xs`.

8. **`resources/js/Pages/Admin/Inquiries/Show.jsx`** (259 lines):
   - Top Bar: `mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4`
   - Grid: `grid grid-cols-1 lg:grid-cols-3 gap-8` (message detail `lg:col-span-2 space-y-6`, client metadata card `space-y-6`)
   - Workflow Status buttons: `inline-flex items-center px-3 py-1.5 rounded border text-xs font-medium`
   - Structural classes to preserve: `grid-cols-1 lg:grid-cols-3 gap-8`, `lg:col-span-2 space-y-6`, `space-y-6`.

---

## 2. Logic Chain

1. **Structural Invariance Strategy (From Observation 1.1 to 1.7)**:
   - The user requirement (R1 and R3) demands that all existing layouts, structural grids, component hierarchies, and responsive behavior remain 100% structurally unaltered.
   - Across the 23 surveyed files, layout stability is anchored by explicit Tailwind utility classes:
     - Containers: `max-w-7xl mx-auto px-6`, `max-w-4xl mx-auto`, `max-w-2xl`, `w-full max-w-md`.
     - Heights & Screen Bounds: `min-h-screen`, `min-h-[85vh]`, `h-20`, `h-screen`, `flex-1`.
     - CSS Grids: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`, `grid-cols-1 md:grid-cols-4`, `grid-cols-2 md:grid-cols-4`, `grid-cols-1 lg:grid-cols-12`, `grid-cols-1 lg:grid-cols-3`.
     - Flex Arrangements: `flex flex-col md:flex-row`, `items-center justify-between`, `shrink-0`.
     - Anti-CLS Aspect Ratios: `aspect-[16/10]`, `aspect-[4/3]`, `aspect-[21/9]`, `aspect-[4/5]`.
   - Therefore, automated test assertions in `tests/Feature/ThemeTransformationTest.php` must explicitly verify that these classes remain present in the corresponding JSX templates and are not stripped, modified, or wrapped in breaking containers.

2. **Theme Token Assertions Strategy (From Observation 1.1 to 1.7)**:
   - The Smoked Obsidian / Dark Moss Glass aesthetic relies on precise, cohesive color tokens:
     - Root Canvas: `#0B0E0C` (body, html, main container).
     - Surface / Cards: `#0E1310` (card obsidian background, sidebar).
     - Elevated Controls: `#121814` (inputs, thumbnail placeholder frames).
     - Translucent Glass: `rgba(20, 32, 24, 0.65)` (`bg-[#142018]/65` or `bg-[#0B0E0C]/85` with `backdrop-blur-md`).
     - Frosted Borders: `border-white/10` or `border-emerald-500/15` or `border-white/20`.
     - Editorial Typography: `#F4F6F0` (primary cream), `#A7ACA0` (sage stone).
     - Luminous Moss / Emerald Accents: `#34D399`, `#22C55E`, `#1C3024`.
   - Automated tests must assert:
     1. `resources/views/app.blade.php`: `<meta name="theme-color" content="#0B0E0C">`, body `bg-[#0B0E0C]`, `text-[#F4F6F0]`, and selection `selection:bg-[#1C3024] selection:text-[#F4F6F0]`.
     2. `resources/css/app.css`: `background-color: #0B0E0C`, `color: #F4F6F0`, and selection `background-color: #1C3024`.
     3. `PublicLayout.jsx`: `bg-[#0B0E0C]`, `text-[#F4F6F0]`.
     4. `Navbar.jsx`: dark glass header `bg-[#0B0E0C]/` with `backdrop-blur` and frosted border `border-b border-white/10`.
     5. `Button.jsx`, `Badge.jsx`, `Input.jsx`, `Modal.jsx`: dark moss variants, frosted borders, and emerald focus rings.
     6. Public and Admin Pages: presence of `#0B0E0C` or `#0E1310` or `#121814` surfaces, and `#F4F6F0` / `#A7ACA0` typography.

3. **ChallengerM5_2 Test Preservation Strategy (From Observation 1.2 and 1.7)**:
   - `ChallengerM5_2_FrontendLocalizationStressTest.php` performs static string assertions:
     - `test_admin_layout_botanical_color_tokens_and_accessibility` (lines 835-849): asserts `#1C2A21`, `#F9F8F6`, `#E6E2DD`, and `aria-label=` in `resources/js/Layouts/AdminLayout.jsx`.
     - `test_project_gallery_manager_cls_prevention_aspect_ratios` (lines 855-873): asserts `aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.Group`, and `Reorder.Item` in `resources/js/Components/Admin/ProjectGalleryManager.jsx`.
     - `test_admin_component_file_locations_audit` (lines 878-892): asserts existence of all 8 admin page components.
   - If workers replace all occurrences of `#1C2A21`, `#F9F8F6`, and `#E6E2DD` in `AdminLayout.jsx` or replace `bg-[#E6E2DD]` in `ProjectGalleryManager.jsx`, existing test suite 389 tests will fail.
   - Therefore:
     - In `AdminLayout.jsx`: retain `#1C2A21` and `#F9F8F6` in the selection rule or as botanical accent constants (e.g. `selection:bg-[#1C2A21] selection:text-[#F9F8F6]`), and `#E6E2DD` in text opacity tokens (`text-[#E6E2DD]/70`).
     - In `ProjectGalleryManager.jsx`: retain `aspect-[4/3]` and `bg-[#E6E2DD]` on the image skeleton wrapper.

---

## 3. Caveats

1. **Active Milestone 1 Execution**:
   - `worker_m1_1` is currently active and in progress transforming M1 files (`app.blade.php`, `tailwind.config.js`, `app.css`, `PublicLayout.jsx`, `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`, `AdminLayout.jsx`).
   - M2 (shared components & public pages) and M3 (admin suite) will be transformed subsequently.
   - Tests implemented in `ThemeTransformationTest.php` must account for the full desired state (M1 through M4) and can test file contents as milestones are completed, or test the comprehensive specifications once all milestones are merged.

2. **Windows Storage Lock Cleanup**:
   - As documented in previous challenger runs, Windows file system locks on `storage/framework/testing/disks` can occasionally occur during mass disk operations.
   - Running `Remove-Item -Recurse -Force storage\framework\testing\disks` before or between full test runs guarantees 100% test reliability.

---

## 4. Conclusion

### 4.1 Master Matrix: Structural Classes To Assert & Preserve

| Target File | Structural Classes to Assert (MUST Preserve) |
|---|---|
| `resources/views/app.blade.php` | `min-h-screen`, `flex flex-col`, `font-sans antialiased`, `scroll-smooth` |
| `resources/js/Layouts/PublicLayout.jsx` | `min-h-screen`, `flex flex-col`, `flex-1` |
| `resources/js/Layouts/AdminLayout.jsx` | `min-h-screen`, `flex flex-col md:flex-row`, `w-64`, `sticky top-0 h-screen`, `flex-1 flex flex-col min-w-0 overflow-y-auto`, `max-w-7xl w-full mx-auto`, `p-4 sm:p-6 lg:p-8` |
| `resources/js/Components/Navigation/Navbar.jsx` | `sticky top-0 z-40`, `max-w-7xl mx-auto px-6 h-20 flex items-center justify-between`, `hidden md:flex items-center space-x-8`, `md:hidden p-2` |
| `resources/js/Components/Navigation/MobileMenu.jsx` | `md:hidden`, `px-6 py-8 overflow-hidden`, `flex flex-col space-y-5`, `mt-8 pt-6 flex flex-col space-y-3` |
| `resources/js/Components/Navigation/Footer.jsx` | `mt-auto py-16`, `max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12`, `mt-16 pt-8 flex flex-col md:flex-row items-center justify-between` |
| `resources/js/Components/UI/Button.jsx` | `inline-flex items-center justify-center font-medium select-none`, `px-3.5 py-1.5`, `px-5 py-2.5`, `px-7 py-3.5`, `tracking-widest uppercase` |
| `resources/js/Components/UI/Badge.jsx` | `inline-block px-2.5 py-1 text-[11px] uppercase tracking-widest font-medium` |
| `resources/js/Components/UI/Input.jsx` | `w-full px-4 py-2.5 text-sm`, `block text-xs uppercase tracking-wider font-medium mb-1.5`, `mt-1.5 text-xs font-medium` |
| `resources/js/Components/UI/Modal.jsx` | `fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6`, `relative w-full`, `overflow-hidden shadow-2xl`, `flex items-center justify-between px-6 py-4`, `p-6` |
| `resources/js/Components/Portfolio/ProjectCard.jsx` | `group`, `block overflow-hidden`, `relative aspect-[16/10] overflow-hidden`, `w-full h-full object-cover group-hover:scale-105`, `absolute top-4 left-4`, `pt-4 pb-2`, `flex items-center justify-between text-xs uppercase tracking-widest` |
| `resources/js/Components/Portfolio/FilterBar.jsx` | `flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6`, `relative flex-1 max-w-md`, `flex items-center justify-between md:justify-end space-x-4` |
| `resources/js/Components/Portfolio/ImageGallery.jsx` | `grid grid-cols-1 md:grid-cols-2 gap-6`, `aspect-[4/3] overflow-hidden`, `relative w-full aspect-[16/10] max-h-[70vh]`, `w-full mt-4 flex items-center justify-between` |
| `resources/js/Pages/Home/Index.jsx` | `relative min-h-[85vh]`, `max-w-4xl mx-auto`, `max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8`, `max-w-7xl mx-auto px-6 py-24`, `grid grid-cols-1 md:grid-cols-3 gap-8`, `grid grid-cols-1 md:grid-cols-3 gap-12` |
| `resources/js/Pages/Projects/Index.jsx` | `max-w-7xl mx-auto px-6 py-16`, `max-w-2xl mb-12`, `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8` |
| `resources/js/Pages/Projects/Show.jsx` | `max-w-7xl mx-auto px-6`, `grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6`, `aspect-[21/9] overflow-hidden`, `grid grid-cols-1 lg:grid-cols-3 gap-16`, `lg:col-span-2`, `grid grid-cols-1 md:grid-cols-3 gap-8` |
| `resources/js/Pages/Studio/Index.jsx` | `max-w-7xl mx-auto px-6 py-20`, `max-w-3xl`, `grid grid-cols-1 md:grid-cols-2 gap-12`, `grid grid-cols-1 md:grid-cols-4 gap-8`, `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8`, `aspect-[4/5] overflow-hidden` |
| `resources/js/Pages/Services/Index.jsx` | `max-w-7xl mx-auto px-6 py-20`, `max-w-3xl`, `grid grid-cols-1 lg:grid-cols-12 gap-8`, `lg:col-span-1`, `lg:col-span-5`, `lg:col-span-6 p-6 sm:p-8 space-y-4` |
| `resources/js/Pages/Contact/Index.jsx` | `max-w-7xl mx-auto px-6 py-20`, `max-w-3xl mb-16`, `grid grid-cols-1 lg:grid-cols-12 gap-16`, `lg:col-span-7 p-8 sm:p-12`, `grid grid-cols-1 sm:grid-cols-2 gap-6`, `lg:col-span-5 space-y-12` |
| `resources/js/Pages/Admin/Auth/Login.jsx` | `min-h-screen flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8`, `w-full max-w-md`, `p-8 sm:p-10 relative`, `space-y-5` |
| `resources/js/Pages/Admin/Dashboard/Index.jsx` | `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-10`, `grid grid-cols-1 lg:grid-cols-2 gap-8`, `overflow-x-auto`, `w-full text-left text-xs` |
| `resources/js/Pages/Admin/Projects/Index.jsx` | `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b gap-4`, `p-4 sm:p-5 mb-6`, `overflow-x-auto`, `w-full text-left text-xs` |
| `resources/js/Pages/Admin/Projects/Create.jsx` / `Edit.jsx` | `flex items-center justify-between pb-6 mb-6 border-b gap-4`, `grid grid-cols-1 lg:grid-cols-3 gap-8`, `lg:col-span-2 space-y-6`, `border-2 border-dashed p-6`, `flex items-center justify-end space-x-3 pt-6 border-t` |
| `resources/js/Pages/Admin/Projects/Gallery.jsx` | `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b gap-4` |
| `resources/js/Components/Admin/ProjectGalleryManager.jsx` | `aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.Group`, `Reorder.Item` |
| `resources/js/Pages/Admin/Inquiries/Index.jsx` | `flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b gap-4`, `flex items-center space-x-2 overflow-x-auto`, `overflow-x-auto`, `w-full text-left text-xs` |
| `resources/js/Pages/Admin/Inquiries/Show.jsx` | `mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4`, `grid grid-cols-1 lg:grid-cols-3 gap-8`, `lg:col-span-2 space-y-6` |

---

### 4.2 Master Matrix: Theme Tokens To Assert in Automated Tests

| Category | Target File | Theme Tokens to Assert |
|---|---|---|
| **Root Template** | `resources/views/app.blade.php` | `<meta name="theme-color" content="#0B0E0C">`<br>`bg-[#0B0E0C]`<br>`text-[#F4F6F0]`<br>`selection:bg-[#1C3024]`<br>`selection:text-[#F4F6F0]` |
| **Global Styles** | `resources/css/app.css` | `background-color: #0B0E0C`<br>`color: #F4F6F0`<br>`background-color: #1C3024` (::selection) |
| **Public Shell** | `resources/js/Layouts/PublicLayout.jsx` | `bg-[#0B0E0C]`<br>`text-[#F4F6F0]`<br>`selection:bg-[#1C3024]` |
| **Admin Shell** | `resources/js/Layouts/AdminLayout.jsx` | Preserves: `#1C2A21`, `#F9F8F6`, `#E6E2DD`<br>Obsidian bases: `#0E1310` or `#0B0E0C`<br>`aria-label=` |
| **Navbar Header** | `resources/js/Components/Navigation/Navbar.jsx` | `bg-[#0B0E0C]/`<br>`backdrop-blur`<br>`border-white/10`<br>`text-[#F4F6F0]` |
| **Mobile Drawer** | `resources/js/Components/Navigation/MobileMenu.jsx` | `bg-[#0E1310]` or `bg-[#0B0E0C]/`<br>`border-white/10`<br>`text-[#F4F6F0]` |
| **Footer** | `resources/js/Components/Navigation/Footer.jsx` | `bg-[#0B0E0C]` or `bg-[#0E1310]`<br>`border-white/10`<br>`text-[#F4F6F0]` |
| **Primitives** | `Button.jsx`, `Badge.jsx`, `Input.jsx`, `Modal.jsx` | Button: `bg-[#1C3024]` or `bg-emerald-600`, `focus:ring-emerald-500`<br>Badge: frosted borders `border-white/10`<br>Input: `bg-[#121814]`, `border-white/10`, `text-[#F4F6F0]`<br>Modal: `bg-[#0E1310]`, `border-white/10` |
| **Public Cards** | `ProjectCard.jsx`, `ImageGallery.jsx` | Image frames: `bg-[#121814]`<br>Titles: `text-[#F4F6F0]`<br>Sage text: `text-[#A7ACA0]` |
| **Public Pages** | Home, Projects, Studio, Services, Contact | Typography: `#F4F6F0`, `#A7ACA0`<br>Panels: `bg-[#0E1310]` or `bg-[#142018]/65`<br>Borders: `border-white/10` |
| **Admin Suite** | Login, Dashboard, Projects, Inquiries | Ground: `bg-[#0B0E0C]`<br>Cards: `bg-[#0E1310]`<br>Borders: `border-white/10`<br>Tables: `bg-[#121814]` thead, `divide-white/10` |

---

## 5. Verification Method

To independently verify the observations, structural layout classes, and token mappings:

1. **Verify Root Blade & Stylesheet Tokens**:
   ```powershell
   Get-Content resources\views\app.blade.php | Select-String "theme-color","bg-","text-","selection:"
   Get-Content resources\css\app.css | Select-String "background-color","color","::selection"
   ```

2. **Verify ChallengerM5_2 Critical Assertions (AdminLayout & ProjectGalleryManager)**:
   ```powershell
   Select-String -Path resources\js\Layouts\AdminLayout.jsx -Pattern "1C2A21","F9F8F6","E6E2DD","aria-label"
   Select-String -Path resources\js\Components\Admin\ProjectGalleryManager.jsx -Pattern "aspect-\[4/3\]","bg-\[#E6E2DD\]","loading=","decoding=","Reorder"
   ```

3. **Verify Structural Grid Classes Across Pages**:
   ```powershell
   Select-String -Path resources\js\Pages\Home\Index.jsx -Pattern "grid-cols"
   Select-String -Path resources\js\Pages\Projects\Index.jsx -Pattern "grid-cols"
   Select-String -Path resources\js\Pages\Studio\Index.jsx -Pattern "grid-cols"
   Select-String -Path resources\js\Pages\Services\Index.jsx -Pattern "grid-cols"
   Select-String -Path resources\js\Pages\Contact\Index.jsx -Pattern "grid-cols"
   Select-String -Path resources\js\Pages\Admin\Dashboard\Index.jsx -Pattern "grid-cols"
   Select-String -Path resources\js\Pages\Admin\Projects\Create.jsx -Pattern "grid-cols"
   ```

4. **Verify Baseline Test Suite Execution**:
   ```powershell
   if (Test-Path storage\framework\testing\disks) { Remove-Item -Recurse -Force storage\framework\testing\disks }
   php artisan test --filter=ChallengerM5_2_FrontendLocalizationStressTest
   ```
   *Expected result*: 100% pass across all assertions.
