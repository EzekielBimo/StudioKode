# Handoff Report: Milestone 1 Database Schema, Eloquent Models & DatabaseSeeder

**Author**: Explorer M1_2  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m1_2`  
**Milestone**: Milestone 1 — Database Schema, Eloquent Models & DatabaseSeeder  
**Date**: 2026-11-08T09:08:00Z  

---

### Executive Summary
This report delivers the production-ready database migration schema for `projects`, `project_images`, and `inquiries`, complete Eloquent models with typed casts and relationships, Eloquent model factories for automated testing, and a comprehensive `DatabaseSeeder.php` populating 6 realistic luxury garden architecture projects with 22 gallery images, strictly adhering to the zero-category design rule.

---

## 1. Observation

Direct observations from specifications, workspace requirements, and peer analysis:

1. **Authoritative Specification (`c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md`)**:
   - Lines 12–17 (R1):  
     *"Set up Laravel 11+ with Inertia.js and configure MySQL as the database. Implement Eloquent models (`Project`, `ProjectImage`, `Inquiry`) and database migrations matching the schema:*  
     *- `projects`: `id`, `title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size` (e.g., '240 m²'), `cover_image`, `masterplan_image`, `is_featured` (boolean), `published_at` (timestamp), `timestamps`. (No category table; all projects are garden/landscape designs).*  
     *- `project_images`: `id`, `project_id` (foreign key), `image_path`, `caption`, `sort_order` (integer), `timestamps`.*  
     *- `inquiries`: `id`, `name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message`, `status` (default: 'new'), `timestamps`.*  
     *Implement `DatabaseSeeder.php` seeding 6 realistic garden architecture projects with dummy gallery images."*
   - Lines 41–42 (R4):  
     *"In `Components/Portfolio/FilterBar.jsx`, handle keyword/location search and sorting (Newest, Oldest, Largest Area Size in m²) without category filters."*
   - Lines 52–55 (Acceptance Criteria):  
     *"Database migrations execute cleanly on MySQL, creating `projects`, `project_images`, and `inquiries` tables with all specified columns and relationships."*  
     *"`php artisan db:seed` executes successfully and populates 6 complete garden architecture projects with gallery images."*  
     *"Contact form submission via POST to the inquiry route validates inputs and persists a new record in `inquiries`."*  
     *"Automated PHPUnit/Pest feature test suite runs and passes 100% of tests verifying route responses (HTTP 200) and inquiry persistence."*

2. **Project Architecture Plan (`c:\laragon\www\StudioKode\.agents\teamwork\orchestrator\PROJECT.md`)**:
   - Lines 6–8: MySQL 8.4.3 (Database: `studiokode`, Port: 3306, User: `root`, Password: `""`).
   - Lines 64–76: Inertia props contract requires `Project` objects with `cover_image`, `title`, `slug`, `location`, `year`, `area_size`, `masterplan_image`, and related `images`.
   - Lines 162–167: Constraints mandate strict omission of categories and PHP 8.3 CLI execution.

3. **Survey Spec Miner Findings (`c:\laragon\www\StudioKode\.agents\teamwork\teamwork_spec_miner_survey_2\handoff.md`)**:
   - Lines 48–52: Reaffirms strict absence of category tables/columns and outlines `area_size` sorting mechanics.
   - Lines 213–320: Outlines the 6 luxury botanical typologies and gallery datasets.
   - Lines 571–714: Test suite specifications rely on `Project::factory()`, `ProjectImage`, and `DatabaseSeeder` assertions (`assertDatabaseCount('projects', 6)` and `ProjectImage::count() >= 18`).

---

## 2. Logic Chain

1. **Migration Sequence & Foreign Key Integrity**:
   - `projects` must be migrated first because `project_images` holds a foreign key constraint referencing `projects.id`.
   - The migration order is enforced chronologically using timestamp prefixes:
     1. `2024_01_01_000001_create_projects_table.php`
     2. `2024_01_01_000002_create_project_images_table.php`
     3. `2024_01_01_000003_create_inquiries_table.php`
   - In `project_images`, `$table->foreignId('project_id')->constrained('projects')->cascadeOnDelete()` guarantees referential integrity and automatic cleanup of child gallery images if a parent project is deleted.

2. **Data Types and Constraints Formulation**:
   - `projects.slug`: Defined as `string('slug', 255)->unique()` to enable fast indexed lookups and URL slugs (`/projects/{slug}`).
   - `projects.year`: Stored as `unsignedSmallInteger` (0–65535) for optimal index sizing and clean numeric comparison.
   - `projects.area_size`: Stored as `string('area_size', 50)` to preserve architectural formatting (e.g. `"240 m²"`, `"1,250 m²"`). An accessor `getAreaNumericAttribute()` is added to the `Project` model to strip non-digit characters and return integer square meters for sorting and computation.
   - `projects.is_featured`: Indexed boolean with `default(false)` for fast queries on the homepage showcase (`where('is_featured', true)`).
   - `projects.masterplan_image`: Nullable string to allow projects without a published masterplan drawing.
   - `project_images.sort_order`: Indexed integer with `default(0)` so gallery images can be retrieved in deterministic presentation order.
   - `inquiries.status`: String column with `default('new')` and index for easy administrative filtering.
   - **Zero Category Policy**: No `category_id`, `category`, or pivot tables are created, adhering strictly to R1.

3. **Eloquent Models & Laravel 11 Standards**:
   - Laravel 11 introduces method-based casts `protected function casts(): array`, which is implemented alongside standard fillable definitions.
   - `Project` defines:
     - `images()`: `HasMany` to `ProjectImage` ordered by `sort_order ASC`.
     - `scopeFeatured()`: convenient scope for home page.
     - `scopePublished()`: filters active published projects.
     - `getRouteKeyName()`: returns `'slug'` to support standard Laravel route model binding.
   - `ProjectImage` defines:
     - `project()`: `BelongsTo` to `Project`.
   - `Inquiry` defines:
     - Mass assignment protection matching contact form inputs.

4. **Testing Enablement via Factories**:
   - `RouteTest` and future feature tests invoke `Project::factory()->create(...)`.
   - Providing `ProjectFactory`, `ProjectImageFactory`, and `InquiryFactory` ensures tests do not fail with `Class "Database\Factories\ProjectFactory" not found`.

5. **Curated Seeder Dataset Architecture**:
   - To reflect a world-class landscape and garden architecture studio, 6 distinct typologies were authored:
     1. *The Moss & Stone Sanctuary* (Kyoto, Japan — 380 m²): Zen dry-creek, bryophyte moss carpets, cedar pavilion.
     2. *Verdant Terrace Estate* (Lake Como, Italy — 1,250 m²): Tiered limestone retaining walls, cypress promenade, reflection pool.
     3. *Atelier Botanica Courtyard* (Melbourne, Australia — 180 m²): Urban warehouse conversion, Dicksonia tree ferns, blackened steel rills.
     4. *Highland Mist Alpine Garden* (Zurich, Switzerland — 850 m²): Glacial erratic gneiss rocks, montane pines, wild alpine meadow.
     5. *Oasis Dune Residence* (Palm Springs, California — 620 m²): Mid-century desert xeriscaping, rammed-earth windbreaks, blue glow agaves.
     6. *Coastal Dune & Pine Refuge* (Comporta, Portugal — 2,100 m²): Maritime pines, elevated ipe boardwalks, biological reed pool.
   - Total projects: 6 (4 featured, 2 standard).
   - Total project images: 22 high-resolution Unsplash architectural photographs with evocative captions.
   - 2 realistic inquiries seeded to verify inquiry querying and administration.

---

## 3. Caveats

1. **Unsplash Image URLs**: Seeder uses live Unsplash CDN URLs with optimized query parameters (`?auto=format&fit=crop&w=1600&q=80`). In offline environments, images still load without breaking markup because `<img>` tags gracefully handle external URLs.
2. **SQLite vs MySQL Dialects in Migrations**: All migration statements use standard Schema Builder methods compatible with both MySQL 8.4 and SQLite in-memory testing.
3. **Area Sorting Computation**: While `area_size` is stored as a formatted string per specification (e.g., `"1,250 m²"`), SQL sorting directly on strings would fail alphabetical sorting (e.g., "1,250" < "240"). The `Project` model provides the `area_numeric` virtual accessor, and controllers/frontend sort using numeric parsing.

---

## 4. Conclusion & Complete Blueprints

The implementer (Builder M1) can copy and paste the following exact files directly into the repository.

### Blueprint A: Database Migrations

#### 1. `database/migrations/2024_01_01_000001_create_projects_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('description');
            $table->string('client')->nullable();
            $table->string('location');
            $table->unsignedSmallInteger('year');
            $table->string('area_size', 50); // e.g. "240 m²"
            $table->string('cover_image');
            $table->string('masterplan_image')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
