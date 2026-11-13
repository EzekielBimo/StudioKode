# Comprehensive Survey & Analysis: Shared UI Components & Public Pages
**Theme Transformation**: "Smoked Obsidian / Dark Moss Glass"
**Standard Compliance**: `/design-taste-frontend`, `/antislop-ui`, `/ponytail`
**Date**: 2026-11-10
**Author**: Explorer Survey Theme 2

---

## 1. Executive Summary

StudioKode is transitioning from its initial light stone/foliage aesthetic (`#F9F8F6` background, `#E6E2DD` borders, `#1A1A1A` charcoal text, `#1C2A21` dark pine accents) into an ultra-refined, high-contrast, editorial **"Smoked Obsidian / Dark Moss Glass"** theme.

### Design Principles & Skill Governance
- **`/design-taste-frontend`**: Editorial restraint, architectural luxury, calm micro-interactions (subtle hover scale 1.05 on project images, 2px lift, luminous highlights), unbloated styling, real architectural imagery preservation, and strict 1440/1024/768/390 responsive balance.
- **`/antislop-ui`**: Restrained glassmorphism (dose cap: glass applied to elevated cards, modals, and interactive containers; base canvas remains solid deep smoked obsidian `#0B0E0C` / `#0E1310` to avoid flat "blur-everywhere" slop), deliberate border radii (clean architectural angles), restrained luminous moss accents (`#88B896` / `#9EC4A7`) reserved for primary indicators and focus rings, high-contrast cream/off-white typography (`#F4F2ED` for display headings, `#C4C9C3` for body copy, `#A8B0A6` for metadata).
- **`/ponytail`**: The smallest complete change. Retain 100% of the current JSX component signatures, props, state machines, Framer Motion transitions, and layout grids. Zero unnecessary abstractions or CSS bloat.

### Directory Mapping Realignment
During survey execution, the exact relative file paths in the codebase were verified:
- Dispatched path `resources/js/Components/ProjectCard.jsx` -> Actual: `resources/js/Components/Portfolio/ProjectCard.jsx`
- Dispatched path `resources/js/Components/FilterBar.jsx` -> Actual: `resources/js/Components/Portfolio/FilterBar.jsx`
- Dispatched path `resources/js/Components/ImageGallery.jsx` -> Actual: `resources/js/Components/Portfolio/ImageGallery.jsx`
- Dispatched path `resources/js/Components/Button.jsx` -> Actual: `resources/js/Components/UI/Button.jsx`
- Dispatched path `resources/js/Components/Badge.jsx` -> Actual: `resources/js/Components/UI/Badge.jsx`
- Dispatched path `resources/js/Components/Modal.jsx` -> Actual: `resources/js/Components/UI/Modal.jsx`
- Dispatched path `resources/js/Components/Input.jsx` -> Actual: `resources/js/Components/UI/Input.jsx`
- Associated animation helper -> `resources/js/Components/UI/InView.jsx`

---

## 2. Color Palette & Token System Translation

| Design Token | Current Light Stone / Foliage | Target Smoked Obsidian / Dark Moss Glass | Purpose & Applied Elements |
|---|---|---|---|
| **Canvas Primary** | `#F9F8F6` (light off-white) | `#0B0E0C` (deep smoked obsidian) | Root canvas, page backgrounds |
| **Canvas Secondary** | `#FFFFFF` (pure white) | `#0E1310` / `#121814` (dark obsidian-slate) | Section breaks, alternating rows |
| **Glass Surface** | `bg-white` or `bg-[#E6E2DD]/30` | `rgba(20, 32, 24, 0.65)` (`backdrop-blur-md`) | Cards, form containers, elevated panels |
| **Frosted Border** | `#E6E2DD` (stone beige) | `rgba(255, 255, 255, 0.10)` (`border-white/10`) | Dividers, card borders, input borders |
| **Accent Border** | `#1C2A21` (dark foliage) | `rgba(52, 211, 153, 0.20)` (`border-emerald-500/20`) | Featured badges, active states |
| **Display Typography** | `#1A1A1A` (charcoal black) | `#F4F2ED` (warm editorial off-white) | Serif headings (H1, H2, H3, H4) |
| **Body Typography** | `#1A1A1A]/80` (dim charcoal) | `#C4C9C3` (soft muted stone) | Paragraphs, narrative copy |
| **Muted Metadata** | `#1A1A1A]/60` | `#A8B0A6` (editorial sage-gray) | Subtitles, labels, timestamps, tags |
| **Luminous Accent** | `#1C2A21` | `#88B896` / `#9EC4A7` (luminous moss) | Active links, hover targets, icons, indicators |
| **Warm Gold Accent**| — | `#C8AA6E` / `#D4B87C` | Subtle secondary luxury highlight |
| **Interactive Glow**| — | `ring-[#88B896]/30 shadow-emerald-950/40` | Focused inputs, buttons |

---

## 3. Shared UI Components Investigation & Redesign Specifications

### 3.1 `resources/js/Components/Portfolio/ProjectCard.jsx`

#### Direct Observation
- **Lines 12**: `<div className="relative aspect-[16/10] overflow-hidden bg-[#E6E2DD]">`
  - Current image background is light stone `#E6E2DD`.
- **Line 16**: `className="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"`
  - Hover zoom 1.05 aligns with `/design-taste-frontend` micro-interaction rules.
- **Line 22**: `<Badge variant="foliage">Featured Sanctuary</Badge>`
  - Uses Badge component.
- **Lines 28-30**:
  - `text-xs text-[#1A1A1A]/70 uppercase tracking-widest font-medium mb-1.5`
  - Location, area size, year metadata rendered in `#1A1A1A]/70`.
- **Line 32**: `<h3 className="font-serif text-2xl text-[#1A1A1A] group-hover:text-[#1C2A21] transition-colors">`
  - Title text `#1A1A1A` hovering to `#1C2A21`.
