<?php

use App\Controllers\Tides as TidesController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TidesControllerDateScopingTest extends CIUnitTestCase
{
    public function testCompletenessAndGapFillUseUtcDayBoundaries(): void
    {
        $controller = new TidesController();
        $rows = [
            [
                'station_code'    => 'BBLN',
                'measured_at_utc' => '2026-08-20 00:00:00',
                'water_level'     => '1.234',
            ],
            [
                'station_code'    => 'BBLN',
                'measured_at_utc' => '2026-08-19 23:55:00',
                'water_level'     => '9.999',
            ],
        ];

        $assess = new ReflectionMethod($controller, 'assessCompleteness');
        $assess->setAccessible(true);
        $completeness = $assess->invoke($controller, $rows, '2026-08-20', 5);

        $fill = new ReflectionMethod($controller, 'fillMissingRows');
        $fill->setAccessible(true);
        $filledRows = $fill->invoke($controller, 'BBLN', '2026-08-20', 5, $rows);

        $this->assertSame(1, $completeness['actual_points']);
        $this->assertSame(287, $completeness['missing_points']);
        $this->assertCount(288, $filledRows);
        $this->assertCount(1, array_filter($filledRows, static fn (array $row): bool => ! $row['is_gap_fill']));
        $this->assertSame('1.234', $filledRows[0]['water_level']);
        $this->assertNotContains('9.999', array_column($filledRows, 'water_level'));

        $gapRows = array_values(array_filter($filledRows, static fn (array $row): bool => $row['is_gap_fill']));
        $gapRow  = $gapRows[0] ?? null;
        $this->assertIsArray($gapRow);
        $this->assertNull($gapRow['prs1']);
        $this->assertNull($gapRow['enc1']);
        $this->assertNull($gapRow['rad1']);
        $this->assertSame('gap_fill', $gapRow['water_level_source']);
    }
}