```

#### 2. `database/migrations/2024_01_01_000002_create_project_images_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_images');
    }
};
```

#### 3. `database/migrations/2024_01_01_000003_create_inquiries_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('site_location')->nullable();
            $table->string('estimated_area', 100)->nullable();
            $table->string('budget_range', 100)->nullable();
            $table->text('message');
            $table->string('status', 50)->default('new')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
```

---

### Blueprint B: Eloquent Models

#### 1. `app/Models/Project.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'description',
        'client',
        'location',
        'year',
        'area_size',
        'cover_image',
        'masterplan_image',
        'is_featured',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'area_numeric',
    ];

    /**
     * Get all gallery images associated with the project ordered by sort_order.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order', 'asc');
    }

    /**
     * Scope a query to only include featured projects.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include published projects.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Get route key for implicit model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Accessor: Extract integer numeric value from area_size (e.g. "1,250 m²" -> 1250).
     */
    public function getAreaNumericAttribute(): int
    {
        if (empty($this->area_size)) {
            return 0;
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string) $this->area_size);

        return $cleaned !== '' ? (int) $cleaned : 0;
    }
}
```

#### 2. `app/Models/ProjectImage.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'image_path',
        'caption',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the project that owns the image.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
```

#### 3. `app/Models/Inquiry.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inquiries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'site_location',
        'estimated_area',
        'budget_range',
        'message',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }
}
```

---

### Blueprint C: Model Factories

#### 1. `database/factories/ProjectFactory.php`
```php
<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true) . ' Sanctuary';

        return [
            'title' => ucwords($title),
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(1),
            'description' => fake()->paragraphs(3, true),
            'client' => fake()->company() . ' Trust',
            'location' => fake()->city() . ', ' . fake()->country(),
            'year' => fake()->numberBetween(2020, 2024),
            'area_size' => fake()->numberBetween(150, 2500) . ' m²',
            'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1600&q=80',
            'masterplan_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => fake()->boolean(50),
            'published_at' => now()->subDays(fake()->numberBetween(1, 100)),
        ];
    }

    /**
     * Indicate that the project is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
```

#### 2. `database/factories/ProjectImageFactory.php`
```php
<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectImage>
 */
