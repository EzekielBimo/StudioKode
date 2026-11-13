# Handoff Report: Milestone 2 — Frontend Architecture, Design System, Multi-Page Portfolio & Motion Blueprint

**Author**: Explorer M2 (`teamwork_explorer_m2`)  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m2\`  
**Working Directory**: `c:\laragon\www\StudioKode`  
**Milestone**: Milestone 2 (Frontend Architecture, Design System, Multi-Page Portfolio & Motion)  
**Date**: 2026-11-09T02:15:00Z  

---

## 1. Observation

Direct observations from the workspace, file contents, environment configuration, and test executions:

1. **`package.json` Inspection**:
   - `c:\laragon\www\StudioKode\package.json`:
     - Lines 8–14:
       ```json
       "dependencies": {
           "@inertiajs/react": "^1.2.0",
           "framer-motion": "^11.0.0",
           "lucide-react": "^0.359.0",
           "react": "^18.2.0",
           "react-dom": "^18.2.0"
       }
       ```
     - Lines 15–23:
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
   - Observed: `clsx` and `tailwind-merge` are not yet declared in `dependencies`.
   - Tool check: `Test-Path node_modules` returned `False`. `node_modules` is not yet installed.
   - Node and npm versions: `node -v` outputs `v24.15.0`; `npm -v` outputs `12.2.2`.

2. **`vite.config.js` Inspection**:
   - `c:\laragon\www\StudioKode\vite.config.js`:
     - Lines 4–10:
       ```javascript
       export default defineConfig({
           plugins: [
               laravel({
                   input: ['resources/css/app.css', 'resources/js/app.js'],
                   refresh: true,
               }),
           ],
       });
       ```
   - Observed Discrepancies:
     - Line 7 specifies `resources/js/app.js`, while the root blade template `resources/views/app.blade.php` at line 17 specifies `@vite(['resources/css/app.css', 'resources/js/app.jsx'])`.
     - `@vitejs/plugin-react` is not imported or listed in the `plugins` array. Without `react()`, JSX transformation and Vite Fast Refresh fail during build.

3. **`tailwind.config.js` and `postcss.config.js` Absence**:
   - `c:\laragon\www\StudioKode\tailwind.config.js` does NOT exist.
   - `c:\laragon\www\StudioKode\postcss.config.js` does NOT exist.
   - `resources/css/app.css` contains `@tailwind base; @tailwind components; @tailwind utilities;` but lacks custom design token bindings and theme definitions.

4. **Existing Frontend Component Inventory (R3 Compliance)**:
   - All 14 files required by R3 were scaffolded during M1 to satisfy test assertions:
     - `resources/js/Components/UI/`: `Button.jsx`, `Badge.jsx`, `Modal.jsx`, `Input.jsx`, `InView.jsx`.
     - `resources/js/Components/Navigation/`: `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`.
     - `resources/js/Components/Portfolio/`: `ProjectCard.jsx`, `FilterBar.jsx`, `ImageGallery.jsx`.
     - `resources/js/Layouts/`: `PublicLayout.jsx`.
     - `resources/js/Pages/`: `Home/Index.jsx`, `Projects/Index.jsx`, `Projects/Show.jsx`, `Studio/Index.jsx`, `Services/Index.jsx`, `Contact/Index.jsx`.
   - Structural observations in existing components:
     - `InView.jsx`: Implements `useInView` and `motion` with `variants = { hidden: { opacity: 0, y: 24, filter: 'blur(4px)' }, visible: { opacity: 1, y: 0, filter: 'blur(0px)' } }` and `once: true`. Can be enhanced with polymorphic `as` and explicit `delay` propagation.
     - `FilterBar.jsx`: Correctly supports `newest`, `oldest`, and `area_desc` ("Largest Area (m²)") with zero mention of categories. Can be enhanced with reactive debounce and search clearing.
     - `Modal.jsx` and `ImageGallery.jsx`: Currently open a basic modal with image. Lightbox needs high-resolution controls: previous/next arrows, wrap-around navigation, keyboard arrow bindings (Left/Right), full image captions, and index counter (`01 / 04`).
     - Pages: Functionally wired to Inertia controller props, but need visual elevation to reflect the organic architectural luxury aesthetic.

5. **Automated Test Suite Status**:
   - Command: `$env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH; php artisan test`
   - Result: `Tests: 83 passed (711 assertions) Duration: 3.33s`.
   - All tests in `FrontendBuildTest.php`, `RouteTest.php`, `ProjectFilterTest.php`, `InquiryTest.php`, `SeederTest.php`, and `AreaSizeParserTest.php` pass with 100% success.

---

## 2. Logic Chain

1. **Build Tooling Rectification (Vite, PostCSS, Tailwind)**:
   - Observation 1 & 2 showed that `vite.config.js` points to `app.js` and omits `@vitejs/plugin-react`, while `app.blade.php` requests `app.jsx`. Furthermore, `tailwind.config.js` and `postcss.config.js` are missing.
   - Therefore, running `npm run build` in the current state would fail. To achieve a zero-error Vite build, the implementer must:
     1. Add `clsx` and `tailwind-merge` to `package.json` and run `npm install`.
     2. Create `postcss.config.js` with `tailwindcss` and `autoprefixer`.
     3. Create `tailwind.config.js` configuring content paths (`./resources/views/**/*.blade.php`, `./resources/js/**/*.jsx`) and extending the theme with the exact architectural colors (`#F9F8F6`, `#1C2A21`, `#E6E2DD`, `#1A1A1A`) and typography (`Cormorant Garamond` and `Plus Jakarta Sans`).
     4. Update `vite.config.js` to import `react` from `@vitejs/plugin-react`, add `react()` to plugins, and configure the input to `['resources/css/app.css', 'resources/js/app.jsx']`.

2. **Design System & Palette Mapping**:
   - Observation 3 showed that Tailwind tokens must represent the studio's architectural identity:
     - Off-white (`#F9F8F6`): Canvas background, crisp contrast.
     - Deep foliage green (`#1C2A21`): Primary brand color, dark hero sections, footer background, accent text.
     - Warm stone (`#E6E2DD`): Subtle borders, secondary backgrounds, card foundations, tactile badging.
     - Charcoal (`#1A1A1A`): Primary headings, body copy, high contrast readability.
   - Typography loaded in `app.blade.php` provides Cormorant Garamond for editorial serif headings and Plus Jakarta Sans for clean geometric body text.

3. **Motion Architecture (`InView.jsx`)**:
   - Specification R4 dictates Framer Motion reveal variants:
     `hidden: { opacity: 0, y: 24, filter: 'blur(4px)' }` -> `visible: { opacity: 1, y: 0, filter: 'blur(0px)' }` with `once: true`.
   - Providing `as` allows rendering semantic HTML (`article`, `section`, `div`) without breaking the DOM hierarchy.
   - Supporting `delay` allows cascading stagger animations across grids of cards and gallery images.

4. **FilterBar Search & Sorting Compliance**:
   - `tests/Feature/FrontendBuildTest.php` and `tests/Feature/ProjectFilterTest.php` verify:
     - Search matches project title, location, client, or excerpt.
     - Sort options include `newest`, `oldest`, and `area_desc` ("Largest Area Size in m²").
     - Strict rule: **Zero category mentions**.
   - `ProjectController@index` already processes `search` and `sort` using the cross-engine area parsing expression `(REPLACE(REPLACE(REPLACE(area_size, ' m²', ''), ',', ''), ' ', '') + 0) DESC`.
   - `FilterBar.jsx` should offer local responsive controls and notify parent or trigger Inertia navigation seamlessly.

