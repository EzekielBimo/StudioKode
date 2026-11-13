# Handoff Report: StudioKode Complete Specification Mining

**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\teamwork_spec_miner_survey_2`  
**Milestone**: Survey & Specification Mining  
**Date**: 2026-11-08  
**Author**: Survey Spec Miner 2  

---

## 1. Observation

Direct observations from the workspace and specification source:

1. **Authoritative Specification Document (`ORIGINAL_REQUEST.md`)**:
   - `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md`: Contains lines 1–63 specifying requirements R1 through R5 and backend/frontend acceptance criteria.
   - Quote R1 (lines 12–17): *"Set up Laravel 11+ with Inertia.js and configure MySQL as the database. Implement Eloquent models (`Project`, `ProjectImage`, `Inquiry`) and database migrations matching the schema: `projects`: `id`, `title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size` (e.g., '240 m²'), `cover_image`, `masterplan_image`, `is_featured` (boolean), `published_at` (timestamp), `timestamps`. (No category table; all projects are garden/landscape designs). `project_images`: `id`, `project_id` (foreign key), `image_path`, `caption`, `sort_order` (integer), `timestamps`. `inquiries`: `id`, `name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message`, `status` (default: 'new'), `timestamps`. Implement `DatabaseSeeder.php` seeding 6 realistic garden architecture projects with dummy gallery images."*
   - Quote R2 (lines 19–26): Controllers required: `HomeController`, `ProjectController`, `PageController`, `InquiryController`. Pages: `Home/Index`, `Projects/Index`, `Projects/Show`, `Studio/Index`, `Services/Index`, `Contact/Index`.
   - Quote R3 (lines 28–34): Strict frontend directory layout under `resources/js/`:
     - `Components/UI/`: `Button.jsx`, `Badge.jsx`, `Modal.jsx`, `Input.jsx`, `InView.jsx`.
     - `Components/Navigation/`: `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`.
     - `Components/Portfolio/`: `ProjectCard.jsx`, `FilterBar.jsx`, `ImageGallery.jsx`.
     - `Layouts/`: `PublicLayout.jsx`.
     - `Pages/`: `Home/Index.jsx`, `Projects/Index.jsx`, `Projects/Show.jsx`, `Studio/Index.jsx`, `Services/Index.jsx`, `Contact/Index.jsx`.
   - Quote R4 (lines 36–43): Styling & Motion:
     - Color palette: off-white (`#F9F8F6`), deep foliage green (`#1C2A21`), warm stone (`#E6E2DD`), charcoal (`#1A1A1A`).
     - Motion: Framer-motion `InView.jsx` using `useInView` and `motion` with variants `opacity: 0, y: 24, filter: 'blur(4px)'` to `opacity: 1, y: 0, filter: 'blur(0px)'` with `once = true`.
     - Lightbox: `Modal.jsx` for high-resolution project gallery lightbox viewing.
     - FilterBar: Keyword/location search and sorting (`Newest`, `Oldest`, `Largest Area Size in m²`), **no category filters**.
     - Iconography: Lucide React.
   - Quote R5 (lines 44–48): Automated test suites for web routes, inquiry persistence, database seeder, and Vite build compilation.

2. **Workspace Probing Observations**:
   - `c:\laragon\www\StudioKode`: Currently contains only `.agents/`. No project code has been created yet.
   - Default CLI Environment check:
     - `php -v` in default PATH returns `PHP 8.0.3 (cli)` located at `C:\laragon\bin\php\php8.0.3\php.exe`.
     - `node -v` returns `v24.15.0`.
     - `npm -v` returns `12.2.2`.
     - `composer -V` returns `Composer version 2.9.4` via `C:\laragon\bin\composer\composer.bat`.
   - Laragon Environment probing:
     - Directory `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64` exists. Running `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -v` reports `PHP 8.3.30 (cli)`.
     - Extensions `pdo_mysql` and `pdo_sqlite` are compiled into PHP 8.3.30.
     - MySQL 8.4.3 is installed at `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe`.

---

## 2. Logic Chain

1. **Database Schema & Constraints**:
   - R1 explicitly states: *"No category table; all projects are garden/landscape designs."* Therefore, the schema must strictly omit any `categories` table or `category_id` foreign key.
   - The `area_size` column is specified with example `"240 m²"`. Storing this as `VARCHAR(50)` satisfies the display format, but because R4 requires sorting by "Largest Area Size in m²", sorting logic must either extract the numeric portion via SQL (`CAST(REPLACE(REPLACE(area_size, ' m²', ''), ',', '') AS UNSIGNED)`) or client-side JavaScript (`parseInt(area.replace(/[^0-9]/g, ''))`). Both approaches must be supported.
   - Eloquent relationships: `Project` has many `ProjectImage` (ordered by `sort_order`). `ProjectImage` belongs to `Project`. `Inquiry` stands alone.

