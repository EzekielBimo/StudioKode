<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AreaSizeParserTest extends TestCase
{
    /**
     * Helper parser mimicking SQL / Controller / Javascript numeric extraction.
     */
    private function parseArea(string $area): int
    {
        return (int) preg_replace('/[^0-9]/', '', $area);
    }

    /**
     * Tier 1: Parse standard area sizes without commas.
     */
    public function test_parses_standard_area_sizes(): void
    {
        $this->assertEquals(380, $this->parseArea('380 m²'));
        $this->assertEquals(180, $this->parseArea('180 m²'));
        $this->assertEquals(850, $this->parseArea('850 m²'));
        $this->assertEquals(620, $this->parseArea('620 m²'));
    }

    /**
     * Tier 2 Boundary: Parse large area sizes containing thousand-separator commas.
     */
    public function test_parses_comma_formatted_area_sizes(): void
    {
        $this->assertEquals(1250, $this->parseArea('1,250 m²'));
        $this->assertEquals(2100, $this->parseArea('2,100 m²'));
        $this->assertEquals(15000, $this->parseArea('15,000 m²'));
    }

    /**
     * Tier 2 Boundary: Verify numerical comparison handles commas correctly.
     * "1,250 m²" must evaluate strictly greater than "380 m²".
     */
    public function test_numeric_comparison_orders_correctly(): void
    {
        $area1 = $this->parseArea('1,250 m²');
        $area2 = $this->parseArea('380 m²');
        $area3 = $this->parseArea('2,100 m²');

        $this->assertGreaterThan($area2, $area1);
        $this->assertGreaterThan($area1, $area3);
    }

    /**
     * Tier 3 Combinatorial: Sorting an array of project areas descending.
     */
    public function test_array_sort_by_area_descending(): void
    {
        $projects = [
            ['title' => 'Project A', 'area_size' => '380 m²'],
            ['title' => 'Project B', 'area_size' => '1,250 m²'],
            ['title' => 'Project C', 'area_size' => '180 m²'],
            ['title' => 'Project D', 'area_size' => '2,100 m²'],
        ];

        usort($projects, function ($a, $b) {
            return $this->parseArea($b['area_size']) <=> $this->parseArea($a['area_size']);
        });

        $this->assertEquals('Project D', $projects[0]['title']); // 2,100 m²
        $this->assertEquals('Project B', $projects[1]['title']); // 1,250 m²
        $this->assertEquals('Project A', $projects[2]['title']); // 380 m²
        $this->assertEquals('Project C', $projects[3]['title']); // 180 m²
    }
}
