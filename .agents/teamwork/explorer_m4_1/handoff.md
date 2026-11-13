# Handoff Report — Explorer M4.1: Vite Code-Splitting & Rollup Configuration (Feature 23)

## 1. Observation

### Current File Implementations

#### File 1: `vite.config.js`
`c:\laragon\www\StudioKode\vite.config.js` (Lines 1–17):
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        cors: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
```
Currently, `vite.config.js` does NOT specify any `build` property, Rollup `manualChunks`, chunk size limit, or sourcemap configurations.

#### File 2: `package.json`
`c:\laragon\www\StudioKode\package.json` (Lines 8–25):
```json
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
```

#### File 3: `resources/js/app.jsx`
`c:\laragon\www\StudioKode\resources\js\app.jsx` (Line 30):
```javascript
resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
```
Inertia dynamic page resolution is already configured via `import.meta.glob('./Pages/**/*.jsx')` which generates dynamic async import factories `() => import(...)`.

### Baseline Vite Production Build (Without Code Splitting)
Command: `npm run build`
Output:
```
vite v5.4.21 building for production...
transforming...
✓ 2013 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json                              12.51 kB │ gzip:  1.45 kB
public/build/assets/app-zKhGeuEX.css                    49.88 kB │ gzip:  8.76 kB
public/build/assets/check-COEfnhCP.js                    0.30 kB │ gzip:  0.25 kB
public/build/assets/arrow-left-DQywn4Ya.js               0.34 kB │ gzip:  0.27 kB
public/build/assets/search-DY7adjLx.js                   0.35 kB │ gzip:  0.28 kB
public/build/assets/clock-DraF_quJ.js                    0.36 kB │ gzip:  0.28 kB
public/build/assets/eye-DGOeCqUy.js                      0.37 kB │ gzip:  0.29 kB
public/build/assets/map-pin-CogUyZWe.js                  0.38 kB │ gzip:  0.30 kB
public/build/assets/mail-B87wfbTU.js                     0.40 kB │ gzip:  0.31 kB
public/build/assets/cloud-upload-Cwc835-A.js             0.43 kB │ gzip:  0.32 kB
public/build/assets/calendar-Dhd3RjGN.js                 0.44 kB │ gzip:  0.31 kB
public/build/assets/inbox-qcjkwouJ.js                    0.47 kB │ gzip:  0.34 kB
public/build/assets/maximize-2-txbl8SjK.js               0.48 kB │ gzip:  0.32 kB
public/build/assets/images-Bi8nY25T.js                   0.50 kB │ gzip:  0.36 kB
public/build/assets/trash-2-Csznd8sx.js                  0.54 kB │ gzip:  0.35 kB
public/build/assets/phone-Di-rSNxZ.js                    0.57 kB │ gzip:  0.36 kB
public/build/assets/sparkles-C5r8bWLn.js                 0.60 kB │ gzip:  0.36 kB
public/build/assets/x-DR1Jwk6c.js                        0.70 kB │ gzip:  0.34 kB
public/build/assets/Button-CoUdn-Wb.js                   1.17 kB │ gzip:  0.57 kB
public/build/assets/createLucideIcon-CtZwq_48.js         1.21 kB │ gzip:  0.57 kB
public/build/assets/ProjectCard-DPVMiSvj.js              1.61 kB │ gzip:  0.80 kB
public/build/assets/Gallery-AzjCkTGO.js                  1.74 kB │ gzip:  0.76 kB
public/build/assets/Index-BMdG1Kqf.js                    2.86 kB │ gzip:  1.13 kB
...
public/build/assets/proxy-C_iAKQ3r.js                  111.82 kB │ gzip: 36.81 kB
public/build/assets/app-IzPnCP4C.js                    290.99 kB │ gzip: 97.72 kB
✓ built in 4.67s (41 total assets)
```
Baseline Issues:
1. `app-*.js` entry point is massive: **290.99 kB** (97.72 kB gzipped), containing React, ReactDOM, Inertia, and shared code.
2. 16+ separate micro-chunks for Lucide icons (`0.30 kB` to `0.70 kB` each), creating excessive HTTP round-trips and manifest bloating.
3. Framer Motion internals are split into an uncommunicative `proxy-*.js` chunk (111.82 kB).

### Empirical Investigation: Object Syntax vs Function Syntax `manualChunks`

#### Test 1: Object Syntax `manualChunks`
Config:
```javascript
manualChunks: {
    'vendor-react': ['react', 'react-dom'],
    'vendor-inertia': ['@inertiajs/react'],
    'vendor-motion': ['framer-motion'],
    'vendor-lucide': ['lucide-react'],
}
```
Result:
- `manifest.json` analysis revealed an inverted circular dependency:
  `vendor-react` generated:
  `import { r as _a } from "./vendor-inertia-CRX1uTbM.js";`
  Because `@inertiajs/react` imported `react` first, Rollup bundled `react.production.min.js` into `vendor-inertia`, and `vendor-react` only received `scheduler` and `react-dom` while depending on `vendor-inertia`!

#### Test 2: Function Syntax `manualChunks` with Path Normalization
Config:
```javascript
manualChunks(id) {
    if (id.includes('node_modules')) {
        const normalizedId = id.replace(/\\/g, '/');
        if (
            normalizedId.includes('/node_modules/react/') ||
            normalizedId.includes('/node_modules/react-dom/') ||
            normalizedId.includes('/node_modules/scheduler/')
        ) {
            return 'vendor-react';
        }
        if (normalizedId.includes('/node_modules/@inertiajs/')) {
            return 'vendor-inertia';
        }
        if (
            normalizedId.includes('/node_modules/framer-motion/') ||
            normalizedId.includes('/node_modules/motion-dom/') ||
            normalizedId.includes('/node_modules/motion-utils/')
        ) {
            return 'vendor-motion';
        }
        if (normalizedId.includes('/node_modules/lucide-react/')) {
            return 'vendor-lucide';
        }
    }
}
```
Result:
```
public/build/manifest.json                               8.96 kB │ gzip:  0.96 kB
public/build/assets/app-zKhGeuEX.css                    49.88 kB │ gzip:  8.76 kB
public/build/assets/Button-Dcp0UyG5.js                   1.22 kB │ gzip:  0.60 kB
public/build/assets/Gallery-DQJbVqUr.js                  1.62 kB │ gzip:  0.69 kB
public/build/assets/ProjectCard-BL_jbUdH.js              1.66 kB │ gzip:  0.83 kB
public/build/assets/Index-CfyNLgsy.js                    2.86 kB │ gzip:  1.12 kB
public/build/assets/Index-B9IP9wl_.js                    4.00 kB │ gzip:  1.57 kB
public/build/assets/app-Ga0Cv5mS.js                      4.19 kB │ gzip:  1.69 kB
...
public/build/assets/vendor-lucide-DHpDFDmf.js           18.33 kB │ gzip:  3.77 kB
public/build/assets/vendor-motion-C96ZWg6v.js          117.89 kB │ gzip: 39.21 kB
public/build/assets/vendor-react-AOuonjQT.js           142.38 kB │ gzip: 45.59 kB
public/build/assets/vendor-inertia-B9MNlpbx.js         143.48 kB │ gzip: 49.95 kB
✓ built in 4.75s (27 total assets)
```
Manifest verification (`manifest.json`):
```json
"_vendor-react-AOuonjQT.js": {
    "file": "assets/vendor-react-AOuonjQT.js",
    "name": "vendor-react"
},
"_vendor-inertia-B9MNlpbx.js": {
    "file": "assets/vendor-inertia-B9MNlpbx.js",
    "name": "vendor-inertia",
    "imports": ["_vendor-react-AOuonjQT.js"]
},
"_vendor-motion-C96ZWg6v.js": {
    "file": "assets/vendor-motion-C96ZWg6v.js",
    "name": "vendor-motion",
    "imports": ["_vendor-react-AOuonjQT.js"]
},
"_vendor-lucide-DHpDFDmf.js": {
    "file": "assets/vendor-lucide-DHpDFDmf.js",
    "name": "vendor-lucide",
    "imports": ["_vendor-react-AOuonjQT.js"]
}
```
1. `vendor-react` is the pristine root node with 0 external vendor dependencies.
2. `vendor-inertia`, `vendor-motion`, and `vendor-lucide` depend strictly downward on `vendor-react`.
3. Circular dependencies are 100% eliminated.
4. Total compiled assets dropped from 41 to 27.
5. Entry point `app.jsx` collapsed from **290.99 kB** down to **4.19 kB** (a 98.5% payload reduction!).

---

## 2. Logic Chain

1. **Root Dependency Hierarchy**:
   - `react` and `react-dom` (with React 18's internal `scheduler`) are the foundational runtime upon which all other React libraries depend.
   - `@inertiajs/react` depends on `@inertiajs/core` and `react`.
   - `framer-motion` depends on `motion-dom`, `motion-utils`, `tslib`, and `react`.
   - `lucide-react` components depend on `react`.
2. **Failure Mode of Object Syntax**:
   - Under standard Rollup module resolution, when package names are declared statically in an object (`manualChunks: { 'vendor-react': ['react', 'react-dom'] }`), sub-dependencies like `scheduler` and deep exports (`react/jsx-runtime`) are not reliably claimed by `vendor-react` if another package (e.g., `@inertiajs/react`) touches them first.
   - This causes Rollup to allocate `react` to `vendor-inertia`, creating an inverted dependency where `vendor-react` imports `vendor-inertia`.
3. **Robustness of Function Syntax with Windows Normalization**:
   - On Windows (e.g. Laragon environments at `c:\laragon\www\StudioKode`), file paths in Rollup's module IDs may contain backslashes (`\`).
   - By executing `const normalizedId = id.replace(/\\/g, '/')`, all platform discrepancies between POSIX and Windows are eliminated.
   - By matching sub-packages (`scheduler` into `vendor-react`, `motion-dom` and `motion-utils` into `vendor-motion`, `@inertiajs/core` into `vendor-inertia`), every piece of related vendor code is cleanly bundled into its designated chunk.
4. **Impact on HTTP Performance & Browser Caching**:
   - Consolidating the 16+ Lucide micro-chunks into `vendor-lucide` (18.33 kB) eliminates HTTP waterfall overhead and header overhead.
   - Isolating vendor code (`vendor-react`, `vendor-inertia`, `vendor-motion`, `vendor-lucide`) means end-users cache long-lived third-party libraries across multiple application deployments.
   - When application code or admin pages change, only the lightweight entry script (4.19 kB) and the specific changed page chunk are downloaded.
5. **Laravel Vite Plugin Compatibility**:
   - Laravel's `@vite` Blade directive parses `public/build/manifest.json`.
   - The manifest produced by function-based `manualChunks` maintains full standard schema compatibility (`file`, `src`, `isEntry`, `isDynamicEntry`, `imports`).
   - Laravel automatically emits `<link rel="modulepreload">` tags for the vendor chunks imported by `app.jsx`, triggering parallel browser preloading without waterfall delay.
   - Empirical verification: Running `php artisan test --filter=test_vite_production_build_artifacts_present` executed with 24 assertions passing cleanly.
6. **Chunk Size Limit and Sourcemap Policy**:
   - All 4 vendor chunks are under 150 kB uncompressed (under 55 kB gzipped).
   - Setting `chunkSizeWarningLimit: 600` provides headroom above default 500 kB to guarantee zero spurious warnings while protecting against unexpected bloat.
   - Setting `sourcemap: false` is optimal for production security and minimal asset footprint.

---

## 3. Caveats

1. **No Application Code Modified**:
   - Explorer M4.1 strictly operated in read-only mode on application source files.
   - Empirical builds were tested using temporary standalone configuration files (`.agents/teamwork/explorer_m4_1/test-vite-*.config.mjs`) without mutating `c:\laragon\www\StudioKode\vite.config.js`.
   - The production build in `public/build` was cleanly rebuilt with the repository's baseline config at the conclusion of testing.
2. **Third-Party Helper Libraries**:
   - `clsx` and `tailwind-merge` are tiny utility libraries (< 3 kB total). In our function-based configuration, they remain with the chunks that consume them or are bundled into the entry chunk without causing bloat.
   - `axios` is pulled by `@inertiajs/core` and is appropriately contained within `vendor-inertia`.

---

## 4. Conclusion & Proposed Implementation

Feature 23 (Vite Code-Splitting) should be implemented in `vite.config.js` using function-based Rollup `manualChunks` with path normalization.

### Proposed Code Change: `vite.config.js`

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        cors: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    build: {
        chunkSizeWarningLimit: 600,
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        const normalizedId = id.replace(/\\/g, '/');
                        if (
                            normalizedId.includes('/node_modules/react/') ||
                            normalizedId.includes('/node_modules/react-dom/') ||
                            normalizedId.includes('/node_modules/scheduler/')
                        ) {
                            return 'vendor-react';
                        }
                        if (normalizedId.includes('/node_modules/@inertiajs/')) {
                            return 'vendor-inertia';
                        }
                        if (
                            normalizedId.includes('/node_modules/framer-motion/') ||
                            normalizedId.includes('/node_modules/motion-dom/') ||
                            normalizedId.includes('/node_modules/motion-utils/')
                        ) {
                            return 'vendor-motion';
                        }
                        if (normalizedId.includes('/node_modules/lucide-react/')) {
                            return 'vendor-lucide';
                        }
                    }
                },
            },
        },
    },
});
```