2. **Seeder Engineering**:
   - To demonstrate a high-end luxury garden architecture capability showcase, the 6 projects must span diverse botanical and architectural typologies (Japanese dry garden & moss sanctuary, sub-alpine lake terraces, urban micro-climate courtyard, montane alpine rockery, desert modernism xeriscape, Atlantic coastal pine & dune refuge).
   - Each project requires a realistic title, unique slug, detailed architectural narrative, location, year, formatted area in m², cover image, masterplan drawing, and 3–4 gallery images with descriptive captions.

3. **Backend Routing & Controllers**:
   - Routing structure matches standard Laravel 11 invokable or resource/explicit routes:
     - `GET /` -> `HomeController@index` -> returns `Home/Index`
     - `GET /projects` -> `ProjectController@index` -> returns `Projects/Index`
     - `GET /projects/{slug}` -> `ProjectController@show` -> returns `Projects/Show`
     - `GET /studio` -> `PageController@studio` -> returns `Studio/Index`
     - `GET /services` -> `PageController@services` -> returns `Services/Index`
     - `GET /contact` -> `PageController@contact` -> returns `Contact/Index`
     - `POST /inquiries` (or `POST /contact`) -> `InquiryController@store` -> validates and stores in `inquiries`, redirects back with flash message.

4. **Frontend Architecture & File Organization**:
   - R3 requires exact file paths for all 14 components. Any deviation in path or naming violates R3 and the acceptance criteria.
   - All pages must be wrapped in `PublicLayout.jsx` with consistent navigation, typography, and footer.

5. **Motion and Design System**:
   - `InView.jsx` provides the signature architectural reveal. Using Framer Motion's `useInView` with `filter: blur(4px)` to `blur(0px)` and vertical translate creates an ethereal, high-end editorial feel appropriate for an architectural studio.
   - Tailwind color tokens must precisely map `#F9F8F6`, `#1C2A21`, `#E6E2DD`, and `#1A1A1A`.

---

## 3. Caveats

1. **CLI PHP Version Mismatch**: Default system PATH points to `PHP 8.0.3`, which fails Laravel 11's requirement of `PHP >= 8.2`. The project implementers must prepend `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64` to `PATH` in shell sessions when executing Composer and Artisan commands.
2. **Database Connection in Test vs Runtime**: MySQL 8.4.3 is installed in Laragon. If MySQL service is not running during automated testing or local builds, automated tests can utilize SQLite in-memory (`:memory:`) or MySQL if running.
3. **Image Asset Resolution**: Seeder image paths should reference local public paths (`/images/projects/...`) or reliable high-resolution Unsplash architectural URLs to guarantee immediate visual presentation without broken image icons.

---

## 4. Conclusion

All 9 target areas have been exhaustively probed and specified. The system architecture, data models, seeder datasets, controller props, component contracts, motion parameters, and automated verification suites are fully defined below in the authoritative specification tables.

---

## 5. Verification Method

To independently verify this specification against project execution:
1. **Schema Check**: Run `php artisan migrate:status` to ensure `projects`, `project_images`, and `inquiries` tables exist with specified columns and no category table.
2. **Seeder Verification**: Run `php artisan db:seed` and verify `SELECT COUNT(*) FROM projects` equals 6 and `SELECT COUNT(*) FROM project_images` >= 18.
3. **Route Verification**: Run `php artisan route:list` to verify all 7 routes exist with correct names and controller bindings.
4. **Component Verification**: Check that all 14 React files exist at `resources/js/` per R3.
5. **Frontend Build Verification**: Run `npm run build` and ensure bundle builds with exit code 0.
6. **Automated Test Run**: Run project tests (`php artisan test` or `./vendor/bin/pest`) to verify 100% pass rate.

---

# Detailed Specifications & Feature Mining

## Features Discovered

