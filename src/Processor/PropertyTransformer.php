<?php

declare(strict_types=1);

namespace App\Processor;

use App\DTO\PropertySale;

final readonly class PropertyTransformer
{
    public function __construct(
        private PropertyValidator $validator
    ) {}

    public function transform(array $row): PropertySale
    {
        $this->validator->validate($row);

        $price = (int) $row[1];

        return new PropertySale(
            transactionId: trim($row[0], '{}'),
            price: $price,
            transferDate: substr($row[2], 0, 10),
            postcode: trim($row[3]) ?: null,
            propertyType: match ($row[4]) {
                'D' => 'Detached',
                'S' => 'Semi-detached',
                'T' => 'Terraced',
                'F' => 'Flat',
                'O' => 'Other',
            },
            newBuild: $row[5] === 'Y',
            tenure: $row[6],
            town: trim($row[11]),
            district: trim($row[12]),
            priceCategory: match (true) {
                $price < 250_000 => 'LOW',
                $price < 500_000 => 'MEDIUM',
                $price < 1_000_000 => 'HIGH',
                default => 'LUXURY',
            },
        );
    }
}
