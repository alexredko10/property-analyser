<?php

declare(strict_types=1);

namespace App\Contract;
interface  CheckpointWriterInterface extends PropertyWriterInterface
{

    public function getCheckpoint(string $sourceId): int;

    public function writeBatchWithCheckpoint(
        array  $sales,
        string $sourceId,
        int    $lastRow
    ): void;

}