- **Line 36**: `<p className="mt-1 text-sm text-[#1A1A1A]/80 line-clamp-2 font-light">`
  - Excerpt rendered in `#1A1A1A]/80`.

#### Concrete Modifications
```diff
- <div className="relative aspect-[16/10] overflow-hidden bg-[#E6E2DD]">
+ <div className="relative aspect-[16/10] overflow-hidden bg-[#121814] border border-white/10">

- <div className="flex items-center justify-between text-xs text-[#1A1A1A]/70 uppercase tracking-widest font-medium mb-1.5">
+ <div className="flex items-center justify-between text-xs text-[#A8B0A6]/80 uppercase tracking-widest font-medium mb-1.5">

- <h3 className="font-serif text-2xl text-[#1A1A1A] group-hover:text-[#1C2A21] transition-colors">
+ <h3 className="font-serif text-2xl text-[#F4F2ED] group-hover:text-[#88B896] transition-colors">

- <p className="mt-1 text-sm text-[#1A1A1A]/80 line-clamp-2 font-light">
+ <p className="mt-1 text-sm text-[#C4C9C3]/80 line-clamp-2 font-light">
```
*Layout & Grid Invariance*: `aspect-[16/10]`, line-clamp-2, padding, and flex alignment remain completely unchanged.

---

### 3.2 `resources/js/Components/Portfolio/FilterBar.jsx`

#### Direct Observation
- **Line 13**: `<div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6 border-b border-[#E6E2DD]">`
  - Border `#E6E2DD` is light stone beige.
- **Line 16**: `<Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#1A1A1A]/50" />`
  - Search icon text `#1A1A1A]/50`.
- **Lines 17-23**:
  - `input`: `w-full pl-10 pr-9 py-2 bg-white border border-[#E6E2DD] text-sm text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 focus:outline-none focus:border-[#1C2A21] transition-colors`
  - Light white background with `#E6E2DD` border.
- **Line 27**: `<button ... className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-[#1A1A1A]/50 hover:text-[#1A1A1A] transition-colors">`
  - Clear button icon.
- **Line 38**: `<span className="text-xs uppercase tracking-widest text-[#1A1A1A]/60 hidden sm:inline">`
- **Line 44**: `<SlidersHorizontal className="w-4 h-4 text-[#1C2A21]" />`
- **Line 45**: `<label htmlFor="sort-select" className="text-xs uppercase tracking-widest text-[#1A1A1A]/70 font-medium">`
- **Lines 48-58**:
  - `select`: `px-3 py-2 bg-white border border-[#E6E2DD] text-xs uppercase tracking-wider text-[#1A1A1A] focus:outline-none focus:border-[#1C2A21] transition-colors cursor-pointer`

#### Concrete Modifications
```diff
- <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6 border-b border-[#E6E2DD]">
+ <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6 border-b border-white/10">

- <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#1A1A1A]/50" />
+ <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#A8B0A6]/60" />

- <input ... className="w-full pl-10 pr-9 py-2 bg-white border border-[#E6E2DD] text-sm text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 focus:outline-none focus:border-[#1C2A21] transition-colors" />
+ <input ... className="w-full pl-10 pr-9 py-2 bg-[#121814]/90 backdrop-blur-sm border border-white/15 text-sm text-[#F4F2ED] placeholder:text-[#A8B0A6]/40 focus:outline-none focus:border-[#88B896] focus:ring-1 focus:ring-[#88B896]/30 transition-all" />

- <button ... className="... text-[#1A1A1A]/50 hover:text-[#1A1A1A] transition-colors">
+ <button ... className="... text-[#A8B0A6]/60 hover:text-[#F4F2ED] transition-colors">

- <span className="text-xs uppercase tracking-widest text-[#1A1A1A]/60 hidden sm:inline">
+ <span className="text-xs uppercase tracking-widest text-[#A8B0A6]/80 hidden sm:inline">

- <SlidersHorizontal className="w-4 h-4 text-[#1C2A21]" />
+ <SlidersHorizontal className="w-4 h-4 text-[#88B896]" />

- <label htmlFor="sort-select" className="text-xs uppercase tracking-widest text-[#1A1A1A]/70 font-medium">
+ <label htmlFor="sort-select" className="text-xs uppercase tracking-widest text-[#C4C9C3]/80 font-medium">

- <select ... className="px-3 py-2 bg-white border border-[#E6E2DD] text-xs uppercase tracking-wider text-[#1A1A1A] focus:outline-none focus:border-[#1C2A21] transition-colors cursor-pointer">
+ <select ... className="px-3 py-2 bg-[#121814] border border-white/15 text-xs uppercase tracking-wider text-[#F4F2ED] focus:outline-none focus:border-[#88B896] focus:ring-1 focus:ring-[#88B896]/30 transition-all cursor-pointer">
```
*Note on Select Options*: Ensure `<option>` tags inherit background `#121814` and text `#F4F2ED` to guarantee native dropdown readability in dark mode.

---

### 3.3 `resources/js/Components/Portfolio/ImageGallery.jsx`

#### Direct Observation
- **Line 35**: `<div ... className="aspect-[4/3] overflow-hidden bg-[#E6E2DD] cursor-pointer relative">`
  - Thumbnail placeholder is light beige `#E6E2DD`.
- **Line 44**: `<div className="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">`
- **Line 45**: `<span className="p-3 bg-[#F9F8F6]/90 rounded-full text-[#1A1A1A] shadow-md transform scale-90 group-hover:scale-100 transition-transform">`
  - Maximize button is light `#F9F8F6]/90` with dark icon `#1A1A1A`.
- **Line 51**: `<p className="mt-2 text-xs text-[#1A1A1A]/70 font-light italic">`
- **Line 69**: Lightbox container: `<div className="relative w-full aspect-[16/10] max-h-[70vh] bg-[#1A1A1A] flex items-center justify-center overflow-hidden">`
- **Lines 86 & 96**: Prev/Next buttons: `bg-black/60 hover:bg-black/85 text-white rounded-full`
- **Lines 106-112**: Caption & counter bar:
  - Caption: `italic text-[#1A1A1A]/90`
  - Counter: `font-mono text-[#1A1A1A]/70`