| # | Category | Feature | Description | Inputs | Outputs | Error Behavior | Discovered Via |
|---|----------|---------|-------------|--------|---------|----------------|----------------|
| 1 | Database | `projects` table | Stores landscape architectural projects with metadata | Schema: `id`, `title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size`, `cover_image`, `masterplan_image`, `is_featured`, `published_at`, `timestamps` | Database table & Eloquent model `Project` | Migration failure if duplicate table or invalid types | `ORIGINAL_REQUEST.md` R1 |
| 2 | Database | `project_images` table | Stores gallery images linked to a project | Schema: `id`, `project_id` (FK cascade), `image_path`, `caption`, `sort_order`, `timestamps` | Database table & Eloquent model `ProjectImage` | Foreign key constraint violation if `project_id` invalid | `ORIGINAL_REQUEST.md` R1 |
| 3 | Database | `inquiries` table | Stores client project consultation requests | Schema: `id`, `name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message`, `status` (default: 'new'), `timestamps` | Database table & Eloquent model `Inquiry` | Validation error if required columns null | `ORIGINAL_REQUEST.md` R1 |
| 4 | Database | No Category Table | Strict requirement: projects do not have category associations | N/A | Flat project taxonomy | Disallowed category foreign keys | `ORIGINAL_REQUEST.md` R1 |
| 5 | Seeder | DatabaseSeeder | Seeds 6 realistic garden architecture projects with gallery photos | `php artisan db:seed` | 6 projects + ~22 project images populated | Rollback on database error | `ORIGINAL_REQUEST.md` R1 |
| 6 | Routing | Home Route (`/`) | Displays hero, featured projects, philosophy, CTA | HTTP GET `/` | Inertia render `'Home/Index'` with `featuredProjects`, `statistics` | HTTP 500 if query fails | `ORIGINAL_REQUEST.md` R2 |
| 7 | Routing | Projects Index Route (`/projects`) | Lists all projects with search & sorting | HTTP GET `/projects?search=...&sort=...` | Inertia render `'Projects/Index'` with filtered `projects`, `filters` | Empty array if no results | `ORIGINAL_REQUEST.md` R2 |
| 8 | Routing | Project Show Route (`/projects/{slug}`) | Detailed project view with masterplan and gallery | HTTP GET `/projects/{slug}` | Inertia render `'Projects/Show'` with `project`, `images`, `relatedProjects` | HTTP 404 ModelNotFoundException if slug invalid | `ORIGINAL_REQUEST.md` R2 |
| 9 | Routing | Studio Route (`/studio`) | Studio background, philosophy, team, and process | HTTP GET `/studio` | Inertia render `'Studio/Index'` with `team`, `philosophy`, `process` | HTTP 500 if view error | `ORIGINAL_REQUEST.md` R2 |
| 10 | Routing | Services Route (`/services`) | Landscape architectural services and deliverables | HTTP GET `/services` | Inertia render `'Services/Index'` with `services` data | HTTP 500 if view error | `ORIGINAL_REQUEST.md` R2 |
| 11 | Routing | Contact Route (`/contact`) | Studio contact details and consultation inquiry form | HTTP GET `/contact` | Inertia render `'Contact/Index'` with studio info | HTTP 500 if view error | `ORIGINAL_REQUEST.md` R2 |
| 12 | Routing | Inquiry Store Route (`/inquiries`) | Validates and stores incoming contact form submission | HTTP POST payload: `name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message` | HTTP 302 Redirect back with flash `success` | HTTP 302 with validation errors bag (422 in JSON) | `ORIGINAL_REQUEST.md` R2 |
| 13 | Component | `Components/UI/Button.jsx` | Luxury button supporting primary foliage, stone, outline, link | Props: `variant`, `size`, `href`, `as`, `children`, `className`, native props | Rendered `<button>` or `<Link>` element | Fallback to default variant | `ORIGINAL_REQUEST.md` R3 |
| 14 | Component | `Components/UI/Badge.jsx` | Minimal architectural tag for year, area size, metadata | Props: `variant`, `children`, `className` | Styled `<span>` tag | Fallback to stone badge | `ORIGINAL_REQUEST.md` R3 |
| 15 | Component | `Components/UI/Modal.jsx` | Accessible dialog with backdrop blur & Escape listener | Props: `isOpen`, `onClose`, `children`, `title` | Portal/Animated modal overlay | Silently ignore if closed | `ORIGINAL_REQUEST.md` R3 |
| 16 | Component | `Components/UI/Input.jsx` | Architectural input & textarea with error display | Props: `label`, `error`, `name`, `type`, `multiline`, `rows`, etc. | Form field with label and error message | Displays error message in red/crimson | `ORIGINAL_REQUEST.md` R3 |
| 17 | Component | `Components/UI/InView.jsx` | Framer-motion scroll entrance wrapper | Props: `children`, `variants`, `transition`, `viewOptions` | Animated container (`motion.div`) | Renders visible if motion unsupported | `ORIGINAL_REQUEST.md` R3, R4 |
| 18 | Component | `Components/Navigation/Navbar.jsx` | Header with logo wordmark, nav links, mobile trigger | Props: none (reads Inertia URL) | Responsive `<header>` with backdrop blur | None | `ORIGINAL_REQUEST.md` R3 |
| 19 | Component | `Components/Navigation/MobileMenu.jsx` | Slide-out mobile menu overlay with staggered links | Props: `isOpen`, `onClose` | Animated mobile drawer overlay | Unmounted / hidden when closed | `ORIGINAL_REQUEST.md` R3 |
| 20 | Component | `Components/Navigation/Footer.jsx` | Comprehensive footer with manifesto, links, social | Props: none | Architectural `<footer>` | None | `ORIGINAL_REQUEST.md` R3 |
| 21 | Component | `Components/Portfolio/ProjectCard.jsx` | Project card with hover zoom, specs, wrapped in InView | Props: `project`, `index` | `<article>` with image, title, location, area | Fallback placeholder if cover missing | `ORIGINAL_REQUEST.md` R3, R4 |
| 22 | Component | `Components/Portfolio/FilterBar.jsx` | Search input + Sort dropdown (Newest, Oldest, Largest Area) | Props: `search`, `onSearchChange`, `sort`, `onSortChange`, `count` | Filter toolbar without category tabs | Clear button resets search | `ORIGINAL_REQUEST.md` R3, R4 |
| 23 | Component | `Components/Portfolio/ImageGallery.jsx` | Masonry/grid gallery with InView stagger and modal lightbox | Props: `images` | Asymmetric image grid; click opens Modal | Fallback empty message | `ORIGINAL_REQUEST.md` R3, R4 |
| 24 | Layout | `Layouts/PublicLayout.jsx` | Base layout with Navbar, flash notifications, Footer | Props: `children`, `title` | Full page HTML structure with `<Head>` | None | `ORIGINAL_REQUEST.md` R3 |
| 25 | Design System | Organic Architectural Palette | Off-white (`#F9F8F6`), deep foliage (`#1C2A21`), warm stone (`#E6E2DD`), charcoal (`#1A1A1A`) | Tailwind theme config | Unified luxury architectural visual design | None | `ORIGINAL_REQUEST.md` R4 |
| 26 | Motion | Blur Reveal Scroll Animation | Motion entrance: `opacity: 0, y: 24, filter: 'blur(4px)'` to `opacity: 1, y: 0, filter: 'blur(0px)'` | Scroll position via `useInView` | Smooth entrance animation once | Preserves layout if JS disabled | `ORIGINAL_REQUEST.md` R4 |
| 27 | Gallery Lightbox | Lightbox Modal | Fullscreen image viewer with caption, counter, prev/next | Selected image index, images array | Interactive modal dialog with keyboard nav | Closes on backdrop or Esc | `ORIGINAL_REQUEST.md` R4 |
| 28 | Filter Logic | Largest Area Size in m² Sorting | Parses numeric area (e.g. "2,100 m²" -> 2100) and sorts descending | Project array with `area_size` | Sorted projects array | Returns 0 for unparseable strings | `ORIGINAL_REQUEST.md` R4 |
| 29 | Verification | Feature Test Suite | PHPUnit/Pest suite for all routes, inquiry submission, seeder | `php artisan test` | Test results (assertions pass) | Fails build if assertions fail | `ORIGINAL_REQUEST.md` R5 |
| 30 | Verification | Asset Build Compilation | Vite / Rollup build verifying zero errors | `npm run build` | Compiled JS/CSS in `public/build` | Build failure exit code 1 if errors | `ORIGINAL_REQUEST.md` R5 |

