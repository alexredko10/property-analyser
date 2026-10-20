<?php

declare(strict_types=1);

namespace App\Writer;

use App\Contract\CheckpointWriterInterface;
use PDO;
use Throwable;

final class SqliteWriter implements CheckpointWriterInterface
{

    public function __construct(
        private readonly PDO $pdo
    )
    {
        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    public function init(): void
    {

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS import_checkpoints (
        source_id TEXT PRIMARY KEY,
        last_row INTEGER NOT NULL DEFAULT 0,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS property_sales (
                transaction_id TEXT PRIMARY KEY,
                price INTEGER NOT NULL,
                transfer_date TEXT NOT NULL,
                postcode TEXT,
                property_type TEXT NOT NULL,
                new_build INTEGER NOT NULL,
                tenure TEXT NOT NULL,
                town TEXT NOT NULL,
                district TEXT NOT NULL,
                price_category TEXT NOT NULL
            )'
        );
    }

    public function writeBatch(array $sales): void
    {
        if ($sales === []) {
            return;
        }

        $sql = 'INSERT INTO property_sales (
            transaction_id, price, transfer_date,
            postcode, property_type, new_build,
            tenure, town, district, price_category
        ) VALUES (
            :transaction_id, :price, :transfer_date,
            :postcode, :property_type, :new_build,
            :tenure, :town, :district, :price_category
        )';

        $statement = $this->pdo->prepare($sql);

        $this->pdo->beginTransaction();

        try {
            foreach ($sales as $sale) {
                $statement->execute([
                    ':transaction_id' => $sale->transactionId,
                    ':price' => $sale->price,
                    ':transfer_date' => $sale->transferDate,
                    ':postcode' => $sale->postcode,
                    ':property_type' => $sale->propertyType,
                    ':new_build' => (int)$sale->newBuild,
                    ':tenure' => $sale->tenure,
                    ':town' => $sale->town,
                    ':district' => $sale->district,
                    ':price_category' => $sale->priceCategory,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function getCheckpoint(string $sourceId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT last_row
         FROM import_checkpoints
         WHERE source_id = :source_id'
        );

        $statement->execute(['source_id' => $sourceId]);

        $value = $statement->fetchColumn();

        return $value === false ? 0 : (int)$value;
    }

    public function writeBatchWithCheckpoint(
        array  $sales,
        string $sourceId,
        int    $lastRow
    ): void
    {
        $this->pdo->beginTransaction();

        try {
            $insert = $this->pdo->prepare(
                'INSERT INTO property_sales (
                transaction_id, price, transfer_date,
                postcode, property_type, new_build,
                tenure, town, district, price_category
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            foreach ($sales as $sale) {
                $insert->execute([
                    $sale->transactionId,
                    $sale->price,
                    $sale->transferDate,
                    $sale->postcode,
                    $sale->propertyType,
                    (int)$sale->newBuild,
                    $sale->tenure,
                    $sale->town,
                    $sale->district,
                    $sale->priceCategory,
                ]);
            }

            $checkpoint = $this->pdo->prepare(
                'INSERT INTO import_checkpoints
                (source_id, last_row)
             VALUES (:source_id, :last_row)
             ON CONFLICT(source_id)
             DO UPDATE SET
                last_row = excluded.last_row,
                updated_at = CURRENT_TIMESTAMP'
            );

            $checkpoint->execute([
                'source_id' => $sourceId,
                'last_row' => $lastRow,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