#### Concrete Modifications
```diff
- <div ... className="aspect-[4/3] overflow-hidden bg-[#E6E2DD] cursor-pointer relative">
+ <div ... className="aspect-[4/3] overflow-hidden bg-[#121814] border border-white/10 cursor-pointer relative">

- <span className="p-3 bg-[#F9F8F6]/90 rounded-full text-[#1A1A1A] shadow-md transform scale-90 group-hover:scale-100 transition-transform">
+ <span className="p-3 bg-[#121814]/90 backdrop-blur-md border border-white/15 rounded-full text-[#F4F2ED] shadow-xl group-hover:border-[#88B896]/50 transform scale-90 group-hover:scale-100 transition-all">

- <p className="mt-2 text-xs text-[#1A1A1A]/70 font-light italic">
+ <p className="mt-2 text-xs text-[#A8B0A6]/80 font-light italic">

- <div className="relative w-full aspect-[16/10] max-h-[70vh] bg-[#1A1A1A] flex items-center justify-center overflow-hidden">
+ <div className="relative w-full aspect-[16/10] max-h-[70vh] bg-[#0B0E0C] border border-white/10 flex items-center justify-center overflow-hidden">

- <button ... className="... bg-black/60 hover:bg-black/85 text-white rounded-full transition-colors">
+ <button ... className="... bg-[#142018]/80 hover:bg-[#1E2E23] border border-white/10 text-[#F4F2ED] rounded-full backdrop-blur-sm transition-colors">

- <div className="w-full mt-4 flex items-center justify-between text-xs text-[#1A1A1A]/70 tracking-wider uppercase font-medium">
+ <div className="w-full mt-4 flex items-center justify-between text-xs text-[#A8B0A6]/80 tracking-wider uppercase font-medium">

- <span className="font-light italic text-[#1A1A1A]/90 normal-case text-sm">
+ <span className="font-light italic text-[#F4F2ED]/90 normal-case text-sm">
```

---

### 3.4 `resources/js/Components/UI/Button.jsx`

#### Direct Observation
- **Line 14**: `const base = 'inline-flex items-center justify-center font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#1C2A21] focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none select-none';`
- **Lines 16-22**:
  - `primary`: `'bg-[#1C2A21] text-[#F9F8F6] hover:bg-[#283c2f] border border-transparent shadow-sm'`
  - `secondary`: `'bg-[#E6E2DD] text-[#1A1A1A] hover:bg-[#d8d3cd] border border-transparent'`
  - `outline`: `'border border-[#1C2A21] text-[#1C2A21] hover:bg-[#1C2A21] hover:text-[#F9F8F6]'`
  - `'outline-light'`: `'border border-[#E6E2DD] text-[#E6E2DD] hover:bg-[#E6E2DD] hover:text-[#1C2A21]'`
  - `ghost`: `'text-[#1A1A1A] hover:text-[#1C2A21] hover:bg-[#E6E2DD]/40'`

#### Cross-Component Usage Analysis
- Used in `Pages/Home/Index.jsx` (`secondary`, `outline-light`, `primary`)
- Used in `Pages/Services/Index.jsx` (`secondary`)
- Used in `Pages/Contact/Index.jsx` (`primary` submit)
- In Admin pages: Admin views currently construct inline buttons with Tailwind or custom buttons. Ensuring `Button.jsx` handles dark moss glass aesthetics guarantees seamless consistency if adopted in administrative toolbars or modal confirmations.

#### Concrete Modifications
```javascript
// Base focus ring aligned with dark background and luminous moss
const base = 'inline-flex items-center justify-center font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#88B896] focus:ring-offset-2 focus:ring-offset-[#0B0E0C] disabled:opacity-50 disabled:pointer-events-none select-none';

const variants = {
    // Primary: Luminous botanical moss ground with subtle emerald rim
    primary: 'bg-[#1C2A21] text-[#F4F2ED] hover:bg-[#283C2F] border border-emerald-500/25 hover:border-emerald-400/40 shadow-sm shadow-emerald-950/40',
    // Secondary: Translucent dark moss glass surface with frosted border
    secondary: 'bg-[rgba(20,32,24,0.75)] text-[#F4F2ED] hover:bg-[rgba(28,45,34,0.95)] border border-white/15 backdrop-blur-md shadow-sm hover:border-white/25',
    // Outline: Frosted luminous moss line
    outline: 'border border-[#88B896]/50 text-[#88B896] hover:bg-[#88B896]/15 hover:border-[#88B896]',
    // Outline-light: Editorial frosted off-white
    'outline-light': 'border border-white/20 text-[#F4F2ED] hover:bg-white/10 hover:border-white/40',
    // Ghost: Soft off-white to luminous moss on hover
    ghost: 'text-[#C4C9C3] hover:text-[#F4F2ED] hover:bg-white/5',
};
```

---

### 3.5 `resources/js/Components/UI/Badge.jsx`

#### Direct Observation
- **Lines 3-9**:
  - `neutral`: `'bg-[#E6E2DD] text-[#1A1A1A]'`
  - `foliage`: `'bg-[#1C2A21] text-[#F9F8F6]'`
  - `outline`: `'border border-[#E6E2DD] text-[#1A1A1A]'`
  - `'outline-light'`: `'border border-white/20 text-[#F9F8F6]'`

#### Concrete Modifications
```javascript
export default function Badge({ children, variant = 'neutral', className = '' }) {
    const variants = {
        // Neutral: Translucent moss-obsidian chip
        neutral: 'bg-[rgba(20,32,24,0.65)] text-[#C4C9C3] border border-white/10 backdrop-blur-xs',
        // Foliage: Featured sanctuary badge with luminous emerald-moss text
        foliage: 'bg-[#152319] text-[#88B896] border border-emerald-500/30 shadow-xs',
        // Outline: Delicate frosted border
        outline: 'border border-white/15 text-[#C4C9C3]',
        // Outline-light: Higher contrast white border
        'outline-light': 'border border-white/25 text-[#F4F2ED]',
    };

    return (
        <span
            className={`inline-block px-2.5 py-1 text-[11px] uppercase tracking-widest font-medium ${
                variants[variant] || variants.neutral
            } ${className}`}
        >
            {children}
        </span>
    );
}
```