---

## Edge Cases

| # | Feature | Input | Observed / Specified Behavior |
|---|---------|-------|-------------------------------|
| 1 | `ProjectController@show` | Slug that does not exist in database (e.g. `/projects/space-garden`) | Laravel triggers `ModelNotFoundException` -> renders standard 404 error page. |
| 2 | `FilterBar.jsx` Sorting | Projects with commas in area (e.g., "1,250 m²" vs "380 m²" vs "2,100 m²") | Numeric parser must strip commas (`replace(/,/g, '')`) before `parseInt` so "1,250" evaluates to `1250` and sorts higher than `380` and lower than `2100`. |
| 3 | `FilterBar.jsx` Search | Search string with special regex characters or leading/trailing spaces (`"  Kyoto  "`) | Search input is trimmed and converted to lowercase before substring matching; no unhandled regex crashes. |
| 4 | `FilterBar.jsx` Empty State | Search keyword matching zero projects (e.g., `"mars"`) | Renders elegant empty state: "No projects match your search criteria" with a "Reset Filters" action button. |
| 5 | `InquiryController@store` | Email formatted improperly or missing mandatory fields | Validator fails, redirects back with `$errors` bag; form displays inline error messages under affected inputs; user input preserved via `useForm`. |
| 6 | `InquiryController@store` | Excessively long message (> 5000 characters) or malicious HTML | `max:5000` rule triggers validation failure; Blade/React escapers neutralize HTML entities to prevent XSS. |
| 7 | `InView.jsx` | User rapidly scrolling or viewport resize | `useInView` triggers once (`once: true`), locking in the visible state so animation does not re-trigger repeatedly during scrolling. |
| 8 | `Modal.jsx` Lightbox | User navigates previous at first image (index 0) or next at last image | Modal loops around (wrap-around navigation) or disables previous/next buttons to prevent out-of-bounds indexing. |
| 9 | `Project` Model | `masterplan_image` is NULL for a project | `Projects/Show.jsx` conditionally hides the Masterplan section or displays an architectural schematic placeholder without throwing undefined errors. |
| 10 | Environment CLI | Running `composer` or `php artisan` using system PATH (`PHP 8.0.3`) | Command fails with PHP version incompatibility. Workaround: execute commands by prepending `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64` to `PATH`. |

