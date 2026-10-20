<?php

declare(strict_types=1);

namespace App\Processor;

use InvalidArgumentException;

final class PropertyValidator
{
    public function validate(array $row): void
    {
        if (count($row) !== 16) {
            throw new InvalidArgumentException(
                'Invalid column count'
            );
        }

        if (
            filter_var($row[1], FILTER_VALIDATE_INT) === false
            || (int) $row[1] <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid property price'
            );
        }

        if (!in_array(
            $row[4],
            ['D', 'S', 'T', 'F', 'O'],
            true
        )) {
            throw new InvalidArgumentException(
                'Invalid property type'
            );
        }

        if (!in_array($row[5], ['Y', 'N'], true)) {
            throw new InvalidArgumentException(
                'Invalid new build value'
            );
        }

        if (trim($row[0]) === '') {
            throw new InvalidArgumentException(
                'Missing transaction ID'
            );
        }

        if (trim($row[2]) === '') {
            throw new InvalidArgumentException(
                'Missing transfer date'
            );
        }
    }
}
