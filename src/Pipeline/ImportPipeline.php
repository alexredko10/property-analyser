<?php

declare(strict_types=1);

namespace App\Pipeline;

use App\Contract\CheckpointWriterInterface;
use App\Processor\PropertyTransformer;
use App\Reader\CsvReader;
use InvalidArgumentException;
use RuntimeException;

final readonly class ImportPipeline
{
    public function __construct(
        private CsvReader $reader,
        private PropertyTransformer $transformer,
        private CheckpointWriterInterface $writer
    ) {}

    public function run(
        string $file,
        int $batchSize = 1000
    ): array {
        if ($batchSize < 1) {
            throw new InvalidArgumentException(
                'Batch size must be positive'
            );
        }

        $sourceId = hash_file('sha256', $file);

        if ($sourceId === false) {
            throw new RuntimeException(
                'Cannot hash source file'
            );
        }

        $lastRow = $this->writer->getCheckpoint($sourceId);

        $batch = [];
        $processed = 0;
        $rejected = 0;
        $rowNumber = 0;
        $pendingRows = 0;

        foreach ($this->reader->read($file) as $csvRow) {
            $rowNumber++;

            if ($rowNumber <= $lastRow) {
                continue;
            }

            $pendingRows++;

            try {
                $batch[] = $this->transformer->transform($csvRow);
            } catch (InvalidArgumentException $e) {
                $rejected++;
            }

            if ($pendingRows >= $batchSize) {
                $this->writer->writeBatchWithCheckpoint(
                    $batch,
                    $sourceId,
                    $rowNumber
                );

                $processed += count($batch);
                $batch = [];
                $pendingRows = 0;
            }
        }

        // Save the remaining records.
        if ($pendingRows > 0) {
            $this->writer->writeBatchWithCheckpoint(
                $batch,
                $sourceId,
                $rowNumber
            );

            $processed += count($batch);
        }

        return [
            'processed' => $processed,
            'rejected' => $rejected,
            'skipped' => $lastRow,
        ];
    }
}