5. **High-Resolution Gallery Modal Lightbox**:
   - Project show pages feature multi-image architectural archives.
   - `Modal.jsx` and `ImageGallery.jsx` must support:
     - Fullscreen darkened backdrop (`bg-[#1A1A1A]/95 backdrop-blur-md`).
     - Previous / Next navigation buttons and Left / Right keyboard arrow triggers.
     - Cyclic wrapping (first -> last, last -> first).
     - Clean uppercase counter (e.g. `02 / 04`) and descriptive caption.
     - Escape key listener and backdrop click dismissal.
     - Overflow hidden on `document.body` while modal is active.

---

## 3. Caveats

1. **Node Package Installation Network & Environment**:
   - Node v24 and npm 12 are available. `npm install` requires internet connectivity to pull packages from the npm registry.
2. **PHP CLI Environment in Subshells**:
   - System PATH default points to PHP 8.0.3. All Artisan and PHPUnit commands in subshells must prepend `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64` to `$env:PATH`.
3. **No Database Alterations**:
   - Schema and database migrations must remain untouched as Milestone 1 is 100% validated and passing all 83 assertions.

---

## 4. Conclusion

Milestone 2 has a clearly defined execution blueprint. The 14 React files already exist in their exact required paths and satisfy basic PHPUnit unit assertions. Full realization of Milestone 2 requires:
1. Updating `package.json`, installing npm packages, creating `postcss.config.js` and `tailwind.config.js`, and adjusting `vite.config.js`.
2. Enhancing all 14 React components with complete luxury architectural styling, high-resolution lightbox mechanics, debounced search/sort, and smooth scroll motion.
3. Compiling the production assets via `npm run build` and verifying that both `npm run build` and `php artisan test` execute with zero errors.

---

## 5. Verification Method

To verify the implementation of Milestone 2:

1. **Verify NPM Packages and Build Output**:
   ```powershell
   Set-Location -Path "c:\laragon\www\StudioKode"
   npm install
   npm run build
   ```
   *Expected Output*:
   - Clean Vite compilation exit code 0.
   - Bundle files generated in `public/build/assets/app-*.js` and `public/build/assets/app-*.css`.
   - Manifest generated at `public/build/manifest.json`.

2. **Verify Automated Feature & Build Test Suite**:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   php artisan test tests/Feature/FrontendBuildTest.php
   php artisan test
   ```
   *Expected Output*:
   - `FrontendBuildTest.php`: 9 passed tests.
   - Overall test suite: 100% passed tests (83+ tests).

3. **Verify Design Token & Lightbox Accessibility Invariants**:
   - Inspect `tailwind.config.js` for colors `#F9F8F6`, `#1C2A21`, `#E6E2DD`, `#1A1A1A`.
   - Inspect `InView.jsx` for `blur(4px)`, `blur(0px)`, `useInView`, and `once: true`.
   - Inspect `FilterBar.jsx` to ensure zero presence of the string `category`.
   - Inspect `Modal.jsx` to ensure presence of `Escape` and `onClose`.

---

# Comprehensive Implementation Blueprints

Below are the exact code blueprints for every required file in Milestone 2.

### 1. Build & Tooling Configuration

#### `package.json`
```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "vite build"
    },
    "dependencies": {
        "@inertiajs/react": "^1.2.0",
        "clsx": "^2.1.1",
        "framer-motion": "^11.0.0",
        "lucide-react": "^0.359.0",
        "react": "^18.2.0",
        "react-dom": "^18.2.0",
        "tailwind-merge": "^2.5.4"
    },
    "devDependencies": {
        "@vitejs/plugin-react": "^4.2.1",
        "autoprefixer": "^10.4.18",
        "axios": "^1.6.4",
        "laravel-vite-plugin": "^1.0",
        "postcss": "^8.4.35",
        "tailwindcss": "^3.4.1",
        "vite": "^5.0"
    }
}
```

#### `postcss.config.js`
```javascript
export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```

#### `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
```

#### `tailwind.config.js`
```javascript
/** @type {import('tailwindcss').Config} */
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

#### `resources/css/app.css`
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

#### `resources/js/lib/utils.js`
```javascript
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
```

---

### 2. UI Components (`resources/js/Components/UI/`)

#### `InView.jsx` (`resources/js/Components/UI/InView.jsx`)
```jsx
import React, { useRef } from 'react';
import { motion, useInView } from 'framer-motion';

const defaultVariants = {
    hidden: {
        opacity: 0,
        y: 24,
        filter: 'blur(4px)',
    },
    visible: {
        opacity: 1,
        y: 0,
        filter: 'blur(0px)',
    },
};

const defaultTransition = {
    duration: 0.7,
    ease: [0.21, 0.47, 0.32, 0.98],
};

const defaultViewOptions = {
    once: true,
    margin: '0px 0px -50px 0px',
};

export default function InView({
    children,
    className = '',
    variants = defaultVariants,
    transition = defaultTransition,
    viewOptions = defaultViewOptions,
    as = 'div',
    delay = 0,
}) {
    const ref = useRef(null);
    const isInView = useInView(ref, viewOptions);

    const MotionComponent = motion[as] || motion.div;
    const resolvedTransition = delay > 0 ? { ...transition, delay } : transition;

    return (
        <MotionComponent
            ref={ref}
            initial="hidden"
            animate={isInView ? 'visible' : 'hidden'}
            variants={variants}
            transition={resolvedTransition}
            className={className}
        >
            {children}
        </MotionComponent>
    );
}
```

#### `Button.jsx` (`resources/js/Components/UI/Button.jsx`)
```jsx
import React from 'react';
import { Link } from '@inertiajs/react';

export default function Button({
    children,
    variant = 'primary',
    size = 'md',
    href,
    className = '',
    disabled = false,
    type = 'button',
    ...props
}) {
    const base = 'inline-flex items-center justify-center font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#1C2A21] focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none select-none';

    const variants = {
        primary: 'bg-[#1C2A21] text-[#F9F8F6] hover:bg-[#283c2f] border border-transparent shadow-sm',
        secondary: 'bg-[#E6E2DD] text-[#1A1A1A] hover:bg-[#d8d3cd] border border-transparent',
        outline: 'border border-[#1C2A21] text-[#1C2A21] hover:bg-[#1C2A21] hover:text-[#F9F8F6]',
        'outline-light': 'border border-[#E6E2DD] text-[#E6E2DD] hover:bg-[#E6E2DD] hover:text-[#1C2A21]',
        ghost: 'text-[#1A1A1A] hover:text-[#1C2A21] hover:bg-[#E6E2DD]/40',
    };

    const sizes = {
        sm: 'px-3.5 py-1.5 text-xs tracking-widest uppercase',
        md: 'px-5 py-2.5 text-xs sm:text-sm tracking-widest uppercase',
        lg: 'px-7 py-3.5 text-sm tracking-widest uppercase',
    };

    const combinedClasses = `${base} ${variants[variant] || variants.primary} ${sizes[size] || sizes.md} ${className}`;

    if (href) {
        return (
            <Link href={href} className={combinedClasses} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <button
            type={type}
            disabled={disabled}
            className={combinedClasses}
            {...props}
        >
            {children}
        </button>
    );
}
```