---

# Detailed Implementation Blueprints

### Blueprint 1: Database Migrations & Models

#### Migration 1: `create_projects_table.php`
```php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('excerpt')->nullable();
    $table->longText('description');
    $table->string('client')->nullable();
    $table->string('location');
    $table->unsignedSmallInteger('year');
    $table->string('area_size', 50); // e.g. "380 m²"
    $table->string('cover_image');
    $table->string('masterplan_image')->nullable();
    $table->boolean('is_featured')->default(false)->index();
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
```

#### Migration 2: `create_project_images_table.php`
```php
Schema::create('project_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
    $table->string('image_path');
    $table->string('caption')->nullable();
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

#### Migration 3: `create_inquiries_table.php`
```php
Schema::create('inquiries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('phone', 50)->nullable();
    $table->string('site_location')->nullable();
    $table->string('estimated_area', 100)->nullable();
    $table->string('budget_range', 100)->nullable();
    $table->text('message');
    $table->string('status', 50)->default('new');
    $table->timestamps();
});
```

---

### Blueprint 2: Seeder Dataset (6 Garden Architecture Projects)

1. **The Moss & Stone Sanctuary**
   - Title: `The Moss & Stone Sanctuary`
   - Slug: `moss-stone-sanctuary`
   - Client: `Private Collector`
   - Location: `Kyoto, Japan`
   - Year: `2024`
   - Area Size: `380 m²`
   - Cover: `https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1600&q=80`
   - Masterplan: `https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80`
   - Excerpt: `A meditative Japanese courtyard garden balancing ancient lichen-encrusted granite, velvet bryophyte carpets, and blackened cedar pavilions.`
   - Description: `Designed as an intimate contemplative sanctuary within Kyoto's historic outskirts. The site required careful soil conditioning to sustain indigenous Polytrichum commune moss species. Weathered megalithic stones sourced from Tamba quarries define the central dry-creek rill, flanked by pruned Japanese Stewartia and weeping maples. Rainwater from the cedar pavilion is guided into a hand-carved stone tsukubai basin.`
   - Featured: `true`
   - Gallery Images (4):
     - `https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1200&q=80` (Caption: "Entrance stone walkway embedded in lush velvet moss margins")
     - `https://images.unsplash.com/photo-1545239351-ef35f43d514b?auto=format&fit=crop&w=1200&q=80` (Caption: "Hand-carved granite water basin and bamboo water flume")
     - `https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80` (Caption: "Acer palmatum canopy casting dappled shadows")
     - `https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1200&q=80` (Caption: "Evening illumination along the dry-creek granite rill")

2. **Verdant Terrace Estate**
   - Title: `Verdant Terrace Estate`
   - Slug: `verdant-terrace-estate`
   - Client: `Villa Bellini Trust`
   - Location: `Lake Como, Italy`
   - Year: `2023`
   - Area Size: `1,250 m²`
   - Cover: `https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80`
   - Masterplan: `https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80`
   - Excerpt: `Cascading limestone terraces descending toward Lake Como, punctuated by columnar cypresses, fragrant citrus groves, and neoclassical water rills.`
   - Description: `Revitalizing a historic 19th-century hillside estate, this landscape masterplan reconciles severe steep topography with elegant multi-tier living spaces. Hand-dressed Moltrasio stone walls support olive orchards, lavender terraces, and stepped water runnels that create acoustic serenity. The lowest terrace features a sunken infinity reflection pool visually dissolving into the lake.`
   - Featured: `true`
   - Gallery Images (4):
     - `https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80` (Caption: "Panoramic terrace view across Lake Como")
     - `https://images.unsplash.com/photo-1524813686514-a57563d77d61?auto=format&fit=crop&w=1200&q=80` (Caption: "Symmetrical cypress promenade and tiered stone steps")
     - `https://images.unsplash.com/photo-1533759413974-9e15f3b745ac?auto=format&fit=crop&w=1200&q=80` (Caption: "Sunken limestone reflecting pool surrounded by fragrant lavender")
     - `https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1200&q=80` (Caption: "Citrus grove pergola with reclaimed timber beams")

