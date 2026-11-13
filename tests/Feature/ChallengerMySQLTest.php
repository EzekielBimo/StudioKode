<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChallengerMySQLTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => 'studiokode',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
    }

    /**
     * Challenger M1: Verify MySQL connection and version
     */
    public function test_mysql_connection_and_engine(): void
    {
        $version = DB::connection('mysql')->select('SELECT VERSION() as v')[0]->v;
        $this->assertNotEmpty($version);
        $this->assertStringContainsString('8.4', $version);
    }

    /**
     * Challenger M2: Verify MySQL tables and foreign keys
     */
    public function test_mysql_schema_tables_and_foreign_keys(): void
    {
        $tables = DB::connection('mysql')->select("
            SELECT TABLE_NAME 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = 'studiokode' 
            AND TABLE_NAME IN ('projects', 'project_images', 'inquiries', 'categories')
        ");

        $tableNames = array_map(fn ($r) => $r->TABLE_NAME, $tables);
        $this->assertContains('projects', $tableNames);
        $this->assertContains('project_images', $tableNames);
        $this->assertContains('inquiries', $tableNames);
        $this->assertNotContains('categories', $tableNames, 'Categories table must NOT exist');

        // Check foreign key constraint and CASCADE rule
        $fk = DB::connection('mysql')->select("
            SELECT rc.CONSTRAINT_NAME, rc.DELETE_RULE, kcu.REFERENCED_TABLE_NAME
            FROM information_schema.REFERENTIAL_CONSTRAINTS rc
            JOIN information_schema.KEY_COLUMN_USAGE kcu 
                ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
            WHERE rc.CONSTRAINT_SCHEMA = 'studiokode'
            AND kcu.TABLE_NAME = 'project_images'
            AND kcu.COLUMN_NAME = 'project_id'
        ");

        $this->assertNotEmpty($fk, 'Foreign key on project_images.project_id must exist');
        $this->assertEquals('CASCADE', $fk[0]->DELETE_RULE);
        $this->assertEquals('projects', $fk[0]->REFERENCED_TABLE_NAME);
    }

    /**
     * Challenger M3: Verify MySQL seeded data counts and format
     */
    public function test_mysql_seeded_records_and_formatting(): void
    {
        $projectCount = DB::connection('mysql')->table('projects')->count();
        $this->assertEquals(6, $projectCount, 'Exact project count in MySQL must be 6');

        $imageCount = DB::connection('mysql')->table('project_images')->count();
        $this->assertGreaterThanOrEqual(18, $imageCount, 'Total gallery images must be at least 18');
        $this->assertEquals(22, $imageCount);

        // Verify area_size format for all projects
        $projects = DB::connection('mysql')->table('projects')->select('id', 'title', 'area_size')->get();
        foreach ($projects as $project) {
            $this->assertMatchesRegularExpression('/^[0-9,]+\s*m²$/', $project->area_size, "Area size for {$project->title} is invalid: {$project->area_size}");
        }
    }

    /**
     * Challenger M4: Empirical cascade delete directly on MySQL database
     */
    public function test_mysql_empirical_cascade_delete(): void
    {
        $projectId = DB::connection('mysql')->table('projects')->insertGetId([
            'title' => 'Empirical Cascade Temp Project',
            'slug' => 'empirical-cascade-temp',
            'description' => 'Testing MySQL foreign key cascade delete empirically.',
            'location' => 'Empirical Lab',
            'year' => 2024,
            'area_size' => '999 m²',
            'cover_image' => 'https://example.com/cover.jpg',
            'is_featured' => 0,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $img1 = DB::connection('mysql')->table('project_images')->insertGetId([
            'project_id' => $projectId,
            'image_path' => 'https://example.com/cascade1.jpg',
            'caption' => 'Cascade image 1',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $img2 = DB::connection('mysql')->table('project_images')->insertGetId([
            'project_id' => $projectId,
            'image_path' => 'https://example.com/cascade2.jpg',
            'caption' => 'Cascade image 2',
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify images exist in MySQL
        $countBefore = DB::connection('mysql')->table('project_images')->where('project_id', $projectId)->count();
        $this->assertEquals(2, $countBefore);

        // Delete parent project in MySQL
        DB::connection('mysql')->table('projects')->where('id', $projectId)->delete();

        // Verify project is deleted
        $projectExists = DB::connection('mysql')->table('projects')->where('id', $projectId)->exists();
        $this->assertFalse($projectExists);

        // Verify child images were automatically deleted by MySQL CASCADE
        $countAfter = DB::connection('mysql')->table('project_images')->where('project_id', $projectId)->count();
        $this->assertEquals(0, $countAfter, 'Child project_images were NOT cascaded by MySQL!');
    }

    /**
     * Challenger M5: Area sort expression directly on MySQL database
     */
    public function test_mysql_area_descending_sort_query(): void
    {
        $ordered = DB::connection('mysql')->select("
            SELECT slug, area_size, (REPLACE(REPLACE(REPLACE(area_size, ' m²', ''), ',', ''), ' ', '') + 0) as parsed_area
            FROM projects
            ORDER BY (REPLACE(REPLACE(REPLACE(area_size, ' m²', ''), ',', ''), ' ', '') + 0) DESC
        ");

        $expectedSlugs = [
            'coastal-dune-pine-refuge',    // 2,100 m²
            'verdant-terrace-estate',      // 1,250 m²
            'highland-mist-alpine-garden', // 850 m²
            'oasis-dune-residence',        // 620 m²
            'moss-stone-sanctuary',        // 380 m²
            'atelier-botanica-courtyard',  // 180 m²
        ];

        $actualSlugs = array_map(fn ($r) => $r->slug, $ordered);
        $this->assertEquals($expectedSlugs, $actualSlugs);
    }
}
