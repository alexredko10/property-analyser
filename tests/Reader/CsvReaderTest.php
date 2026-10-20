<?php

declare(strict_types=1);

namespace Tests\Reader;

use App\Reader\CsvReader;
use PHPUnit\Framework\TestCase;

final class CsvReaderTest extends TestCase
{
    public function testReadsCsvRecords(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'csv_');

        try {
            file_put_contents(
                $file,
                "\"id\",\"price\"\n\"1\",\"250000\"\n"
            );

            $reader = new CsvReader();

            $rows = iterator_to_array(
                $reader->read($file)
            );

            self::assertCount(2, $rows);
            self::assertSame(
                ['1', '250000'],
                $rows[1]
            );
        } finally {
            if ($file !== false && file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testThrowsForMissingFile(): void
    {
        $reader = new CsvReader();

        $this->expectException(
            \RuntimeException::class
        );

        iterator_to_array(
            $reader->read('/nonexistent/file.csv')
        );
    }
}