3. **Atelier Botanica Courtyard**
   - Title: `Atelier Botanica Courtyard`
   - Slug: `atelier-botanica-courtyard`
   - Client: `Studio K Architects`
   - Location: `Melbourne, Australia`
   - Year: `2024`
   - Area Size: `180 m²`
   - Cover: `https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=1600&q=80`
   - Masterplan: `https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80`
   - Excerpt: `A sculptural micro-climate sanctuary within an urban heritage envelope, showcasing architectural tree ferns and blackened steel reflecting pools.`
   - Description: `Concealed within a Victorian brick warehouse conversion, this compact courtyard functions as an ethereal living lung. Multi-layered canopy plantings of Dicksonia antarctica tree ferns establish vertical softness against textured industrial masonry. Custom raw blackened steel water mirrors cool the ambient air temperature, while permeable bluestone gravel ensures complete on-site rainwater infiltration.`
   - Featured: `true`
   - Gallery Images (3):
     - `https://images.unsplash.com/photo-1463936575829-25148e1db1b8?auto=format&fit=crop&w=1200&q=80` (Caption: "Canopy layer of Dicksonia antarctica architectural tree ferns")
     - `https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?auto=format&fit=crop&w=1200&q=80` (Caption: "Blackened steel reflecting pool mirroring heritage red brickwork")
     - `https://images.unsplash.com/photo-1507089947368-19c1da9775ae?auto=format&fit=crop&w=1200&q=80` (Caption: "Bluestone paving details under filtered botanical light")

4. **Highland Mist Alpine Garden**
   - Title: `Highland Mist Alpine Garden`
   - Slug: `highland-mist-alpine-garden`
   - Client: `Dr. Hans & Elena Weber`
   - Location: `Zurich, Switzerland`
   - Year: `2022`
   - Area Size: `850 m²`
   - Cover: `https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1600&q=80`
   - Masterplan: `https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80`
   - Excerpt: `A biodiverse montane rockery and wild alpine meadow, embracing glacial erratic stones and cold-hardy botanical tapestries.`
   - Description: `Perched on a sub-alpine ridge overlooking the lake, this garden embraces severe winter frost and intense summer sun. Glacial erratic gneiss rocks discovered during excavation were repositioned to anchor micro-topographies. Planted with dryas, edelweiss, creeping thyme, dwarf mountain pines (Pinus mugo), and naturalized birch groves, the scheme requires zero synthetic irrigation and provides critical high-altitude pollinator habitat.`
   - Featured: `false`
   - Gallery Images (3):
     - `https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80` (Caption: "Alpine rock garden enveloped in morning mountain mist")
     - `https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80` (Caption: "Native Pinus mugo and silver birch grove boundary")
     - `https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1200&q=80` (Caption: "Sunken granite fire gathering ring overlooking the valley")

5. **Oasis Dune Residence**
   - Title: `Oasis Dune Residence`
   - Slug: `oasis-dune-residence`
   - Client: `Vance & Morgan Design`
   - Location: `Palm Springs, California`
   - Year: `2023`
   - Area Size: `620 m²`
   - Cover: `https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=1600&q=80`
   - Masterplan: `https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=80`
   - Excerpt: `Sculptural desert xeriscaping harmonizing rammed-earth windbreaks, specimen agaves, and pale gravel swales with mid-century lines.`
   - Description: `Responding to the harsh Sonoran climate, this landscape celebrates the architectural power of desert flora. Massive rammed-earth walls create protected microclimates and cast long geometric shadows. A curated collection of specimen blue glow agaves, golden barrel cacti, and palo verde trees rise from decomposed granite beds designed for ephemeral flash-rain harvesting.`
   - Featured: `false`
   - Gallery Images (4):
     - `https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80` (Caption: "Specimen golden barrel cacti framing rammed-earth wall")
     - `https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80` (Caption: "Palo verde canopy casting fine shadows on pale granite gravel")
     - `https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=1200&q=80` (Caption: "Architectural Agave attenuata grouping along concrete steps")
     - `https://images.unsplash.com/photo-1470240731273-7821a6eeb6bd?auto=format&fit=crop&w=1200&q=80` (Caption: "Dusk lighting across desert swale landscape")