#### `Badge.jsx` (`resources/js/Components/UI/Badge.jsx`)
```jsx
import React from 'react';

export default function Badge({ children, variant = 'neutral', className = '' }) {
    const variants = {
        neutral: 'bg-[#E6E2DD] text-[#1A1A1A]',
        foliage: 'bg-[#1C2A21] text-[#F9F8F6]',
        outline: 'border border-[#E6E2DD] text-[#1A1A1A]',
        'outline-light': 'border border-white/20 text-[#F9F8F6]',
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

#### `Modal.jsx` (`resources/js/Components/UI/Modal.jsx`)
```jsx
import React, { useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';

export default function Modal({
    isOpen,
    onClose,
    children,
    title,
    maxWidth = 'max-w-5xl',
}) {
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };

        if (isOpen) {
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', handleKeyDown);
        }

        return () => {
            document.body.style.overflow = 'unset';
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [isOpen, onClose]);

    return (
        <AnimatePresence>
            {isOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#1A1A1A]/95 backdrop-blur-md"
                    onClick={onClose}
                >
                    <motion.div
                        initial={{ opacity: 0, scale: 0.96 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.96 }}
                        transition={{ duration: 0.25 }}
                        className={`relative w-full ${maxWidth} bg-[#F9F8F6] text-[#1A1A1A] overflow-hidden shadow-2xl border border-[#E6E2DD]`}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header bar */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-[#E6E2DD]">
                            {title ? (
                                <h3 className="font-serif text-xl tracking-tight text-[#1A1A1A] truncate pr-4">
                                    {title}
                                </h3>
                            ) : (
                                <div />
                            )}
                            <button
                                onClick={onClose}
                                className="p-1.5 text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#E6E2DD]/60 rounded-full transition-colors"
                                aria-label="Close modal"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {/* Content */}
                        <div className="p-6">{children}</div>
                    </motion.div>
                </div>
            )}
        </AnimatePresence>
    );
}
```

#### `Input.jsx` (`resources/js/Components/UI/Input.jsx`)
```jsx
import React from 'react';

export default function Input({
    label,
    error,
    id,
    name,
    type = 'text',
    multiline = false,
    rows = 4,
    required = false,
    className = '',
    ...props
}) {
    const inputId = id || name;
    const baseClasses = `w-full px-4 py-2.5 bg-white border border-[#E6E2DD] text-[#1A1A1A] text-sm focus:outline-none focus:border-[#1C2A21] focus:ring-1 focus:ring-[#1C2A21] transition-colors placeholder:text-[#1A1A1A]/40 ${
        error ? 'border-red-500' : ''
    } ${className}`;

    return (
        <div className="w-full">
            {label && (
                <label
                    htmlFor={inputId}
                    className="block text-xs uppercase tracking-wider font-medium text-[#1A1A1A] mb-1.5"
                >
                    {label}
                    {required && <span className="text-red-600 ml-1">*</span>}
                </label>
            )}

            {multiline || type === 'textarea' ? (
                <textarea
                    id={inputId}
                    name={name}
                    rows={rows}
                    required={required}
                    className={baseClasses}
                    {...props}
                />
            ) : (
                <input
                    id={inputId}
                    name={name}
                    type={type}
                    required={required}
                    className={baseClasses}
                    {...props}
                />
            )}

            {error && <p className="mt-1.5 text-xs text-red-600 font-medium">{error}</p>}
        </div>
    );
}
```

---

### 3. Navigation Components (`resources/js/Components/Navigation/`)

#### `Navbar.jsx` (`resources/js/Components/Navigation/Navbar.jsx`)
```jsx
import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import MobileMenu from './MobileMenu';

export default function Navbar() {
    const [mobileOpen, setMobileOpen] = useState(false);
    const { url } = usePage();

    const navItems = [
        { name: 'Home', href: '/' },
        { name: 'Projects', href: '/projects' },
        { name: 'Studio', href: '/studio' },
        { name: 'Services', href: '/services' },
        { name: 'Contact', href: '/contact' },
    ];

    const isActive = (path) => {
        if (path === '/') return url === '/';
        return url.startsWith(path);
    };

    return (
        <header className="sticky top-0 z-40 bg-[#F9F8F6]/90 backdrop-blur-md border-b border-[#E6E2DD] transition-all">
            <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                {/* Brand Wordmark */}
                <Link href="/" className="group flex flex-col">
                    <span className="font-serif text-2xl tracking-tight text-[#1A1A1A] group-hover:text-[#1C2A21] transition-colors">
                        StudioKode
                    </span>
                    <span className="text-[9px] uppercase tracking-[0.25em] text-[#1A1A1A]/60 font-medium">
                        Botanical Architecture
                    </span>
                </Link>

                {/* Desktop Navigation */}
                <nav className="hidden md:flex items-center space-x-8 text-xs uppercase tracking-widest font-medium">
                    {navItems.map((item) => {
                        const active = isActive(item.href);
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`relative py-1 transition-colors hover:text-[#1C2A21] ${
                                    active ? 'text-[#1C2A21] font-semibold' : 'text-[#1A1A1A]/80'
                                }`}
                            >
                                {item.name}
                                {active && (
                                    <span className="absolute bottom-0 left-0 w-full h-[1.5px] bg-[#1C2A21]" />
                                )}
                            </Link>
                        );
                    })}
                </nav>

                {/* Desktop CTA */}
                <div className="hidden md:flex items-center">
                    <Link
                        href="/contact"
                        className="px-4 py-2 border border-[#1C2A21] text-xs uppercase tracking-widest text-[#1C2A21] hover:bg-[#1C2A21] hover:text-[#F9F8F6] transition-all font-medium"
                    >
                        Inquire
                    </Link>
                </div>

                {/* Mobile Menu Trigger */}
                <button
                    onClick={() => setMobileOpen(!mobileOpen)}
                    className="md:hidden p-2 text-[#1A1A1A] hover:text-[#1C2A21] transition-colors"
                    aria-label="Toggle navigation menu"
                >
                    {mobileOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                </button>
            </div>

            <MobileMenu
                isOpen={mobileOpen}
                onClose={() => setMobileOpen(false)}
                navItems={navItems}
                currentUrl={url}
            />
        </header>
    );
}
```

#### `MobileMenu.jsx` (`resources/js/Components/Navigation/MobileMenu.jsx`)
```jsx
import React from 'react';
import { Link } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';

