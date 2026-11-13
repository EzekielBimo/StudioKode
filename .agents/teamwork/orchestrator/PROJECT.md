# Project: StudioKode — Landscape & Garden Architecture Portfolio

## Architecture
- **Framework**: Laravel 11.x (PHP 8.3.30)
- **Frontend Layer**: Inertia.js React with Vite, Tailwind CSS, Framer Motion, and Lucide React
- **Database Layer**: MySQL 8.4.3 (Database: `studiokode`, Port: 3306, User: `root`, Password: `""`)
- **Design System**: Luxury landscape & garden architecture studio aesthetic.
  - Color Palette: Off-white (`#F9F8F6`), Deep foliage green (`#1C2A21`), Warm stone (`#E6E2DD`), Charcoal (`#1A1A1A`).
  - Typography: Serif display headings with refined geometric sans body text.
- **Motion System**: Scroll-triggered architectural reveals using Framer Motion (`InView.jsx`) with `opacity: 0, y: 24, filter: 'blur(4px)'` to `opacity: 1, y: 0, filter: 'blur(0px)'`, `once: true`.
- **Image Lightbox**: High-resolution image viewing via `Modal.jsx` in project galleries.
- **Testing**: PHPUnit / Pest feature tests for all web routes, seeder verification, and inquiry persistence, plus Vite production build compilation.

---

## Feature Inventory

| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Laravel 11 Scaffold & MySQL Setup | Bootstrap Laravel 11 using PHP 8.3, configure `.env` for MySQL `studiokode`, enable zip extension | M1 | Survey / R1 |
| 2 | Database Schema: `projects` | Table with `id`, `title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size`, `cover_image`, `masterplan_image`, `is_featured`, `published_at`, `timestamps`. No category table. | M1 | Survey / R1 |
| 3 | Database Schema: `project_images` | Table with `id`, `project_id` (FK cascade), `image_path`, `caption`, `sort_order`, `timestamps` | M1 | Survey / R1 |
| 4 | Database Schema: `inquiries` | Table with `id`, `name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message`, `status` (default: 'new'), `timestamps` | M1 | Survey / R1 |
| 5 | Eloquent Models & Relationships | `Project`, `ProjectImage`, `Inquiry` with proper fillable attributes, casts, and relations | M1 | Survey / R1 |
| 6 | Database Seeder | `DatabaseSeeder.php` populating 6 realistic garden architecture projects with gallery images | M1 | Survey / R1 |
| 7 | Inertia Middleware & Root View | Setup `HandleInertiaRequests` middleware and `app.blade.php` | M1 | Survey / R2 |
| 8 | Web Routes & Controllers | `HomeController`, `ProjectController`, `PageController`, `InquiryController` covering all endpoints | M1 | Survey / R2 |
| 9 | Inquiry Submission & Validation | POST `/inquiries` with validation, database persistence, and flash message | M1 | Survey / R1, R2 |
| 10 | Automated Feature Test Suite | PHPUnit tests verifying routes (200 OK), seeder execution, and inquiry storage | M1 | Survey / R5 |
| 11 | Frontend Tooling & NPM Packages | Install React, Inertia React, Vite plugin, Tailwind, Framer Motion, Lucide React | M2 | Survey / R3, R4 |
| 12 | Tailwind Theme Configuration | Organic architectural palette (#F9F8F6, #1C2A21, #E6E2DD, #1A1A1A) and typography | M2 | Survey / R4 |
| 13 | UI Components: `Button.jsx`, `Badge.jsx`, `Input.jsx` | Strict paths in `resources/js/Components/UI/` | M2 | Survey / R3 |
| 14 | UI Component: `InView.jsx` | Framer Motion scroll entrance with blur reveal variants and once=true | M2 | Survey / R3, R4 |
| 15 | UI Component: `Modal.jsx` | Lightbox modal dialog for high-resolution gallery viewing | M2 | Survey / R3, R4 |
| 16 | Navigation Components | `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx` under `resources/js/Components/Navigation/` | M2 | Survey / R3 |
| 17 | Layout: `PublicLayout.jsx` | Base wrapper under `resources/js/Layouts/PublicLayout.jsx` | M2 | Survey / R3 |
| 18 | Portfolio Component: `ProjectCard.jsx` | Project card with hover effects, metadata badges, wrapped in `InView` | M2 | Survey / R3, R4 |
| 19 | Portfolio Component: `FilterBar.jsx` | Search (keyword/location) and sorting (Newest, Oldest, Largest Area in m²), no category filters | M2 | Survey / R3, R4 |
| 20 | Portfolio Component: `ImageGallery.jsx` | Masonry/grid gallery with InView stagger and Modal lightbox integration | M2 | Survey / R3, R4 |
| 21 | Page: `Home/Index.jsx` | Hero presentation, featured projects showcase, studio philosophy, inquiry CTA | M2 | Survey / R2, R3 |
| 22 | Page: `Projects/Index.jsx` | Full project listing with interactive search and sorting via FilterBar | M2 | Survey / R2, R3 |
| 23 | Page: `Projects/Show.jsx` | Detailed project presentation: narrative, specs, masterplan, image gallery with lightbox | M2 | Survey / R2, R3 |
| 24 | Page: `Studio/Index.jsx` | Studio background, design philosophy, team, and multi-stage process | M2 | Survey / R2, R3 |
| 25 | Page: `Services/Index.jsx` | Landscape and garden architectural services offered with deliverables | M2 | Survey / R2, R3 |
| 26 | Page: `Contact/Index.jsx` | Studio contact info, interactive inquiry form with validation and feedback | M2 | Survey / R2, R3 |
| 27 | E2E Testing, Adversarial Audit & Final Gate | Run test suite, compile Vite assets with zero errors, verify integrity | M3 | Survey / R5 |

---

## Milestones

| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | Backend Architecture, Database, Routing & Feature Tests | Bootstrap Laravel 11, configure MySQL `.env`, create migrations (`projects`, `project_images`, `inquiries`), Eloquent models, `DatabaseSeeder.php` with 6 realistic projects, Inertia setup (`app.blade.php`, `HandleInertiaRequests`), controllers (`HomeController`, `ProjectController`, `PageController`, `InquiryController`), routes in `routes/web.php`, and PHPUnit Feature tests | none | DONE |
| M2 | Frontend Architecture, Design System, Multi-Page Portfolio & Motion | NPM dependencies, Tailwind palette configuration, UI primitives (`Button`, `Badge`, `Input`, `Modal`, `InView`), Navigation (`Navbar`, `MobileMenu`, `Footer`), `PublicLayout.jsx`, Portfolio components (`ProjectCard`, `FilterBar`, `ImageGallery`), and all 6 Pages (`Home`, `Projects/Index`, `Projects/Show`, `Studio`, `Services`, `Contact`) | M1 | DONE |
| M3 | E2E Verification, Adversarial Hardening & Acceptance Sign-off | Execute PHPUnit/Pest suite (100% pass), execute `npm run build` (zero errors), verify acceptance criteria, and perform forensic integrity audit | M2 | DONE |

---

## Interface Contracts

### Backend ↔ Frontend Inertia Props Contracts

#### 1. `Home/Index`
- `featuredProjects`: Array of `Project` objects with `cover_image`, `title`, `slug`, `location`, `year`, `area_size`.
- `statistics`: Array of objects: `[{ value: '15+', label: 'Years Experience' }, { value: '48+', label: 'Curated Sanctuaries' }, { value: '12', label: 'Design Accolades' }]`.

#### 2. `Projects/Index`
- `projects`: Array of all published `Project` objects.
- `filters`: Object `{ search: string|null, sort: string|null }`.

#### 3. `Projects/Show`
- `project`: Complete `Project` object with `id`, `title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size`, `cover_image`, `masterplan_image`.
- `images`: Array of `ProjectImage` objects `[{ id, image_path, caption, sort_order }]`.
- `relatedProjects`: Array of 2–3 other `Project` objects.

#### 4. `Studio/Index`, `Services/Index`
- Static architectural narratives, design philosophies, deliverables, team profiles, and methodology stages passed as Inertia props or structured constants.

#### 5. `Contact/Index` & `POST /inquiries`
- Form fields:
  - `name`: string, required, max 255
  - `email`: email, required, max 255
  - `phone`: string, nullable, max 50
  - `site_location`: string, nullable, max 255
  - `estimated_area`: string, nullable, max 100
  - `budget_range`: string, nullable, max 100
  - `message`: string, required, max 5000
- Response: Redirect back with `session('success', 'Thank you for contacting StudioKode. Our principal architects will review your project.')`.

---

## Code Layout

Strict required paths matching R3:
```
resources/js/
├── Components/
│   ├── Navigation/
│   │   ├── Footer.jsx
│   │   ├── MobileMenu.jsx
│   │   └── Navbar.jsx
│   ├── Portfolio/
│   │   ├── FilterBar.jsx
│   │   ├── ImageGallery.jsx
│   │   └── ProjectCard.jsx
│   └── UI/
│       ├── Badge.jsx
│       ├── Button.jsx
│       ├── InView.jsx
│       ├── Input.jsx
│       └── Modal.jsx
├── Layouts/
│   └── PublicLayout.jsx
└── Pages/
    ├── Contact/
    │   └── Index.jsx
    ├── Home/
    │   └── Index.jsx
    ├── Projects/
    │   ├── Index.jsx
    │   └── Show.jsx
    ├── Services/
    │   └── Index.jsx
    └── Studio/
        └── Index.jsx
```

Backend Layout:
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── InquiryController.php
│   │   ├── PageController.php
│   │   └── ProjectController.php
│   └── Middleware/
│       └── HandleInertiaRequests.php
└── Models/
    ├── Inquiry.php
    ├── Project.php
    └── ProjectImage.php

database/
├── migrations/
│   ├── xxxx_create_projects_table.php
│   ├── xxxx_create_project_images_table.php
│   └── xxxx_create_inquiries_table.php
└── seeders/
    └── DatabaseSeeder.php

tests/
└── Feature/
    ├── InquiryTest.php
    ├── RouteTest.php
    └── SeederTest.php
```

---

## Environment & Execution Rules
1. **PHP CLI**: Always prepend `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64` to `$env:PATH`.
2. **Database Engine**: MySQL 8.4 on port 3306. Run `CREATE DATABASE IF NOT EXISTS studiokode;` before running migrations.
3. **No Categories**: Schema and filters must strictly omit categories.
4. **Framer Motion**: InView must use `useInView` and `motion` with variants `opacity: 0, y: 24, filter: 'blur(4px)'` to `opacity: 1, y: 0, filter: 'blur(0px)'` and `once: true`.
5. **No Cheating**: All implementations must be authentic, functional, and verified by tests.
