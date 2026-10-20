<?php

declare(strict_types=1);

namespace App\Reader;

use Generator;
use RuntimeException;

final class CsvReader
{

    public function read(string $path): Generator
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                "Cannot open CSV file: {$path}"
            );
        }

        try {
            while (($row = fgetcsv(
                    $handle,
                    0,
                    ',',
                    '"',
                    ''
                )) !== false
            ) {
                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }
}
