<?php

declare(strict_types=1);

namespace App\Processor;

use PHPUnit\Framework\TestCase;

final class PropertyTransformerTest extends TestCase
{

    private PropertyTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new PropertyTransformer(
            new PropertyValidator()
        );
    }

    public function testTransformsValidRecord(): void
    {
        $row = [
            '{ABC-123}', '322000',
            '2021-07-30 00:00', 'BS5 7LS',
            'T', 'N', 'F', '10', '',
            'CLOVELLY ROAD', '', 'BRISTOL',
            'CITY OF BRISTOL', 'CITY OF BRISTOL',
            'A', 'A',
        ];

        $sale = $this->transformer->transform($row);

        self::assertSame(322000, $sale->price);
        self::assertSame('Terraced', $sale->propertyType);
        self::assertSame('MEDIUM', $sale->priceCategory);
        self::assertFalse($sale->newBuild);
    }

    public function testRejectsInvalidPrice(): void
    {
        $row = array_fill(0, 16, '');
        $row[0] = '{ABC-123}';
        $row[1] = '-100';
        $row[2] = '2021-07-30';
        $row[4] = 'T';
        $row[5] = 'N';

        $this->expectException(
            \InvalidArgumentException::class
        );

        $this->transformer->transform($row);
    }
}
