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
     * Category constants
     */
    public const CATEGORY_DESIGN = 'Project Design';
    public const CATEGORY_CONSTRUCTION = 'Project Construction';
    public const CATEGORIES = [
        self::CATEGORY_DESIGN,
        self::CATEGORY_CONSTRUCTION,
    ];

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
        'category',
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
     * Scope a query to filter projects by category (supports normalized slugs).
     */
    public function scopeCategory(Builder $query, mixed $category = null): Builder
    {
        if (!is_string($category) || empty($category) || strtolower($category) === 'all') {
            return $query;
        }

        $normalized = strtolower(trim($category));
        if ($normalized === '' || $normalized === 'all') {
            return $query;
        }

        if (in_array($normalized, ['design', 'project design', 'project-design'])) {
            return $query->where('category', self::CATEGORY_DESIGN);
        }

        if (in_array($normalized, ['construction', 'project construction', 'project-construction'])) {
            return $query->where('category', self::CATEGORY_CONSTRUCTION);
        }

        return $query->where('category', $category);
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