6. **Coastal Dune & Pine Refuge**
   - Title: `Coastal Dune & Pine Refuge`
   - Slug: `coastal-dune-pine-refuge`
   - Client: `Herdade da Costa`
   - Location: `Comporta, Portugal`
   - Year: `2024`
   - Area Size: `2,100 m²`
   - Cover: `https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80`
   - Masterplan: `https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1200&q=80`
   - Excerpt: `A vast coastal sanctuary celebrating maritime pines, wild dune grass stabilization, and weathered timber boardwalks navigating sandy terrain.`
   - Description: `Spanning two hectares along the Atlantic littoral, this sensitive coastal restoration preserves fragile dune ecologies. Elevated untreated ipe boardwalks float above shifting white sands, guiding visitors between naturalized clusters of umbrella pine (Pinus pinea), sea lavender, and aromatic curry plants. A biological natural swimming pond filtered entirely by reeds anchors the central living area.`
   - Featured: `true`
   - Gallery Images (4):
     - `https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80` (Caption: "Floating timber boardwalk winding through maritime pine forest")
     - `https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80` (Caption: "Natural biological pool filtered by indigenous wetland reeds")
     - `https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=1200&q=80` (Caption: "Dune grass stabilization and Atlantic ocean view")
     - `https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80` (Caption: "Minimalist timber shaded terrace facing coastal dunes")

---

### Blueprint 3: InView.jsx Specification

```jsx
import React from 'react';
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
  duration: 0.8,
  ease: [0.21, 0.47, 0.32, 0.98],
};

export default function InView({
  children,
  className = '',
  variants = defaultVariants,
  transition = defaultTransition,
  viewOptions = { once: true, margin: '-10% 0px' },
  as = 'div',
  delay = 0,
}) {
  const ref = React.useRef(null);
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

---

### Blueprint 4: FilterBar.jsx Specification

```jsx
import React from 'react';
import { Search, SlidersHorizontal, X } from 'lucide-react';

export default function FilterBar({
  search = '',
  onSearchChange,
  sort = 'newest',
  onSortChange,
  totalCount = 0,
  filteredCount = 0,
}) {
  return (
    <div className="w-full bg-[#F9F8F6] border-y border-[#E6E2DD] py-4 my-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* Search Input */}
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#6E6B65]" />
          <input
            type="text"
            value={search}
            onChange={(e) => onSearchChange(e.target.value)}
            placeholder="Search by keyword, location, or atmosphere..."
            className="w-full pl-9 pr-8 py-2 bg-white/60 border border-[#E6E2DD] rounded-none text-sm text-[#1A1A1A] placeholder-[#6E6B65] focus:outline-none focus:border-[#1C2A21] transition-colors"
          />
          {search && (
            <button
              onClick={() => onSearchChange('')}
              className="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6E6B65] hover:text-[#1A1A1A]"
              aria-label="Clear search"
            >
              <X className="w-4 h-4" />
            </button>
          )}
        </div>

        {/* Sort Controls & Count */}
        <div className="flex items-center gap-4 self-end md:self-auto">
          <span className="text-xs uppercase tracking-wider text-[#6E6B65] hidden sm:inline">
            Showing {filteredCount} of {totalCount} Works
          </span>

          <div className="flex items-center gap-2">
            <SlidersHorizontal className="w-4 h-4 text-[#1C2A21]" />
            <label htmlFor="sort-select" className="text-xs uppercase tracking-wider text-[#1C2A21] font-medium">
              Sort:
            </label>
            <select
              id="sort-select"
              value={sort}
              onChange={(e) => onSortChange(e.target.value)}
              className="py-1.5 px-3 bg-white/60 border border-[#E6E2DD] text-xs uppercase tracking-wider text-[#1A1A1A] focus:outline-none focus:border-[#1C2A21] transition-colors"
            >
              <option value="newest">Newest</option>
              <option value="oldest">Oldest</option>
              <option value="area_desc">Largest Area Size in m²</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  );
}
```

---

### Blueprint 5: Modal Lightbox Specification

```jsx
import React, { useEffect } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { X, ChevronLeft, ChevronRight } from 'lucide-react';

