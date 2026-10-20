<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PropertySale
{
    public function __construct(
        public string $transactionId,
        public int $price,
        public string $transferDate,
        public ?string $postcode,
        public string $propertyType,
        public bool $newBuild,
        public string $tenure,
        public string $town,
        public string $district,
        public string $priceCategory,
    ) {}
}