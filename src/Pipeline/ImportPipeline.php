<?php

declare(strict_types=1);

namespace App\Pipeline;

use App\Contract\PropertyWriterInterface;
use App\Processor\PropertyTransformer;
use App\Reader\CsvReader;
use InvalidArgumentException;

final readonly class ImportPipeline
{

    public function __construct(
        private CsvReader               $reader,
        private PropertyTransformer     $transformer,
        private PropertyWriterInterface $writer
    )
    {
    }

    public function run(string $file, int $batchSize = 1000): array
    {
        if ($batchSize < 1) {
            throw new InvalidArgumentException('Batch size have to be positive');
        }

        $batch = [];
        $processed = 0;
        $rejected = 0;

        foreach ($this->reader->read($file) as $csvRow) {
            try {
                $batch[] = $this->transformer->transform($csvRow);
            } catch (\InvalidArgumentException $e) {
                $rejected++;
                continue;
            }

            if (count($batch) >= $batchSize) {
                $this->writer->writeBatch($batch);
                $processed += count($batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->writer->writeBatch($batch);
            $processed += count($batch);
        }

        return [
            'processed' => $processed,
            'rejected' => $rejected
        ];
    }
}