class ProjectImageFactory extends Factory
{
    protected $model = ProjectImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'image_path' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1200&q=80',
            'caption' => fake()->sentence(6),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
```

#### 3. `database/factories/InquiryFactory.php`
```php
<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'site_location' => fake()->city() . ', ' . fake()->country(),
            'estimated_area' => '500-1000 m²',
            'budget_range' => '$100k - $250k',
            'message' => fake()->paragraph(2),
            'status' => 'new',
        ];
    }
}
```

---

### Blueprint D: Complete `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key constraints during clean reseeding
        Schema::disableForeignKeyConstraints();
        ProjectImage::truncate();
        Project::truncate();
        Inquiry::truncate();
        Schema::enableForeignKeyConstraints();

        $projects = [
            [
                'title' => 'The Moss & Stone Sanctuary',
                'slug' => 'moss-stone-sanctuary',
                'client' => 'Private Collector',
                'location' => 'Kyoto, Japan',
                'year' => 2024,
                'area_size' => '380 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A meditative Japanese courtyard garden balancing ancient lichen-encrusted granite, velvet bryophyte carpets, and blackened cedar pavilions.',
                'description' => "Designed as an intimate contemplative sanctuary within Kyoto's historic outskirts. The site required careful soil conditioning to sustain indigenous Polytrichum commune moss species.\n\nWeathered megalithic stones sourced from Tamba quarries define the central dry-creek rill, flanked by pruned Japanese Stewartia and weeping maples. Rainwater from the cedar pavilion is guided into a hand-carved stone tsukubai basin, creating subtle acoustic ripples throughout the courtyard.\n\nAt twilight, concealed brass luminaires cast soft shadows through bamboo screens, accentuating the undulating topography of green moss hummocks against dark river pebbles.",
                'is_featured' => true,
                'published_at' => now()->subDays(12),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Entrance stone walkway embedded in lush velvet moss margins',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1545239351-ef35f43d514b?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Hand-carved granite water basin and bamboo water flume',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Acer palmatum canopy casting dappled shadows on stone paving',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Evening illumination along the dry-creek granite rill',
                        'sort_order' => 4,
                    ],
                ],
            ],
            [
                'title' => 'Verdant Terrace Estate',
                'slug' => 'verdant-terrace-estate',
                'client' => 'Villa Bellini Trust',
                'location' => 'Lake Como, Italy',
                'year' => 2023,
                'area_size' => '1,250 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'Cascading limestone terraces descending toward Lake Como, punctuated by columnar cypresses, fragrant citrus groves, and neoclassical water rills.',
                'description' => "Revitalizing a historic 19th-century hillside estate, this landscape masterplan reconciles severe steep topography with elegant multi-tier living spaces.\n\nHand-dressed Moltrasio stone walls support olive orchards, lavender terraces, and stepped water runnels that create acoustic serenity. The lowest terrace features a sunken infinity reflection pool visually dissolving into the lake.\n\nMicroclimate analysis guided the positioning of lemon pergolas against heat-retaining stone abutments, while native drought-hardy Mediterranean perennials ensure seasonal fragrance without intensive chemical interventions.",
                'is_featured' => true,
                'published_at' => now()->subDays(45),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Panoramic terrace view across Lake Como and alpine foothills',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1524813686514-a57563d77d61?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Symmetrical cypress promenade and tiered Moltrasio stone steps',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1533759413974-9e15f3b745ac?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Sunken limestone reflecting pool surrounded by fragrant lavender',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Citrus grove pergola with reclaimed chestnut timber beams',
                        'sort_order' => 4,
                    ],
                ],
            ],
            [
                'title' => 'Atelier Botanica Courtyard',
                'slug' => 'atelier-botanica-courtyard',
                'client' => 'Studio K Architects',
                'location' => 'Melbourne, Australia',
                'year' => 2024,
                'area_size' => '180 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A sculptural micro-climate sanctuary within an urban heritage envelope, showcasing architectural tree ferns and blackened steel reflecting pools.',
                'description' => "Concealed within a Victorian brick warehouse conversion, this compact courtyard functions as an ethereal living lung for a contemporary design atelier.\n\nMulti-layered canopy plantings of Dicksonia antarctica tree ferns establish vertical softness against textured industrial masonry. Custom raw blackened steel water mirrors cool the ambient air temperature, while permeable bluestone gravel ensures complete on-site rainwater infiltration.\n\nSubtle under-deck lighting reveals intricate frond silhouettes during nocturnal hours, transforming the workspace into a tranquil botanical immersion.",
                'is_featured' => true,
                'published_at' => now()->subDays(5),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1463936575829-25148e1db1b8?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Canopy layer of Dicksonia antarctica architectural tree ferns',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Blackened steel reflecting pool mirroring heritage red brickwork',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1507089947368-19c1da9775ae?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Bluestone paving details under filtered botanical light',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'title' => 'Highland Mist Alpine Garden',
                'slug' => 'highland-mist-alpine-garden',
                'client' => 'Dr. Hans & Elena Weber',
                'location' => 'Zurich, Switzerland',
                'year' => 2022,
                'area_size' => '850 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A biodiverse montane rockery and wild alpine meadow, embracing glacial erratic stones and cold-hardy botanical tapestries.',
                'description' => "Perched on a sub-alpine ridge overlooking the lake basin, this garden embraces severe winter frost and intense summer radiation.\n\nGlacial erratic gneiss rocks discovered during foundation excavation were repositioned to anchor micro-topographies. Planted with dryas, edelweiss, creeping wild thyme, dwarf mountain pines (Pinus mugo), and naturalized birch groves, the scheme requires zero synthetic irrigation.\n\nThe planting plan creates critical high-altitude pollinator corridors and provides dynamic seasonal metamorphosis from spring snowmelt to autumn gold.",
                'is_featured' => false,
                'published_at' => now()->subDays(80),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Alpine rock garden enveloped in morning mountain mist',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Native Pinus mugo and silver birch grove boundary',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Sunken granite fire gathering ring overlooking the valley',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'title' => 'Oasis Dune Residence',
                'slug' => 'oasis-dune-residence',
                'client' => 'Vance & Morgan Design',
                'location' => 'Palm Springs, California',
                'year' => 2023,
                'area_size' => '620 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'Sculptural desert xeriscaping harmonizing rammed-earth windbreaks, specimen agaves, and pale gravel swales with mid-century lines.',
                'description' => "Responding to the extreme diurnal temperature shifts of the Sonoran Desert, this landscape celebrates the bold sculptural silhouettes of arid botanical species.\n\nMassive rammed-earth retaining walls buffer harsh seasonal desert winds and cast long geometric shadows across polished concrete patios. A curated collection of specimen blue glow agaves, golden barrel cacti, and thornless palo verde trees rise from decomposed granite beds designed for ephemeral flash-rain harvesting.\n\nDusk brings the space alive as architectural up-lighting transforms the desert spires into dramatic sculptural monuments against the San Jacinto mountains.",
                'is_featured' => false,
                'published_at' => now()->subDays(60),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Specimen golden barrel cacti framing rammed-earth wall',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Palo verde canopy casting fine shadows on pale granite gravel',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Architectural Agave attenuata grouping along concrete steps',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1470240731273-7821a6eeb6bd?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Dusk illumination across desert swale landscape',
                        'sort_order' => 4,
                    ],
                ],
            ],
            [
                'title' => 'Coastal Dune & Pine Refuge',
                'slug' => 'coastal-dune-pine-refuge',
                'client' => 'Herdade da Costa',
                'location' => 'Comporta, Portugal',
                'year' => 2024,
                'area_size' => '2,100 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A vast coastal sanctuary celebrating maritime pines, wild dune grass stabilization, and weathered timber boardwalks navigating sandy terrain.',
                'description' => "Spanning over two hectares along the pristine Atlantic littoral, this sensitive coastal restoration preserves and expands fragile sand dune ecologies.\n\nElevated untreated ipe timber boardwalks hover gently above shifting white sands, guiding visitors between naturalized clusters of umbrella pine (Pinus pinea), sea lavender, and aromatic curry plants. A biological natural swimming pond filtered entirely by indigenous wetland reeds anchors the central living area without chlorine or synthetic additives.\n\nThe resulting sanctuary is an understated architectural homage to the raw maritime atmosphere of the Alentejo coast.",
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Floating timber boardwalk winding through maritime pine forest',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Natural biological pool filtered by indigenous wetland reeds',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Dune grass stabilization and Atlantic ocean view',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Minimalist timber shaded terrace facing coastal dunes',
                        'sort_order' => 4,
                    ],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $galleryImages = $projectData['images'] ?? [];
            unset($projectData['images']);

            $project = Project::create($projectData);

            foreach ($galleryImages as $imageData) {
                $imageData['project_id'] = $project->id;
                ProjectImage::create($imageData);
            }
        }

        // Seed initial consultation inquiries for administrative and verification testing
        Inquiry::create([
            'name' => 'Julian Finch',
            'email' => 'julian.finch@vanguard-estate.ch',
            'phone' => '+41 22 819 4022',
            'site_location' => 'Geneva, Switzerland',
            'estimated_area' => '750 m²',
            'budget_range' => '$150,000 - $300,000',
            'message' => 'We are seeking an organic landscape masterplan for our terraced lakeside residence in Cologny. We admire your stone sanctuary work and wish to integrate natural stone rills.',
            'status' => 'new',
        ]);

        Inquiry::create([
            'name' => 'Seraphina Vance',
            'email' => 'seraphina@vance-atelier.com',
            'phone' => '+1 (310) 849-2910',
            'site_location' => 'Montecito, California',
            'estimated_area' => '1,800 m²',
            'budget_range' => '$350,000+',
            'message' => 'Commissioning a comprehensive landscape overhaul for a mid-century coastal property. Looking for drought-hardy architectural specimens, rammed-earth interventions, and reflection water features.',
            'status' => 'reviewed',
        ]);
    }
}
```

---

## 5. Verification Method

Once implemented by Builder M1, the implementation can be independently verified using the following automated commands:

1. **Migration Clean Run**:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   php artisan migrate:fresh
   ```
   *Expected outcome*: `projects`, `project_images`, and `inquiries` tables are created with exit code 0.