export default function Modal({
  isOpen,
  onClose,
  images = [],
  currentIndex = 0,
  onNavigate,
}) {
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (!isOpen) return;
      if (e.key === 'Escape') onClose();
      if (e.key === 'ArrowLeft' && onNavigate && images.length > 1) {
        onNavigate((currentIndex - 1 + images.length) % images.length);
      }
      if (e.key === 'ArrowRight' && onNavigate && images.length > 1) {
        onNavigate((currentIndex + 1) % images.length);
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
  }, [isOpen, currentIndex, images.length, onClose, onNavigate]);

  if (!isOpen || images.length === 0) return null;

  const currentImage = images[currentIndex];

  return (
    <AnimatePresence>
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        className="fixed inset-0 z-50 flex items-center justify-center bg-[#1A1A1A]/95 backdrop-blur-md p-4 sm:p-8"
        onClick={onClose}
      >
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-6 right-6 p-2 text-[#F9F8F6] hover:text-[#E6E2DD] transition-colors z-50"
          aria-label="Close Lightbox"
        >
          <X className="w-6 h-6" />
        </button>

        {/* Previous Button */}
        {images.length > 1 && (
          <button
            onClick={(e) => {
              e.stopPropagation();
              onNavigate((currentIndex - 1 + images.length) % images.length);
            }}
            className="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 p-3 text-[#F9F8F6]/80 hover:text-[#F9F8F6] hover:bg-white/10 rounded-full transition-colors z-50"
            aria-label="Previous Image"
          >
            <ChevronLeft className="w-8 h-8" />
          </button>
        )}

        {/* Image Content Container */}
        <div
          className="relative max-w-6xl max-h-[85vh] flex flex-col items-center justify-center"
          onClick={(e) => e.stopPropagation()}
        >
          <motion.img
            key={currentImage.image_path}
            initial={{ opacity: 0, scale: 0.98 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.3 }}
            src={currentImage.image_path}
            alt={currentImage.caption || 'Project Gallery Image'}
            className="max-h-[75vh] w-auto object-contain shadow-2xl border border-white/10"
          />

          {/* Caption and Counter */}
          <div className="mt-4 flex flex-col sm:flex-row items-center justify-between w-full text-xs text-[#E6E2DD] tracking-wider uppercase px-2 gap-2">
            <span className="text-[#F9F8F6] font-light italic normal-case text-sm">
              {currentImage.caption || 'Studio Architectural Detail'}
            </span>
            <span className="font-mono text-stone-300">
              {String(currentIndex + 1).padStart(2, '0')} / {String(images.length).padStart(2, '0')}
            </span>
          </div>
        </div>

        {/* Next Button */}
        {images.length > 1 && (
          <button
            onClick={(e) => {
              e.stopPropagation();
              onNavigate((currentIndex + 1) % images.length);
            }}
            className="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 p-3 text-[#F9F8F6]/80 hover:text-[#F9F8F6] hover:bg-white/10 rounded-full transition-colors z-50"
            aria-label="Next Image"
          >
            <ChevronRight className="w-8 h-8" />
          </button>
        )}
      </motion.div>
    </AnimatePresence>
  );
}
```

---

### Blueprint 6: Automated Test Suite Specifications

#### Test 1: `tests/Feature/RouteTest.php`
```php
namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_200_and_renders_inertia(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Home/Index')->has('featuredProjects'));
    }

    public function test_projects_index_returns_200_and_renders_inertia(): void
    {
        $response = $this->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Projects/Index')->has('projects'));
    }

    public function test_projects_show_returns_200_with_project_data(): void
    {
        $project = Project::factory()->create([
            'slug' => 'test-garden-sanctuary',
            'title' => 'Test Garden Sanctuary',
        ]);

        $response = $this->get(route('projects.show', $project->slug));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Projects/Show')->has('project'));
    }

    public function test_projects_show_returns_404_for_invalid_slug(): void
    {
        $response = $this->get('/projects/non-existent-garden');
        $response->assertStatus(404);
    }

    public function test_studio_page_returns_200(): void
    {
        $response = $this->get(route('studio'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Studio/Index'));
    }

    public function test_services_page_returns_200(): void
    {
        $response = $this->get(route('services'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Services/Index'));
    }

    public function test_contact_page_returns_200(): void
    {
        $response = $this->get(route('contact'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Contact/Index'));
    }
}
```

#### Test 2: `tests/Feature/InquiryTest.php`
```php
namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inquiry_can_be_submitted_and_persisted(): void
    {
        $payload = [
            'name' => 'Julian Finch',
            'email' => 'julian@example.com',
            'phone' => '+41 22 555 0192',
            'site_location' => 'Geneva, Switzerland',
            'estimated_area' => '500-1000 m²',
            'budget_range' => '$100k - $250k',
            'message' => 'We are seeking an organic landscape masterplan for our terraced lakeside residence.',
        ];

        $response = $this->post(route('inquiries.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Julian Finch',
            'email' => 'julian@example.com',
            'status' => 'new',
        ]);
    }

    public function test_inquiry_requires_mandatory_fields(): void
    {
        $response = $this->post(route('inquiries.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertDatabaseCount('inquiries', 0);
    }
}
```

#### Test 3: `tests/Feature/SeederTest.php`
```php
namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_six_garden_projects(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('projects', 6);
        $this->assertTrue(ProjectImage::count() >= 18);
        $this->assertTrue(Project::where('is_featured', true)->count() >= 3);

        $project = Project::first();
        $this->assertNotEmpty($project->title);
        $this->assertNotEmpty($project->slug);
        $this->assertNotEmpty($project->area_size);
        $this->assertNotEmpty($project->cover_image);
        $this->assertTrue($project->images()->count() > 0);
    }
}
```