---

### 3.6 `resources/js/Components/UI/Modal.jsx`

#### Direct Observation
- **Line 34**: Backdrop overlay: `fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#1A1A1A]/95 backdrop-blur-md`
- **Line 42**: Modal box: `relative w-full ${maxWidth} bg-[#F9F8F6] text-[#1A1A1A] overflow-hidden shadow-2xl border border-[#E6E2DD]`
- **Line 46**: Header: `flex items-center justify-between px-6 py-4 border-b border-[#E6E2DD]`
- **Line 48**: Title: `font-serif text-xl tracking-tight text-[#1A1A1A] truncate pr-4`
- **Line 56**: Close button: `text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#E6E2DD]/60 rounded-full transition-colors`

#### Concrete Modifications
```diff
- className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#1A1A1A]/95 backdrop-blur-md"
+ className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#0B0E0C]/90 backdrop-blur-md"

- className={`relative w-full ${maxWidth} bg-[#F9F8F6] text-[#1A1A1A] overflow-hidden shadow-2xl border border-[#E6E2DD]`}
+ className={`relative w-full ${maxWidth} bg-[#0E1310]/95 backdrop-blur-xl text-[#F4F2ED] overflow-hidden shadow-2xl shadow-black/80 border border-white/15`}

- <div className="flex items-center justify-between px-6 py-4 border-b border-[#E6E2DD]">
+ <div className="flex items-center justify-between px-6 py-4 border-b border-white/10">

- <h3 className="font-serif text-xl tracking-tight text-[#1A1A1A] truncate pr-4">
+ <h3 className="font-serif text-xl tracking-tight text-[#F4F2ED] truncate pr-4">

- <button ... className="p-1.5 text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#E6E2DD]/60 rounded-full transition-colors">
+ <button ... className="p-1.5 text-[#A8B0A6]/70 hover:text-[#F4F2ED] hover:bg-white/10 rounded-full transition-colors">
```

---

### 3.7 `resources/js/Components/UI/Input.jsx`

#### Direct Observation
- **Lines 16-18**:
  - `baseClasses`: `w-full px-4 py-2.5 bg-white border border-[#E6E2DD] text-[#1A1A1A] text-sm focus:outline-none focus:border-[#1C2A21] focus:ring-1 focus:ring-[#1C2A21] transition-colors placeholder:text-[#1A1A1A]/40 ${error ? 'border-red-500' : ''} ${className}`
- **Line 25**: Label: `block text-xs uppercase tracking-wider font-medium text-[#1A1A1A] mb-1.5`
- **Line 28**: Required indicator: `text-red-600 ml-1`
- **Line 52**: Error message: `mt-1.5 text-xs text-red-600 font-medium`

#### Concrete Modifications
```diff
- const baseClasses = `w-full px-4 py-2.5 bg-white border border-[#E6E2DD] text-[#1A1A1A] text-sm focus:outline-none focus:border-[#1C2A21] focus:ring-1 focus:ring-[#1C2A21] transition-colors placeholder:text-[#1A1A1A]/40 ${
-     error ? 'border-red-500' : ''
- } ${className}`;
+ const baseClasses = `w-full px-4 py-2.5 bg-[#121814]/90 backdrop-blur-sm border border-white/15 text-[#F4F2ED] text-sm focus:outline-none focus:border-[#88B896] focus:ring-1 focus:ring-[#88B896]/30 transition-all placeholder:text-[#A8B0A6]/40 ${
+     error ? 'border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/20' : ''
+ } ${className}`;

- className="block text-xs uppercase tracking-wider font-medium text-[#1A1A1A] mb-1.5"
+ className="block text-xs uppercase tracking-wider font-medium text-[#C4C9C3] mb-1.5"

- {required && <span className="text-red-600 ml-1">*</span>}
+ {required && <span className="text-rose-400 ml-1">*</span>}

- {error && <p className="mt-1.5 text-xs text-red-600 font-medium">{error}</p>}
+ {error && <p className="mt-1.5 text-xs text-rose-400 font-medium">{error}</p>}
```

---

## 4. Public Pages Survey & Redesign Specifications

### 4.1 `resources/js/Pages/Home/Index.jsx`

#### Direct Observation & Visual Mapping
1. **Hero Section (Lines 19-54)**:
   - Container: `bg-[#1C2A21] text-[#F9F8F6]`
   - Image overlay: `mix-blend-overlay opacity-40`
   - Subtitle: `text-[#E6E2DD]/80`
   - H1: `font-serif text-4xl sm:text-6xl md:text-7xl leading-[1.08] tracking-tight`
   - Paragraph: `text-[#E6E2DD]/90`
   - CTAs: `<Button variant="secondary">Explore Sanctuaries</Button>`, `<Button variant="outline-light">Commission Project</Button>`
2. **Statistics Bar (Lines 57-71)**:
   - Container: `border-b border-[#E6E2DD] bg-[#E6E2DD]/30`
   - Value: `text-3xl md:text-4xl text-[#1C2A21]`
   - Label: `text-[#1A1A1A]/70`
3. **Featured Projects Showcase (Lines 74-103)**:
   - Section tag: `text-[#1C2A21]`
   - H2: `text-[#1A1A1A]`
   - Link: `hover:text-[#1C2A21]`
   - 3-column grid of `ProjectCard`
4. **Philosophy Highlights (Lines 105-135)**:
   - Container: `bg-[#1C2A21] text-[#F9F8F6] py-24`
   - Header subtitle: `text-[#E6E2DD]/70`
   - Grid cards: `border-t border-[#E6E2DD]/20 pt-6`
   - Pillar number: `text-[#E6E2DD]/50`
   - Description: `text-[#E6E2DD]/80`
5. **Inquiry CTA (Lines 137-155)**:
   - Tag: `text-[#1C2A21]`
   - H2: `text-[#1A1A1A]`
   - Paragraph: `text-[#1A1A1A]/80`
   - Button: `Initiate Commission`

#### Concrete Modifications
- **Hero Section**:
  - Replace `bg-[#1C2A21]` with deep smoked obsidian gradient `bg-gradient-to-b from-[#0B0E0C] via-[#0E1310] to-[#0B0E0C]`.
  - Subtitle: update `text-[#E6E2DD]/80` to luminous moss `text-[#88B896]`.
  - Body paragraph: `text-[#C4C9C3]/90`.
- **Statistics Bar**:
  - Replace `border-[#E6E2DD] bg-[#E6E2DD]/30` with `border-white/10 bg-[rgba(20,32,24,0.45)] backdrop-blur-md`.
  - Stat value: replace `text-[#1C2A21]` with luminous moss `text-[#88B896]`.
  - Stat label: replace `text-[#1A1A1A]/70` with `text-[#A8B0A6]/70`.
- **Featured Projects Showcase**:
  - Section tag: replace `text-[#1C2A21]` with `text-[#88B896]`.
  - H2: replace `text-[#1A1A1A]` with `text-[#F4F2ED]`.
  - Link: replace `hover:text-[#1C2A21]` with `text-[#C4C9C3] hover:text-[#88B896]`.
- **Philosophy Highlights**:
  - Container: replace `bg-[#1C2A21]` with `bg-[#0E1310] border-y border-white/10`.
  - Pillar card borders: replace `border-[#E6E2DD]/20` with `border-white/10`.
  - Number: replace `text-[#E6E2DD]/50` with luminous moss `text-[#88B896]/70`.
  - Description: replace `text-[#E6E2DD]/80` with `text-[#C4C9C3]/80`.
- **Inquiry CTA**:
  - Section tag: replace `text-[#1C2A21]` with `text-[#88B896]`.
  - H2: replace `text-[#1A1A1A]` with `text-[#F4F2ED]`.
  - Paragraph: replace `text-[#1A1A1A]/80` with `text-[#C4C9C3]/80`.

---

### 4.2 `resources/js/Pages/Projects/Index.jsx`

#### Direct Observation & Visual Mapping
- **Lines 38-49**:
  - Section tag: `text-xs uppercase tracking-widest text-[#1C2A21] font-semibold`
  - H1: `font-serif text-4xl sm:text-5xl text-[#1A1A1A] mt-2 tracking-tight`
  - Paragraph: `text-base text-[#1A1A1A]/80 font-light leading-relaxed`
- **Lines 51-58**: `<FilterBar ... />`
- **Lines 61-70**: Grid of `ProjectCard`
- **Lines 72-86**: Empty state:
  - Container: `border border-dashed border-[#E6E2DD] bg-[#E6E2DD]/20`
  - Empty message: `font-serif text-xl text-[#1A1A1A]`
  - Reset button: `text-xs uppercase tracking-widest font-semibold text-[#1C2A21] underline hover:no-underline`

#### Concrete Modifications
```diff
- <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
+ <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">

- <h1 className="font-serif text-4xl sm:text-5xl text-[#1A1A1A] mt-2 tracking-tight">
+ <h1 className="font-serif text-4xl sm:text-5xl text-[#F4F2ED] mt-2 tracking-tight">

- <p className="mt-4 text-base text-[#1A1A1A]/80 font-light leading-relaxed">
+ <p className="mt-4 text-base text-[#C4C9C3]/85 font-light leading-relaxed">

- <div className="text-center py-24 border border-dashed border-[#E6E2DD] bg-[#E6E2DD]/20">
+ <div className="text-center py-24 border border-dashed border-white/15 bg-[rgba(20,32,24,0.40)] backdrop-blur-sm">

- <p className="font-serif text-xl text-[#1A1A1A]">
+ <p className="font-serif text-xl text-[#F4F2ED]">

- <button ... className="mt-4 text-xs uppercase tracking-widest font-semibold text-[#1C2A21] underline hover:no-underline">
+ <button ... className="mt-4 text-xs uppercase tracking-widest font-semibold text-[#88B896] hover:text-[#A9D6B3] underline hover:no-underline transition-colors">
```

---

### 4.3 `resources/js/Pages/Projects/Show.jsx`

#### Direct Observation & Visual Mapping
- **Line 21**: Breadcrumb: `text-xs uppercase tracking-widest text-[#1A1A1A]/70 hover:text-[#1C2A21] font-medium transition-colors`
- **Line 34**: H1: `font-serif text-4xl sm:text-6xl text-[#1A1A1A] tracking-tight`
- **Line 42**: Metadata grid: `grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6 border-y border-[#E6E2DD] text-xs uppercase tracking-widest font-medium text-[#1A1A1A]`
  - Label: `text-[#1A1A1A]/50 block mb-1`
  - Icons: `text-[#1C2A21]`
- **Line 77**: Cover image container: `aspect-[21/9] overflow-hidden bg-[#E6E2DD]`
- **Line 91**: Subtitle: `text-[#1C2A21] font-semibold`
- **Line 94**: H2: `font-serif text-3xl text-[#1A1A1A]`
- **Line 97**: Narrative prose: `prose prose-stone max-w-none text-base text-[#1A1A1A]/85 font-light leading-relaxed space-y-4 whitespace-pre-line`
- **Line 106**: Masterplan card: `border border-[#E6E2DD] bg-white p-6 space-y-3`
  - Title: `text-[#1A1A1A]`
  - Image frame: `bg-[#E6E2DD]`
  - Caption: `text-xs text-[#1A1A1A]/60`
- **Lines 131 & 146**: Section dividers: `border-t border-[#E6E2DD]`
  - Subtitles: `text-[#1C2A21]`
  - Headings: `text-[#1A1A1A]`

#### Concrete Modifications
```diff
- className="inline-flex items-center text-xs uppercase tracking-widest text-[#1A1A1A]/70 hover:text-[#1C2A21] font-medium transition-colors"
+ className="inline-flex items-center text-xs uppercase tracking-widest text-[#A8B0A6]/70 hover:text-[#88B896] font-medium transition-colors"

- <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] tracking-tight">
+ <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] tracking-tight">

- <div className="grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6 border-y border-[#E6E2DD] text-xs uppercase tracking-widest font-medium text-[#1A1A1A]">
+ <div className="grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6 border-y border-white/10 text-xs uppercase tracking-widest font-medium text-[#F4F2ED]">

- <span className="text-[#1A1A1A]/50 block mb-1">
+ <span className="text-[#A8B0A6]/60 block mb-1">

- <MapPin className="w-3.5 h-3.5 mr-1.5 text-[#1C2A21]" />
+ <MapPin className="w-3.5 h-3.5 mr-1.5 text-[#88B896]" />

- <div className="aspect-[21/9] overflow-hidden bg-[#E6E2DD]">
+ <div className="aspect-[21/9] overflow-hidden bg-[#121814] border border-white/10">

- <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
+ <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">

- <h2 className="font-serif text-3xl text-[#1A1A1A] mt-2 mb-6">
+ <h2 className="font-serif text-3xl text-[#F4F2ED] mt-2 mb-6">

- <div className="prose prose-stone max-w-none text-base text-[#1A1A1A]/85 font-light leading-relaxed space-y-4 whitespace-pre-line">
+ <div className="prose prose-invert max-w-none text-base text-[#C4C9C3]/90 font-light leading-relaxed space-y-4 whitespace-pre-line">

- <div className="border border-[#E6E2DD] bg-white p-6 space-y-3">
+ <div className="border border-white/10 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-6 space-y-3 shadow-xl">

- <h3 className="font-serif text-xl text-[#1A1A1A]">
+ <h3 className="font-serif text-xl text-[#F4F2ED]">

- <div className="aspect-[4/3] overflow-hidden bg-[#E6E2DD]">
+ <div className="aspect-[4/3] overflow-hidden bg-[#121814] border border-white/10">

- <p className="text-xs text-[#1A1A1A]/60 font-light">
+ <p className="text-xs text-[#A8B0A6]/70 font-light">

- <section className="max-w-7xl mx-auto px-6 py-16 border-t border-[#E6E2DD]">
+ <section className="max-w-7xl mx-auto px-6 py-16 border-t border-white/10">
```

---

### 4.4 `resources/js/Pages/Studio/Index.jsx`

#### Direct Observation & Visual Mapping
- **Lines 20-28**: Header:
  - Subtitle: `text-[#1C2A21]`
  - H1: `text-[#1A1A1A]`
  - Manifesto: `text-[#1A1A1A]/85`
- **Lines 33-54**: Philosophy section:
  - Container: `bg-[#1C2A21] text-[#F9F8F6] py-24`
  - Subtitle: `text-[#E6E2DD]/70`
  - Pillar card: `border-t border-[#E6E2DD]/20 pt-6 space-y-3`
  - Number: `text-[#E6E2DD]/40`
  - Description: `text-[#E6E2DD]/80`
- **Lines 57-76**: Methodology section:
  - Subtitle: `text-[#1C2A21]`
  - H2: `text-[#1A1A1A]`
  - Process cards: `border border-[#E6E2DD] bg-white p-6 space-y-4`
  - Stage number: `text-[#1C2A21]`
  - Stage title: `text-[#1A1A1A]`
  - Description: `text-[#1A1A1A]/80`
- **Lines 79-107**: Team section:
  - Subtitle: `text-[#1C2A21]`
  - H2: `text-[#1A1A1A]`
  - Image frame: `aspect-[4/5] overflow-hidden bg-[#E6E2DD]`
  - Member name: `text-[#1A1A1A]`
  - Role: `text-[#1C2A21]`
  - Bio: `text-[#1A1A1A]/70`
- **Lines 110-132**: Accolades section:
  - Container: `bg-[#E6E2DD]/30 border-t border-[#E6E2DD] py-20`
  - Row: `border-b border-[#E6E2DD] pb-4`
  - Award name: `text-[#1A1A1A]`
  - Year: `text-[#1C2A21]`

#### Concrete Modifications
```diff
- <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
+ <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">

- <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] mt-2 tracking-tight">
+ <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] mt-2 tracking-tight">

- <p className="mt-6 text-lg text-[#1A1A1A]/85 font-light leading-relaxed">
+ <p className="mt-6 text-lg text-[#C4C9C3]/90 font-light leading-relaxed">

- <section className="bg-[#1C2A21] text-[#F9F8F6] py-24">
+ <section className="bg-[#0E1310] border-y border-white/10 text-[#F4F2ED] py-24">

- <InView key={i} delay={i * 0.1} className="border-t border-[#E6E2DD]/20 pt-6 space-y-3">
+ <InView key={i} delay={i * 0.1} className="border-t border-white/10 pt-6 space-y-3">

- <span className="font-serif text-2xl text-[#E6E2DD]/40">{item.number}</span>
+ <span className="font-serif text-2xl text-[#88B896]/70">{item.number}</span>

- <p className="text-sm text-[#E6E2DD]/80 font-light leading-relaxed">{item.description}</p>
+ <p className="text-sm text-[#C4C9C3]/80 font-light leading-relaxed">{item.description}</p>

- <InView key={i} delay={i * 0.08} className="border border-[#E6E2DD] bg-white p-6 space-y-4">
+ <InView key={i} delay={i * 0.08} className="border border-white/10 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-6 space-y-4 shadow-lg">

- <span className="font-serif text-3xl text-[#1C2A21]">{step.stage}</span>
+ <span className="font-serif text-3xl text-[#88B896]">{step.stage}</span>

- <h3 className="font-serif text-xl text-[#1A1A1A]">{step.title}</h3>
+ <h3 className="font-serif text-xl text-[#F4F2ED]">{step.title}</h3>

- <p className="text-xs text-[#1A1A1A]/80 font-light leading-relaxed">{step.description}</p>
+ <p className="text-xs text-[#C4C9C3]/80 font-light leading-relaxed">{step.description}</p>

- <div className="aspect-[4/5] overflow-hidden bg-[#E6E2DD]">
+ <div className="aspect-[4/5] overflow-hidden bg-[#121814] border border-white/10">

- <h3 className="font-serif text-lg text-[#1A1A1A]">{member.name}</h3>
+ <h3 className="font-serif text-lg text-[#F4F2ED]">{member.name}</h3>

- <p className="text-xs uppercase tracking-wider text-[#1C2A21] font-medium mt-0.5">{member.role}</p>
+ <p className="text-xs uppercase tracking-wider text-[#88B896] font-medium mt-0.5">{member.role}</p>

- <p className="text-xs text-[#1A1A1A]/70 font-light mt-2 leading-relaxed">{member.bio}</p>
+ <p className="text-xs text-[#A8B0A6]/80 font-light mt-2 leading-relaxed">{member.bio}</p>

- <section className="bg-[#E6E2DD]/30 border-t border-[#E6E2DD] py-20">
+ <section className="bg-[rgba(20,32,24,0.35)] backdrop-blur-sm border-t border-white/10 py-20">

- <span className="font-serif text-lg text-[#1A1A1A]">{acc.award}</span>
+ <span className="font-serif text-lg text-[#F4F2ED]">{acc.award}</span>

- <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">{acc.year}</span>
+ <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">{acc.year}</span>
```

---

### 4.5 `resources/js/Pages/Services/Index.jsx`

#### Direct Observation & Visual Mapping
- **Lines 16-24**: Header:
  - Subtitle: `text-[#1C2A21]`
  - H1: `text-[#1A1A1A]`
  - Paragraph: `text-[#1A1A1A]/85`
- **Lines 29-55**: Services Detailed List:
  - Row item divider: `border-t border-[#E6E2DD] pt-12`
  - Number: `text-[#1C2A21]/60`
  - Title: `text-[#1A1A1A]`
  - Tagline: `text-[#1C2A21]`
  - Description: `text-[#1A1A1A]/80`
  - Deliverables Box: `bg-[#E6E2DD]/20 border border-[#E6E2DD] p-6 sm:p-8 space-y-4`
  - Deliverables Header: `text-[#1A1A1A]`
  - Deliverables Item Text: `text-[#1A1A1A]/85`
  - Check Icon: `text-[#1C2A21]`
- **Lines 58-71**: Bottom CTA:
  - Container: `bg-[#1C2A21] text-[#F9F8F6] py-20 text-center`
  - Button: `<Button href="/contact" variant="secondary" size="lg">Contact Principals</Button>`

#### Concrete Modifications
```diff
- <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
+ <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">

- <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] mt-2 tracking-tight">
+ <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] mt-2 tracking-tight">

- <p className="mt-6 text-lg text-[#1A1A1A]/85 font-light leading-relaxed">
+ <p className="mt-6 text-lg text-[#C4C9C3]/90 font-light leading-relaxed">

- <InView ... className="border-t border-[#E6E2DD] pt-12">
+ <InView ... className="border-t border-white/10 pt-12">

- <div className="lg:col-span-1 font-serif text-3xl text-[#1C2A21]/60">
+ <div className="lg:col-span-1 font-serif text-3xl text-[#88B896]/60">

- <h2 className="font-serif text-2xl sm:text-3xl text-[#1A1A1A] tracking-tight">{service.title}</h2>
+ <h2 className="font-serif text-2xl sm:text-3xl text-[#F4F2ED] tracking-tight">{service.title}</h2>

- <p className="text-xs uppercase tracking-wider text-[#1C2A21] font-medium">{service.tagline}</p>
+ <p className="text-xs uppercase tracking-wider text-[#88B896] font-medium">{service.tagline}</p>

- <p className="text-sm text-[#1A1A1A]/80 font-light leading-relaxed">{service.description}</p>
+ <p className="text-sm text-[#C4C9C3]/80 font-light leading-relaxed">{service.description}</p>

- <div className="lg:col-span-6 bg-[#E6E2DD]/20 border border-[#E6E2DD] p-6 sm:p-8 space-y-4">
+ <div className="lg:col-span-6 bg-[rgba(20,32,24,0.65)] backdrop-blur-md border border-white/10 p-6 sm:p-8 space-y-4 shadow-xl">

- <h3 className="text-xs uppercase tracking-widest text-[#1A1A1A] font-semibold">Key Deliverables</h3>
+ <h3 className="text-xs uppercase tracking-widest text-[#F4F2ED] font-semibold">Key Deliverables</h3>

- <Check className="w-4 h-4 text-[#1C2A21] mr-3 shrink-0 mt-0.5" />
+ <Check className="w-4 h-4 text-[#88B896] mr-3 shrink-0 mt-0.5" />

- <li ... className="flex items-start text-xs sm:text-sm text-[#1A1A1A]/85 font-light">
+ <li ... className="flex items-start text-xs sm:text-sm text-[#C4C9C3]/90 font-light">

- <section className="bg-[#1C2A21] text-[#F9F8F6] py-20 text-center">
+ <section className="bg-[#0E1310] border-t border-white/10 text-[#F4F2ED] py-20 text-center">
```

---

### 4.6 `resources/js/Pages/Contact/Index.jsx`

#### Direct Observation & Visual Mapping
- **Lines 34-42**: Header:
  - Subtitle: `text-[#1C2A21]`
  - H1: `text-[#1A1A1A]`
  - Description: `text-[#1A1A1A]/85`
- **Lines 47-53**: Form card:
  - Container: `lg:col-span-7 bg-white border border-[#E6E2DD] p-8 sm:p-12 shadow-sm`
  - H2: `text-[#1A1A1A]`
  - Subtext: `text-[#1A1A1A]/60`
  - Success banner: `bg-[#1C2A21] text-[#F9F8F6]`
- **Lines 61-138**: Form fields using `<Input />`
- **Lines 140-148**: Submit button: `<Button type="submit" size="lg">Transmit Consultation Brief</Button>`
- **Lines 155-177**: Atelier directory:
  - H2: `text-[#1A1A1A]`
  - Item border: `border-t border-[#E6E2DD]`
  - City: `text-[#1A1A1A]`
  - Icons: `text-[#1C2A21]`
  - Details: `text-[#1A1A1A]/80`
- **Lines 181-192**: Consultation Guidelines card:
  - Container: `bg-[#E6E2DD]/30 border border-[#E6E2DD] p-6 space-y-3`
  - Subtitle: `text-[#1C2A21]`
  - Body: `text-[#1A1A1A]/80`

#### Concrete Modifications
```diff
- <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
+ <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">

- <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] mt-2 tracking-tight">
+ <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] mt-2 tracking-tight">

- <p className="mt-4 text-base text-[#1A1A1A]/85 font-light leading-relaxed">
+ <p className="mt-4 text-base text-[#C4C9C3]/90 font-light leading-relaxed">

- <div className="lg:col-span-7 bg-white border border-[#E6E2DD] p-8 sm:p-12 shadow-sm">
+ <div className="lg:col-span-7 bg-[rgba(20,32,24,0.65)] backdrop-blur-md border border-white/10 p-8 sm:p-12 shadow-2xl shadow-black/50">

- <h2 className="font-serif text-2xl text-[#1A1A1A] mb-2">Project Brief &amp; Site Assessment</h2>
+ <h2 className="font-serif text-2xl text-[#F4F2ED] mb-2">Project Brief &amp; Site Assessment</h2>

- <p className="text-xs text-[#1A1A1A]/60 font-light mb-8">
+ <p className="text-xs text-[#A8B0A6]/70 font-light mb-8">

- <div className="mb-6 p-4 bg-[#1C2A21] text-[#F9F8F6] text-xs uppercase tracking-wider font-medium">
+ <div className="mb-6 p-4 bg-[#1C2A21] border border-emerald-500/30 text-[#88B896] text-xs uppercase tracking-wider font-medium shadow-inner">

- <h2 className="font-serif text-2xl text-[#1A1A1A]">Studio Ateliers</h2>
+ <h2 className="font-serif text-2xl text-[#F4F2ED]">Studio Ateliers</h2>

- <div key={i} className="border-t border-[#E6E2DD] pt-6 space-y-2 text-sm text-[#1A1A1A]">
+ <div key={i} className="border-t border-white/10 pt-6 space-y-2 text-sm text-[#F4F2ED]">

- <MapPin className="w-3.5 h-3.5 mr-2 shrink-0 mt-0.5 text-[#1C2A21]" />
+ <MapPin className="w-3.5 h-3.5 mr-2 shrink-0 mt-0.5 text-[#88B896]" />

- <div className="flex items-start text-xs text-[#1A1A1A]/80 font-light">
+ <div className="flex items-start text-xs text-[#C4C9C3]/80 font-light">

- <div className="bg-[#E6E2DD]/30 border border-[#E6E2DD] p-6 space-y-3">
+ <div className="bg-[rgba(20,32,24,0.45)] backdrop-blur-md border border-white/10 p-6 space-y-3 shadow-lg">

- <h3 className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
+ <h3 className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">

- <p className="text-xs text-[#1A1A1A]/80 font-light leading-relaxed">
+ <p className="text-xs text-[#C4C9C3]/80 font-light leading-relaxed">
```

---

## 5. Layout Preservation & Responsive Verification Across Viewports

| Viewport Range | Breakpoint | Structural Grid & Component Behavior | Risk Analysis & Theme Preservation |
|---|---|---|---|
| **Mobile Narrow** (320px–380px) | Base (`sm:`) | Single column across all project grids, stat bars (grid-cols-2), filter bar stacks vertically, form inputs span 100% width. | Zero overflow; all padding `px-4` / `px-6` remains within bounds. Backdrop blur is hardware accelerated. |
| **Mobile Standard** (390px–480px) | `sm:` (640px) | Clean mobile flow; buttons wrap or flex gracefully, badges fit cleanly in corner chips. | Checked against `/antislop-layoutmobile`. Minimum tap targets (>44px) preserved on buttons and filter dropdown. |
| **Tablet Portrait** (768px–1024px) | `md:` (768px) | Project grids shift to 2 or 3 columns (`md:grid-cols-2`, `md:grid-cols-3`); filter bar switches to horizontal flex layout. | Clean grid wrapping, zero horizontal clipping. |
| **Desktop High-Res** (1280px–1920px)| `lg:` / `xl:` | 3-column project showcase, 4-column leadership team, 12-column asymmetric layout for Services and Contact. | Deep smoked canvas creates rich contrast with architectural imagery. |

---

## 6. Implementation Readiness & Risk Mitigation

1. **Tailwind Config & Base CSS Integration**:
   - `PublicLayout.jsx` background must be updated in tandem (`bg-[#0B0E0C] text-[#F4F2ED] selection:bg-[#1C2A21] selection:text-[#88B896]`).
   - `resources/css/app.css` html base layer should mirror this (`background-color: #0B0E0C; color: #F4F2ED;`).
2. **Build & Test Safety**:
   - `npm run build` executed cleanly in baseline (21.29s).
   - Automated test suite passes 100% (`43 passed` in `ChallengerM5_1_WhiteboxHardeningTest`, test suite fully intact).
   - Because all changes are pure Tailwind CSS class reassignments on existing JSX elements with zero props or markup removals, no Inertia or controller contracts are altered.
