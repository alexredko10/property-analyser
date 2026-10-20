<?php

declare(strict_types=1);

namespace App\Contract;


use App\DTO\PropertySale;

interface PropertyWriterInterface
{
/** @param PropertySale[] $sales */
    public function writeBatch(array $sales): void;
}