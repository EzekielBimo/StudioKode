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
        $projectIndexes = collect(Schema::getIndexes('projects'))
            ->pluck('name')
            ->map(fn ($n) => strtolower($n))
            ->all();

        Schema::table('projects', function (Blueprint $table) use ($projectIndexes) {
            // Clean up legacy sub-optimal index names if present
            if (in_array('projects_published_year_id_index', $projectIndexes)) {
                $table->dropIndex('projects_published_year_id_index');
            }
            if (in_array('projects_featured_published_year_index', $projectIndexes)) {
                $table->dropIndex('projects_featured_published_year_index');
            }

            // Composite index for HomeController: ORDER BY is_featured DESC, year DESC, id DESC
            if (!in_array('projects_featured_year_id_index', $projectIndexes)) {
                $table->index(['is_featured', 'year', 'id'], 'projects_featured_year_id_index');
            }

            // Composite index for ProjectController: ORDER BY year DESC, id DESC
            if (!in_array('projects_year_id_index', $projectIndexes)) {
                $table->index(['year', 'id'], 'projects_year_id_index');
            }
        });

        $imageIndexes = collect(Schema::getIndexes('project_images'))
            ->pluck('name')
            ->map(fn ($n) => strtolower($n))
            ->all();

        Schema::table('project_images', function (Blueprint $table) use ($imageIndexes) {
            // Composite index for ProjectController::show gallery images: WHERE project_id = ? ORDER BY sort_order ASC
            if (!in_array('project_images_project_sort_index', $imageIndexes)) {
                $table->index(['project_id', 'sort_order'], 'project_images_project_sort_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $projectIndexes = collect(Schema::getIndexes('projects'))
            ->pluck('name')
            ->map(fn ($n) => strtolower($n))
            ->all();

        Schema::table('projects', function (Blueprint $table) use ($projectIndexes) {
            if (in_array('projects_featured_year_id_index', $projectIndexes)) {
                $table->dropIndex('projects_featured_year_id_index');
            }
            if (in_array('projects_year_id_index', $projectIndexes)) {
                $table->dropIndex('projects_year_id_index');
            }
            if (in_array('projects_published_year_id_index', $projectIndexes)) {
                $table->dropIndex('projects_published_year_id_index');
            }
            if (in_array('projects_featured_published_year_index', $projectIndexes)) {
                $table->dropIndex('projects_featured_published_year_index');
            }
        });

        $imageIndexes = collect(Schema::getIndexes('project_images'))
            ->pluck('name')
            ->map(fn ($n) => strtolower($n))
            ->all();

        Schema::table('project_images', function (Blueprint $table) use ($imageIndexes) {
            // 1. MySQL InnoDB requires an index supporting the foreign key constraint.
            // When the composite index was created, MySQL silently dropped the internal auto-generated FK index.
            // We must restore the standalone FK index BEFORE dropping the composite index to prevent MySQL Error 1553.
            if (!in_array('project_images_project_id_foreign', $imageIndexes)) {
                $table->index('project_id', 'project_images_project_id_foreign');
            }

            // 2. Now that the FK constraint is backed by the restored FK index,
            // we can safely drop the composite performance index without triggering MySQL Error 1553.
            if (in_array('project_images_project_sort_index', $imageIndexes)) {
                $table->dropIndex('project_images_project_sort_index');
            }
        });
    }
};