export default function MobileMenu({ isOpen, onClose, navItems = [], currentUrl = '' }) {
    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                    transition={{ duration: 0.3, ease: [0.21, 0.47, 0.32, 0.98] }}
                    className="md:hidden border-b border-[#E6E2DD] bg-[#F9F8F6] px-6 py-8 overflow-hidden shadow-lg"
                >
                    <nav className="flex flex-col space-y-5 text-sm uppercase tracking-widest font-medium">
                        {navItems.map((item) => {
                            const active = item.href === '/' ? currentUrl === '/' : currentUrl.startsWith(item.href);
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    onClick={onClose}
                                    className={`py-1.5 transition-colors ${
                                        active ? 'text-[#1C2A21] font-bold border-l-2 border-[#1C2A21] pl-3' : 'text-[#1A1A1A]/80 hover:text-[#1C2A21]'
                                    }`}
                                >
                                    {item.name}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="mt-8 pt-6 border-t border-[#E6E2DD] flex flex-col space-y-3">
                        <span className="text-xs text-[#1A1A1A]/60 font-light">
                            Kyoto &middot; Z&uuml;rich &middot; Melbourne
                        </span>
                        <Link
                            href="/contact"
                            onClick={onClose}
                            className="inline-block text-center py-2.5 bg-[#1C2A21] text-[#F9F8F6] text-xs uppercase tracking-widest font-medium"
                        >
                            Initiate Consultation
                        </Link>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
```

#### `Footer.jsx` (`resources/js/Components/Navigation/Footer.jsx`)
```jsx
import React from 'react';
import { Link } from '@inertiajs/react';

export default function Footer() {
    return (
        <footer className="mt-auto bg-[#1C2A21] text-[#F9F8F6] py-16 border-t border-[#283c2f]">
            <div className="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
                {/* Brand Manifesto */}
                <div className="space-y-4">
                    <span className="font-serif text-2xl tracking-tight block">StudioKode</span>
                    <p className="text-sm text-[#E6E2DD]/80 leading-relaxed font-light">
                        Landscape architecture and botanical sanctuaries balancing living topography, tactile materiality, and ecological permanence.
                    </p>
                </div>

                {/* Exploration */}
                <div>
                    <h4 className="text-xs uppercase tracking-widest text-[#E6E2DD] font-semibold mb-4">
                        Exploration
                    </h4>
                    <ul className="space-y-2.5 text-sm text-[#E6E2DD]/80 font-light">
                        <li>
                            <Link href="/" className="hover:text-white transition-colors">Home</Link>
                        </li>
                        <li>
                            <Link href="/projects" className="hover:text-white transition-colors">Portfolio</Link>
                        </li>
                        <li>
                            <Link href="/studio" className="hover:text-white transition-colors">Studio Philosophy</Link>
                        </li>
                        <li>
                            <Link href="/services" className="hover:text-white transition-colors">Services &amp; Scope</Link>
                        </li>
                    </ul>
                </div>

                {/* Ateliers */}
                <div>
                    <h4 className="text-xs uppercase tracking-widest text-[#E6E2DD] font-semibold mb-4">
                        Ateliers
                    </h4>
                    <p className="text-sm text-[#E6E2DD]/80 font-light leading-relaxed space-y-1">
                        <span className="block">Kyoto: Kamigamo Sanctuary</span>
                        <span className="block">Z&uuml;rich: Seestrasse 142</span>
                        <span className="block">Melbourne: Flinders Lane</span>
                    </p>
                </div>

                {/* Consultations */}
                <div>
                    <h4 className="text-xs uppercase tracking-widest text-[#E6E2DD] font-semibold mb-4">
                        Inquiries
                    </h4>
                    <p className="text-sm text-[#E6E2DD]/80 font-light leading-relaxed mb-4">
                        Consultations by private appointment for architectural estates and residences.
                    </p>
                    <Link
                        href="/contact"
                        className="inline-block px-4 py-2 border border-[#E6E2DD] text-xs uppercase tracking-wider text-[#F9F8F6] hover:bg-[#F9F8F6] hover:text-[#1C2A21] transition-colors"
                    >
                        Initiate Consultation
                    </Link>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-6 mt-16 pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between text-xs text-[#E6E2DD]/60 font-light">
                <p>&copy; {new Date().getFullYear()} StudioKode Landscape Architecture. All rights reserved.</p>
                <p className="mt-2 md:mt-0">Botanical Architecture &amp; Spatial Ecology</p>
            </div>
        </footer>
    );
}
```

---

### 4. Portfolio Components (`resources/js/Components/Portfolio/`)

#### `ProjectCard.jsx` (`resources/js/Components/Portfolio/ProjectCard.jsx`)
```jsx
import React from 'react';
import { Link } from '@inertiajs/react';
import InView from '../UI/InView';
import Badge from '../UI/Badge';

export default function ProjectCard({ project, priority = false, delay = 0 }) {
    if (!project) return null;

    return (
        <InView delay={delay} className="group">
            <Link href={`/projects/${project.slug}`} className="block overflow-hidden">
                <div className="relative aspect-[16/10] overflow-hidden bg-[#E6E2DD]">
                    <img
                        src={project.cover_image}
                        alt={project.title}
                        className="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                        loading={priority ? 'eager' : 'lazy'}
                    />
                    {project.is_featured && (
                        <div className="absolute top-4 left-4">
                            <Badge variant="foliage">Featured Sanctuary</Badge>
                        </div>
                    )}
                </div>

                <div className="pt-4 pb-2">
                    <div className="flex items-center justify-between text-xs text-[#1A1A1A]/70 uppercase tracking-widest font-medium mb-1.5">
                        <span>{project.location}</span>
                        <span>{project.area_size} &middot; {project.year}</span>
                    </div>
                    <h3 className="font-serif text-2xl text-[#1A1A1A] group-hover:text-[#1C2A21] transition-colors">
                        {project.title}
                    </h3>
                    {project.excerpt && (
                        <p className="mt-1 text-sm text-[#1A1A1A]/80 line-clamp-2 font-light">
                            {project.excerpt}
                        </p>
                    )}
                </div>
            </Link>
        </InView>
    );
}
```

#### `FilterBar.jsx` (`resources/js/Components/Portfolio/FilterBar.jsx`)
*Note: Complies strictly with the zero-category constraint.*
```jsx
import React from 'react';
import { Search, X, SlidersHorizontal } from 'lucide-react';

export default function FilterBar({
    search = '',
    sort = 'newest',
    onSearchChange,
    onSortChange,
    totalCount = 0,
    filteredCount = 0,
}) {
    return (
        <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6 border-b border-[#E6E2DD]">
            {/* Search Input */}
            <div className="relative flex-1 max-w-md">
                <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#1A1A1A]/50" />
                <input
                    type="text"
                    value={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    placeholder="Search sanctuaries by name, location, or material..."
                    className="w-full pl-10 pr-9 py-2 bg-white border border-[#E6E2DD] text-sm text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 focus:outline-none focus:border-[#1C2A21] transition-colors"
                />
                {search && (
                    <button
                        onClick={() => onSearchChange('')}
                        className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-[#1A1A1A]/50 hover:text-[#1A1A1A] transition-colors"
                        aria-label="Clear search"
                    >
                        <X className="w-3.5 h-3.5" />
                    </button>
                )}
            </div>

            {/* Sort & Count Controls */}
            <div className="flex items-center justify-between md:justify-end space-x-4">
                {filteredCount > 0 && totalCount > 0 && (
                    <span className="text-xs uppercase tracking-widest text-[#1A1A1A]/60 hidden sm:inline">
                        Showing {filteredCount} of {totalCount} Works
                    </span>
                )}

                <div className="flex items-center space-x-2.5">
                    <SlidersHorizontal className="w-4 h-4 text-[#1C2A21]" />
                    <label htmlFor="sort-select" className="text-xs uppercase tracking-widest text-[#1A1A1A]/70 font-medium">
                        Sort By:
                    </label>
                    <select
                        id="sort-select"
                        value={sort}
                        onChange={(e) => onSortChange(e.target.value)}
                        className="px-3 py-2 bg-white border border-[#E6E2DD] text-xs uppercase tracking-wider text-[#1A1A1A] focus:outline-none focus:border-[#1C2A21] transition-colors cursor-pointer"
                    >
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="area_desc">Largest Area (m²)</option>
                    </select>
                </div>
            </div>
        </div>
    );
}
```

#### `ImageGallery.jsx` (`resources/js/Components/Portfolio/ImageGallery.jsx`)
```jsx
import React, { useState, useEffect } from 'react';
import InView from '../UI/InView';
import Modal from '../UI/Modal';
import { Maximize2, ChevronLeft, ChevronRight } from 'lucide-react';

export default function ImageGallery({ images = [] }) {
    const [selectedIndex, setSelectedIndex] = useState(null);

    useEffect(() => {
        const handleKeyDown = (e) => {
            if (selectedIndex === null) return;
            if (e.key === 'ArrowLeft') {
                setSelectedIndex((prev) => (prev - 1 + images.length) % images.length);
            } else if (e.key === 'ArrowRight') {
                setSelectedIndex((prev) => (prev + 1) % images.length);
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [selectedIndex, images.length]);

    if (!images || images.length === 0) return null;

    const currentImage = selectedIndex !== null ? images[selectedIndex] : null;

    return (
        <div>
            {/* Gallery Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {images.map((img, idx) => (
                    <InView key={img.id || idx} delay={idx * 0.08} className="group relative">
                        <div
                            onClick={() => setSelectedIndex(idx)}
                            className="aspect-[4/3] overflow-hidden bg-[#E6E2DD] cursor-pointer relative"
                        >
                            <img
                                src={img.image_path}
                                alt={img.caption || 'Project visual'}
                                className="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                loading="lazy"
                            />
                            <div className="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span className="p-3 bg-[#F9F8F6]/90 rounded-full text-[#1A1A1A] shadow-md transform scale-90 group-hover:scale-100 transition-transform">
                                    <Maximize2 className="w-5 h-5" />
                                </span>
                            </div>
                        </div>
                        {img.caption && (
                            <p className="mt-2 text-xs text-[#1A1A1A]/70 font-light italic">
                                {img.caption}
                            </p>
                        )}
                    </InView>
                ))}
            </div>

            {/* High-Resolution Lightbox Modal */}
            <Modal
                isOpen={selectedIndex !== null}
                onClose={() => setSelectedIndex(null)}
                title={currentImage?.caption || 'Sanctuary Architectural Archive'}
                maxWidth="max-w-6xl"
            >
                {currentImage && (
                    <div className="relative flex flex-col items-center">
                        {/* Lightbox Image Container */}
                        <div className="relative w-full aspect-[16/10] max-h-[70vh] bg-[#1A1A1A] flex items-center justify-center overflow-hidden">
                            <img
                                key={currentImage.image_path}
                                src={currentImage.image_path}
                                alt={currentImage.caption || 'Expanded image'}
                                className="w-full h-full object-contain"
                            />

                            {/* Left / Right Navigation Buttons */}
                            {images.length > 1 && (
                                <>
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedIndex((prev) => (prev - 1 + images.length) % images.length);
                                        }}
                                        className="absolute left-4 top-1/2 -translate-y-1/2 p-2.5 bg-black/60 hover:bg-black/85 text-white rounded-full transition-colors"
                                        aria-label="Previous Image"
                                    >
                                        <ChevronLeft className="w-6 h-6" />
                                    </button>
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedIndex((prev) => (prev + 1) % images.length);
                                        }}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 p-2.5 bg-black/60 hover:bg-black/85 text-white rounded-full transition-colors"
                                        aria-label="Next Image"
                                    >
                                        <ChevronRight className="w-6 h-6" />
                                    </button>
                                </>
                            )}
                        </div>

                        {/* Caption & Counter */}
                        <div className="w-full mt-4 flex items-center justify-between text-xs text-[#1A1A1A]/70 tracking-wider uppercase font-medium">
                            <span className="font-light italic text-[#1A1A1A]/90 normal-case text-sm">
                                {currentImage.caption || 'Archival Record'}
                            </span>
                            <span className="font-mono">
                                {String(selectedIndex + 1).padStart(2, '0')} / {String(images.length).padStart(2, '0')}
                            </span>
                        </div>
                    </div>
                )}
            </Modal>
        </div>
    );
}
```

---

### 5. Layout (`resources/js/Layouts/PublicLayout.jsx`)

```jsx
import React from 'react';
import { usePage } from '@inertiajs/react';
import Navbar from '../Components/Navigation/Navbar';
import Footer from '../Components/Navigation/Footer';

export default function PublicLayout({ children }) {
    const { flash } = usePage().props;

    return (
        <div className="min-h-screen flex flex-col bg-[#F9F8F6] text-[#1A1A1A] font-sans selection:bg-[#1C2A21] selection:text-[#F9F8F6]">
            <Navbar />

            {flash?.success && (
                <div className="bg-[#1C2A21] text-[#F9F8F6] px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider animate-in fade-in duration-300">
                    {flash.success}
                </div>
            )}

            {flash?.error && (
                <div className="bg-red-800 text-white px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider">
                    {flash.error}
                </div>
            )}

            <main className="flex-1">{children}</main>

            <Footer />
        </div>
    );
}
```

---

### 6. Pages (`resources/js/Pages/`)

#### `Pages/Home/Index.jsx` (`resources/js/Pages/Home/Index.jsx`)
```jsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import InView from '../../Components/UI/InView';
import Button from '../../Components/UI/Button';
import { ArrowRight } from 'lucide-react';

export default function HomeIndex({
    featuredProjects = [],
    statistics = [],
    philosophyHighlights = [],
}) {
    return (
        <PublicLayout>
            <Head title="Architectural Sanctuaries & Botanical Landscape Design" />

            {/* Hero Section */}
            <section className="relative min-h-[85vh] flex items-center justify-center bg-[#1C2A21] text-[#F9F8F6] px-6 overflow-hidden">
                <div className="absolute inset-0 opacity-40 mix-blend-overlay">
                    <img
                        src="https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=2000&q=80"
                        alt="Sanctuary landscape"
                        className="w-full h-full object-cover"
                    />
                </div>
                <div className="relative max-w-4xl mx-auto text-center space-y-8 py-20">
                    <InView>
                        <span className="text-xs uppercase tracking-[0.25em] text-[#E6E2DD]/80 font-medium">
                            StudioKode Landscape Architecture
                        </span>
                    </InView>
                    <InView delay={0.1}>
                        <h1 className="font-serif text-4xl sm:text-6xl md:text-7xl leading-[1.08] tracking-tight">
                            Mediating between architectural form and untamed nature.
                        </h1>
                    </InView>
                    <InView delay={0.2}>
                        <p className="text-base sm:text-lg text-[#E6E2DD]/90 font-light max-w-2xl mx-auto leading-relaxed">
                            Crafting living topographies, dry-stone masonry, and biodiverse botanical tapestries for generational private estates.
                        </p>
                    </InView>
                    <InView delay={0.3}>
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                            <Button href="/projects" variant="secondary" size="lg">
                                Explore Sanctuaries
                            </Button>
                            <Button href="/contact" variant="outline-light" size="lg">
                                Commission Project
                            </Button>
                        </div>
                    </InView>
                </div>
            </section>

            {/* Statistics Bar */}
            {statistics.length > 0 && (
                <section className="border-b border-[#E6E2DD] bg-[#E6E2DD]/30">
                    <div className="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        {statistics.map((stat, i) => (
                            <InView key={i} delay={i * 0.08} className="space-y-1">
                                <div className="font-serif text-3xl md:text-4xl text-[#1C2A21] font-normal">
                                    {stat.value}
                                </div>
                                <div className="text-xs uppercase tracking-widest text-[#1A1A1A]/70 font-medium">
                                    {stat.label}
                                </div>
                            </InView>
                        ))}
                    </div>
                </section>
            )}

            {/* Featured Projects Showcase */}
            <section className="max-w-7xl mx-auto px-6 py-24">
                <InView className="flex flex-col md:flex-row md:items-end justify-between mb-12">
                    <div>
                        <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                            Selected Works
                        </span>
                        <h2 className="font-serif text-3xl md:text-5xl tracking-tight text-[#1A1A1A] mt-2">
                            Curated Sanctuaries
                        </h2>
                    </div>
                    <Link
                        href="/projects"
                        className="mt-4 md:mt-0 inline-flex items-center text-xs uppercase tracking-widest font-semibold hover:text-[#1C2A21] transition-colors"
                    >
                        View Full Portfolio <ArrowRight className="w-4 h-4 ml-1.5" />
                    </Link>
                </InView>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {featuredProjects.map((project, i) => (
                        <ProjectCard
                            key={project.id || i}
                            project={project}
                            priority={i === 0}
                            delay={i * 0.1}
                        />
                    ))}
                </div>
            </section>

            {/* Philosophy Highlights */}
            {philosophyHighlights.length > 0 && (
                <section className="bg-[#1C2A21] text-[#F9F8F6] py-24">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="text-center max-w-2xl mx-auto mb-16 space-y-3">
                            <span className="text-xs uppercase tracking-widest text-[#E6E2DD]/70 font-semibold">
                                Our Approach
                            </span>
                            <h2 className="font-serif text-3xl md:text-4xl tracking-tight">
                                The Three Botanical Pillars
                            </h2>
                        </InView>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                            {philosophyHighlights.map((pillar, i) => (
                                <InView key={i} delay={i * 0.12} className="space-y-3 border-t border-[#E6E2DD]/20 pt-6">
                                    <span className="font-serif text-2xl text-[#E6E2DD]/50">
                                        {pillar.number}
                                    </span>
                                    <h3 className="font-serif text-xl tracking-tight">
                                        {pillar.title}
                                    </h3>
                                    <p className="text-sm text-[#E6E2DD]/80 font-light leading-relaxed">
                                        {pillar.description}
                                    </p>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Inquiry CTA */}
            <section className="max-w-4xl mx-auto px-6 py-24 text-center space-y-6">
                <InView>
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        Begin A Conversation
                    </span>
                    <h2 className="font-serif text-3xl md:text-5xl tracking-tight text-[#1A1A1A] mt-2">
                        Let us envision your living landscape.
                    </h2>
                    <p className="mt-4 text-base text-[#1A1A1A]/80 font-light leading-relaxed max-w-xl mx-auto">
                        Whether an alpine estate, urban microclimate courtyard, or coastal woodland sanctuary, our partners personally guide each inquiry.
                    </p>
                    <div className="pt-6">
                        <Button href="/contact" size="lg">
                            Initiate Commission
                        </Button>
                    </div>
                </InView>
            </section>
        </PublicLayout>
    );
}
```

#### `Pages/Projects/Index.jsx` (`resources/js/Pages/Projects/Index.jsx`)
```jsx
import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import FilterBar from '../../Components/Portfolio/FilterBar';
import InView from '../../Components/UI/InView';

export default function ProjectsIndex({
    projects = [],
    filters = { search: '', sort: 'newest' },
    totalCount = 0,
}) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [sortOption, setSortOption] = useState(filters.sort || 'newest');

    const handleSearchChange = (val) => {
        setSearchTerm(val);
        router.get(
            '/projects',
            { search: val, sort: sortOption },
            { preserveState: true, replace: true }
        );
    };

    const handleSortChange = (val) => {
        setSortOption(val);
        router.get(
            '/projects',
            { search: searchTerm, sort: val },
            { preserveState: true, replace: true }
        );
    };

    return (
        <PublicLayout>
            <Head title="Architectural Sanctuaries Portfolio" />

            <div className="max-w-7xl mx-auto px-6 py-16">
                <InView className="max-w-2xl mb-12">
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        Our Portfolio
                    </span>
                    <h1 className="font-serif text-4xl sm:text-5xl text-[#1A1A1A] mt-2 tracking-tight">
                        Botanical Sanctuaries &amp; Living Architecture
                    </h1>
                    <p className="mt-4 text-base text-[#1A1A1A]/80 font-light leading-relaxed">
                        Each garden is conceived as a long-term ecological intervention, responding to microclimates, geological substrates, and contemplative spatial flow.
                    </p>
                </InView>

                <FilterBar
                    search={searchTerm}
                    sort={sortOption}
                    onSearchChange={handleSearchChange}
                    onSortChange={handleSortChange}
                    totalCount={totalCount}
                    filteredCount={projects.length}
                />

                <div className="mt-12">
                    {projects.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {projects.map((project, idx) => (
                                <ProjectCard
                                    key={project.id}
                                    project={project}
                                    delay={idx * 0.08}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-24 border border-dashed border-[#E6E2DD] bg-[#E6E2DD]/20">
                            <p className="font-serif text-xl text-[#1A1A1A]">
                                No sanctuaries found matching your criteria.
                            </p>
                            <button
                                onClick={() => {
                                    setSearchTerm('');
                                    setSortOption('newest');
                                    router.get('/projects', { search: '', sort: 'newest' });
                                }}
                                className="mt-4 text-xs uppercase tracking-widest font-semibold text-[#1C2A21] underline hover:no-underline"
                            >
                                Reset Search &amp; Filters
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
```

#### `Pages/Projects/Show.jsx` (`resources/js/Pages/Projects/Show.jsx`)
```jsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ImageGallery from '../../Components/Portfolio/ImageGallery';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import InView from '../../Components/UI/InView';
import Badge from '../../Components/UI/Badge';
import { ArrowLeft, MapPin, Calendar, Maximize2, UserCheck } from 'lucide-react';

export default function ProjectsShow({ project, images = [], relatedProjects = [] }) {
    if (!project) return null;

    return (
        <PublicLayout>
            <Head title={`${project.title} — Garden Architecture Sanctuary`} />

            {/* Back Navigation & Breadcrumb */}
            <div className="max-w-7xl mx-auto px-6 pt-12 pb-6">
                <Link
                    href="/projects"
                    className="inline-flex items-center text-xs uppercase tracking-widest text-[#1A1A1A]/70 hover:text-[#1C2A21] font-medium transition-colors"
                >
                    <ArrowLeft className="w-4 h-4 mr-2" /> Back to Portfolio
                </Link>
            </div>

            {/* Title & Metadata Section */}
            <div className="max-w-7xl mx-auto px-6 pb-12">
                <InView>
                    <div className="flex flex-wrap items-center gap-2 mb-3">
                        {project.is_featured && <Badge variant="foliage">Featured Sanctuary</Badge>}
                        <Badge variant="outline">{project.area_size}</Badge>
                    </div>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] tracking-tight">
                        {project.title}
                    </h1>
                </InView>

                {/* Metadata Grid */}
                <InView
                    delay={0.1}
                    className="grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6 border-y border-[#E6E2DD] text-xs uppercase tracking-widest font-medium text-[#1A1A1A]"
                >
                    <div>
                        <span className="text-[#1A1A1A]/50 block mb-1">Location</span>
                        <div className="flex items-center">
                            <MapPin className="w-3.5 h-3.5 mr-1.5 text-[#1C2A21]" />
                            {project.location}
                        </div>
                    </div>
                    <div>
                        <span className="text-[#1A1A1A]/50 block mb-1">Year Completed</span>
                        <div className="flex items-center">
                            <Calendar className="w-3.5 h-3.5 mr-1.5 text-[#1C2A21]" />
                            {project.year}
                        </div>
                    </div>
                    <div>
                        <span className="text-[#1A1A1A]/50 block mb-1">Grounds Scale</span>
                        <div className="flex items-center">
                            <Maximize2 className="w-3.5 h-3.5 mr-1.5 text-[#1C2A21]" />
                            {project.area_size}
                        </div>
                    </div>
                    <div>
                        <span className="text-[#1A1A1A]/50 block mb-1">Client / Trust</span>
                        <div className="flex items-center">
                            <UserCheck className="w-3.5 h-3.5 mr-1.5 text-[#1C2A21]" />
                            {project.client || 'Private Commission'}
                        </div>
                    </div>
                </InView>
            </div>

            {/* Hero Cover Image */}
            <section className="max-w-7xl mx-auto px-6 pb-16">
                <InView className="aspect-[21/9] overflow-hidden bg-[#E6E2DD]">
                    <img
                        src={project.cover_image}
                        alt={project.title}
                        className="w-full h-full object-cover"
                    />
                </InView>
            </section>

            {/* Narrative & Masterplan */}
            <section className="max-w-7xl mx-auto px-6 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-16">
                    <div className="lg:col-span-2 space-y-6">
                        <InView>
                            <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                                Architectural Narrative
                            </span>
                            <h2 className="font-serif text-3xl text-[#1A1A1A] mt-2 mb-6">
                                Ecological Intent &amp; Spatial Choreography
                            </h2>
                            <div className="prose prose-stone max-w-none text-base text-[#1A1A1A]/85 font-light leading-relaxed space-y-4 whitespace-pre-line">
                                {project.description}
                            </div>
                        </InView>
                    </div>

                    {/* Masterplan Sidebar */}
                    <div className="space-y-6">
                        {project.masterplan_image && (
                            <InView delay={0.15} className="border border-[#E6E2DD] bg-white p-6 space-y-3">
                                <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                                    Territorial Drawing
                                </span>
                                <h3 className="font-serif text-xl text-[#1A1A1A]">
                                    Masterplan Schematic
                                </h3>
                                <div className="aspect-[4/3] overflow-hidden bg-[#E6E2DD]">
                                    <img
                                        src={project.masterplan_image}
                                        alt={`${project.title} masterplan schematic`}
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                                <p className="text-xs text-[#1A1A1A]/60 font-light">
                                    Hydrological contours, dry-creek drainage runnels, and specimen canopy placement.
                                </p>
                            </InView>
                        )}
                    </div>
                </div>
            </section>

            {/* Visual Archive / ImageGallery */}
            {images.length > 0 && (
                <section className="max-w-7xl mx-auto px-6 py-16 border-t border-[#E6E2DD]">
                    <InView className="mb-10">
                        <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                            Visual Documentation
                        </span>
                        <h2 className="font-serif text-3xl text-[#1A1A1A] mt-2">
                            Atmospheric &amp; Material Archives
                        </h2>
                    </InView>
                    <ImageGallery images={images} />
                </section>
            )}

            {/* Related Sanctuaries */}
            {relatedProjects.length > 0 && (
                <section className="max-w-7xl mx-auto px-6 py-24 border-t border-[#E6E2DD]">
                    <InView className="mb-10">
                        <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                            Further Sanctuaries
                        </span>
                        <h2 className="font-serif text-3xl text-[#1A1A1A] mt-2">
                            Related Works
                        </h2>
                    </InView>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {relatedProjects.map((p, idx) => (
                            <ProjectCard key={p.id} project={p} delay={idx * 0.1} />
                        ))}
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
```

#### `Pages/Studio/Index.jsx` (`resources/js/Pages/Studio/Index.jsx`)
```jsx
import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';

export default function StudioIndex({
    studio = {},
    philosophy = [],
    team = [],
    process = [],
    accolades = [],
}) {
    return (
        <PublicLayout>
            <Head title="Studio Philosophy, Methodology & Leadership" />

            {/* Header */}
            <div className="max-w-7xl mx-auto px-6 py-20">
                <InView className="max-w-3xl">
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        The Practice
                    </span>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] mt-2 tracking-tight">
                        Architecture Born From Ground &amp; Topography
                    </h1>
                    <p className="mt-6 text-lg text-[#1A1A1A]/85 font-light leading-relaxed">
                        {studio.manifesto}
                    </p>
                </InView>
            </div>

            {/* Philosophy Pillars */}
            <section className="bg-[#1C2A21] text-[#F9F8F6] py-24">
                <div className="max-w-7xl mx-auto px-6">
                    <InView className="max-w-2xl mb-16">
                        <span className="text-xs uppercase tracking-widest text-[#E6E2DD]/70 font-semibold">
                            Core Tenets
                        </span>
                        <h2 className="font-serif text-3xl sm:text-4xl tracking-tight mt-1">
                            Foundational Principles
                        </h2>
                    </InView>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
                        {philosophy.map((item, i) => (
                            <InView key={i} delay={i * 0.1} className="border-t border-[#E6E2DD]/20 pt-6 space-y-3">
                                <span className="font-serif text-2xl text-[#E6E2DD]/40">{item.number}</span>
                                <h3 className="font-serif text-2xl tracking-tight">{item.title}</h3>
                                <p className="text-sm text-[#E6E2DD]/80 font-light leading-relaxed">{item.description}</p>
                            </InView>
                        ))}
                    </div>
                </div>
            </section>

            {/* Multi-Stage Methodology */}
            <section className="max-w-7xl mx-auto px-6 py-24">
                <InView className="max-w-2xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        Design Evolution
                    </span>
                    <h2 className="font-serif text-3xl sm:text-4xl tracking-tight text-[#1A1A1A] mt-1">
                        Our Four-Stage Methodology
                    </h2>
                </InView>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                    {process.map((step, i) => (
                        <InView key={i} delay={i * 0.08} className="border border-[#E6E2DD] bg-white p-6 space-y-4">
                            <span className="font-serif text-3xl text-[#1C2A21]">{step.stage}</span>
                            <h3 className="font-serif text-xl text-[#1A1A1A]">{step.title}</h3>
                            <p className="text-xs text-[#1A1A1A]/80 font-light leading-relaxed">{step.description}</p>
                        </InView>
                    ))}
                </div>
            </section>

            {/* Leadership Team */}
            <section className="max-w-7xl mx-auto px-6 py-24 border-t border-[#E6E2DD]">
                <InView className="max-w-2xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        Studio Leadership
                    </span>
                    <h2 className="font-serif text-3xl sm:text-4xl tracking-tight text-[#1A1A1A] mt-1">
                        Principal Practitioners
                    </h2>
                </InView>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    {team.map((member, i) => (
                        <InView key={i} delay={i * 0.08} className="space-y-4">
                            <div className="aspect-[4/5] overflow-hidden bg-[#E6E2DD]">
                                <img
                                    src={member.image}
                                    alt={member.name}
                                    className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
                                />
                            </div>
                            <div>
                                <h3 className="font-serif text-lg text-[#1A1A1A]">{member.name}</h3>
                                <p className="text-xs uppercase tracking-wider text-[#1C2A21] font-medium mt-0.5">{member.role}</p>
                                <p className="text-xs text-[#1A1A1A]/70 font-light mt-2 leading-relaxed">{member.bio}</p>
                            </div>
                        </InView>
                    ))}
                </div>
            </section>

            {/* Accolades */}
            {accolades.length > 0 && (
                <section className="bg-[#E6E2DD]/30 border-t border-[#E6E2DD] py-20">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="max-w-xl mb-12">
                            <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                                Recognition
                            </span>
                            <h2 className="font-serif text-2xl text-[#1A1A1A] mt-1">
                                Peer &amp; Industry Accolades
                            </h2>
                        </InView>

                        <div className="space-y-4">
                            {accolades.map((acc, i) => (
                                <InView key={i} className="flex items-center justify-between border-b border-[#E6E2DD] pb-4 text-sm">
                                    <span className="font-serif text-lg text-[#1A1A1A]">{acc.award}</span>
                                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">{acc.year}</span>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
```

#### `Pages/Services/Index.jsx` (`resources/js/Pages/Services/Index.jsx`)
```jsx
import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';
import Button from '../../Components/UI/Button';
import { Check } from 'lucide-react';

export default function ServicesIndex({ services = [] }) {
    return (
        <PublicLayout>
            <Head title="Architectural Landscape & Botanical Services" />

            {/* Header */}
            <div className="max-w-7xl mx-auto px-6 py-20">
                <InView className="max-w-3xl">
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        Our Scope
                    </span>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] mt-2 tracking-tight">
                        Disciplinary Rigor from Masterplan to Stewardship
                    </h1>
                    <p className="mt-6 text-lg text-[#1A1A1A]/85 font-light leading-relaxed">
                        We provide complete landscape architectural direction for visionary landowners, architects, and institutions seeking profound ecological and tactile permanence.
                    </p>
                </InView>
            </div>

            {/* Services Detailed List */}
            <section className="max-w-7xl mx-auto px-6 pb-24 space-y-16">
                {services.map((service, index) => (
                    <InView key={service.id || index} delay={index * 0.08} className="border-t border-[#E6E2DD] pt-12">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div className="lg:col-span-1 font-serif text-3xl text-[#1C2A21]/60">
                                {service.number}
                            </div>
                            <div className="lg:col-span-5 space-y-4">
                                <h2 className="font-serif text-2xl sm:text-3xl text-[#1A1A1A] tracking-tight">{service.title}</h2>
                                <p className="text-xs uppercase tracking-wider text-[#1C2A21] font-medium">{service.tagline}</p>
                                <p className="text-sm text-[#1A1A1A]/80 font-light leading-relaxed">{service.description}</p>
                            </div>
                            <div className="lg:col-span-6 bg-[#E6E2DD]/20 border border-[#E6E2DD] p-6 sm:p-8 space-y-4">
                                <h3 className="text-xs uppercase tracking-widest text-[#1A1A1A] font-semibold">Key Deliverables</h3>
                                <ul className="space-y-3">
                                    {service.deliverables?.map((item, dIdx) => (
                                        <li key={dIdx} className="flex items-start text-xs sm:text-sm text-[#1A1A1A]/85 font-light">
                                            <Check className="w-4 h-4 text-[#1C2A21] mr-3 shrink-0 mt-0.5" />
                                            <span>{item}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </InView>
                ))}
            </section>

            {/* Inquiries CTA */}
            <section className="bg-[#1C2A21] text-[#F9F8F6] py-20 text-center">
                <InView className="max-w-xl mx-auto px-6 space-y-6">
                    <h2 className="font-serif text-3xl tracking-tight">Commission an Architectural Study</h2>
                    <p className="text-sm text-[#E6E2DD]/80 font-light leading-relaxed">
                        We welcome conversations at early territorial acquisition and conceptual architectural stages.
                    </p>
                    <div>
                        <Button href="/contact" variant="secondary" size="lg">
                            Contact Principals
                        </Button>
                    </div>
                </InView>
            </section>
        </PublicLayout>
    );
}
```

#### `Pages/Contact/Index.jsx` (`resources/js/Pages/Contact/Index.jsx`)
```jsx
import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';
import Input from '../../Components/UI/Input';
import Button from '../../Components/UI/Button';
import { MapPin, Mail, Phone, Clock, Send } from 'lucide-react';

export default function ContactIndex({ studios = [], consultationDetails = {} }) {
    const { data, setData, post, processing, errors, recentlySuccessful, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        site_location: '',
        estimated_area: '',
        budget_range: '',
        message: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/inquiries', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <PublicLayout>
            <Head title="Initiate a Consultation — Atelier Locations & Inquiries" />

            <div className="max-w-7xl mx-auto px-6 py-20">
                <InView className="max-w-3xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                        Initiate Commission
                    </span>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#1A1A1A] mt-2 tracking-tight">
                        Consultation &amp; Atelier Inquiries
                    </h1>
                    <p className="mt-4 text-base text-[#1A1A1A]/85 font-light leading-relaxed">
                        We undertake a limited number of residential sanctuaries and botanical masterplans each season to ensure uncompromising artisanal oversight.
                    </p>
                </InView>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-16">
                    {/* Inquiry Form */}
                    <div className="lg:col-span-7 bg-white border border-[#E6E2DD] p-8 sm:p-12 shadow-sm">
                        <InView>
                            <h2 className="font-serif text-2xl text-[#1A1A1A] mb-2">Project Brief &amp; Site Assessment</h2>
                            <p className="text-xs text-[#1A1A1A]/60 font-light mb-8">
                                Please provide essential contextual details regarding your property, scale, and architectural aspirations.
                            </p>

                            {recentlySuccessful && (
                                <div className="mb-6 p-4 bg-[#1C2A21] text-[#F9F8F6] text-xs uppercase tracking-wider font-medium">
                                    Thank you. Your inquiry has been received. Our principal architects will review your project details.
                                </div>
                            )}

                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <Input
                                        label="Principal Name"
                                        name="name"
                                        type="text"
                                        required
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        error={errors.name}
                                        placeholder="e.g. Julian Finch"
                                    />
                                    <Input
                                        label="Email Address"
                                        name="email"
                                        type="email"
                                        required
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        error={errors.email}
                                        placeholder="e.g. julian@estate.com"
                                    />
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <Input
                                        label="Contact Telephone"
                                        name="phone"
                                        type="tel"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        error={errors.phone}
                                        placeholder="+41 22 819 4022"
                                    />
                                    <Input
                                        label="Site Location / Region"
                                        name="site_location"
                                        type="text"
                                        value={data.site_location}
                                        onChange={(e) => setData('site_location', e.target.value)}
                                        error={errors.site_location}
                                        placeholder="Kyoto, Lake Como, Zurich..."
                                    />
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <Input
                                        label="Estimated Grounds Area"
                                        name="estimated_area"
                                        type="text"
                                        value={data.estimated_area}
                                        onChange={(e) => setData('estimated_area', e.target.value)}
                                        error={errors.estimated_area}
                                        placeholder="e.g. 1,250 m²"
                                    />
                                    <Input
                                        label="Anticipated Budget Framework"
                                        name="budget_range"
                                        type="text"
                                        value={data.budget_range}
                                        onChange={(e) => setData('budget_range', e.target.value)}
                                        error={errors.budget_range}
                                        placeholder="e.g. $150,000 – $300,000"
                                    />
                                </div>

                                <div>
                                    <Input
                                        label="Architectural Intent & Vision"
                                        name="message"
                                        multiline
                                        rows={5}
                                        required
                                        value={data.message}
                                        onChange={(e) => setData('message', e.target.value)}
                                        error={errors.message}
                                        placeholder="Describe your site conditions, existing vegetation, architectural styles, water ambitions, and project timeframe..."
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    size="lg"
                                    disabled={processing}
                                    className="w-full sm:w-auto"
                                >
                                    <Send className="w-4 h-4 mr-2" />
                                    {processing ? 'Submitting Brief...' : 'Transmit Consultation Brief'}
                                </Button>
                            </form>
                        </InView>
                    </div>

                    {/* Atelier Directory & Logistics */}
                    <div className="lg:col-span-5 space-y-12">
                        <InView className="space-y-8">
                            <h2 className="font-serif text-2xl text-[#1A1A1A]">Studio Ateliers</h2>
                            {studios.map((atelier, i) => (
                                <div key={i} className="border-t border-[#E6E2DD] pt-6 space-y-2 text-sm text-[#1A1A1A]">
                                    <h3 className="font-serif text-xl font-medium">{atelier.city}</h3>
                                    <div className="flex items-start text-xs text-[#1A1A1A]/80 font-light">
                                        <MapPin className="w-3.5 h-3.5 mr-2 shrink-0 mt-0.5 text-[#1C2A21]" />
                                        <span>{atelier.address}</span>
                                    </div>
                                    <div className="flex items-center text-xs text-[#1A1A1A]/80 font-light">
                                        <Mail className="w-3.5 h-3.5 mr-2 shrink-0 text-[#1C2A21]" />
                                        <a href={`mailto:${atelier.email}`} className="hover:underline">{atelier.email}</a>
                                    </div>
                                    <div className="flex items-center text-xs text-[#1A1A1A]/80 font-light">
                                        <Phone className="w-3.5 h-3.5 mr-2 shrink-0 text-[#1C2A21]" />
                                        <span>{atelier.phone}</span>
                                    </div>
                                    <div className="flex items-center text-xs text-[#1A1A1A]/60 font-light">
                                        <Clock className="w-3.5 h-3.5 mr-2 shrink-0 text-[#1C2A21]" />
                                        <span>{atelier.hours}</span>
                                    </div>
                                </div>
                            ))}
                        </InView>

                        {/* Consultation Guidelines */}
                        <InView delay={0.1} className="bg-[#E6E2DD]/30 border border-[#E6E2DD] p-6 space-y-3">
                            <h3 className="text-xs uppercase tracking-widest text-[#1C2A21] font-semibold">
                                Commission Timeline
                            </h3>
                            <p className="text-xs text-[#1A1A1A]/80 font-light leading-relaxed">
                                {consultationDetails.leadTime}
                            </p>
                            <p className="text-xs text-[#1A1A1A]/80 font-light leading-relaxed">
                                {consultationDetails.minimumScale}
                            </p>
                        </InView>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
```