### Metrics Comparison Table

| Metric | Baseline (Before) | Optimized (After M4.1) | Improvement |
|---|---|---|---|
| Main Entry Bundle (`app-*.js`) | 290.99 kB (97.7 kB gzip) | 4.19 kB (1.69 kB gzip) | **-98.5% payload** |
| Total Asset Chunks | 41 chunks | 27 chunks | **-34% chunk overhead** |
| Lucide Icon Requests | 16+ separate HTTP chunks | 1 consolidated chunk (18.3 kB) | **100% consolidated** |
| Vendor React Chunk | Bundled in entry | 142.38 kB (45.6 kB gzip) | **Clean cached DAG root** |
| Vendor Inertia Chunk | Bundled in entry | 143.48 kB (50.0 kB gzip) | **Clean cached chunk** |
| Vendor Motion Chunk | 111.82 kB (`proxy-*.js`) | 117.89 kB (`vendor-motion-*.js`) | **Semantic vendor bundle** |
| Manifest Size | 12.51 kB | 8.96 kB | **-28.4% manifest overhead** |
| Build Duration | ~4.7s | ~4.7s | **Zero build time penalty** |

---

## 5. Verification Method

Once the implementer applies the changes to `vite.config.js`:

1. **Execute Vite Production Build**:
   ```powershell
   npm run build
   ```
   - Must exit with code 0.
   - Terminal output must show:
     - `vendor-react-*.js` (~142 kB)
     - `vendor-inertia-*.js` (~143 kB)
     - `vendor-motion-*.js` (~118 kB)
     - `vendor-lucide-*.js` (~18 kB)
     - `app-*.js` (~4.2 kB)
     - Zero warnings about chunks exceeding limit.

2. **Verify Manifest & Asset Integrity via PHPUnit**:
   ```powershell
   php artisan test --filter=test_vite_production_build_artifacts_present
   ```
   - Must pass all 24 assertions confirming all entrypoints and page chunks are present on disk and mapped in `public/build/manifest.json`.

3. **Verify Performance Test Suite**:
   ```powershell
   php artisan test --filter=test_vite_configuration_supports_code_splitting
   ```
   - Must pass verifying `vite.config.js` exists and retains the `laravel` plugin.

4. **Invalidation Conditions**:
   - If `vendor-react` imports `vendor-inertia` in `manifest.json`, the configuration has regressed to circular object resolution.
   - If Lucide micro-chunks (e.g. `check-*.js`, `search-*.js`) reappear in `public/build/assets/`, the Lucide manualChunks regex/filter is failing to match.