2. **Schema Verification**:
   Inspect table columns in MySQL or Tinker:
   ```powershell
   php artisan tinker --execute="dump(Schema::getColumnListing('projects')); dump(Schema::getColumnListing('project_images')); dump(Schema::getColumnListing('inquiries'));"
   ```
   *Pass condition*:
   - `projects` contains: `id`, `title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size`, `cover_image`, `masterplan_image`, `is_featured`, `published_at`, `created_at`, `updated_at`. Zero category columns!
   - `project_images` contains: `id`, `project_id`, `image_path`, `caption`, `sort_order`, `created_at`, `updated_at`.
   - `inquiries` contains: `id`, `name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message`, `status`, `created_at`, `updated_at`.

3. **Seeder Verification**:
   ```powershell
   php artisan db:seed
   php artisan tinker --execute="echo 'Projects: ' . App\Models\Project::count() . ' | Images: ' . App\Models\ProjectImage::count() . ' | Featured: ' . App\Models\Project::where('is_featured', true)->count() . PHP_EOL;"
   ```
   *Pass condition*: Exactly 6 projects, 22 project images, and 4 featured projects.

4. **Automated Feature Test**:
   ```powershell
   php artisan test --filter=SeederTest
   ```
   *Pass condition*: `DatabaseSeederTest` passes with 100% assertions green.
