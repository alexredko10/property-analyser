<?php

declare(strict_types=1);

namespace App\Writer;

use App\Contract\PropertyWriterInterface;
use App\DTO\PropertySale;
use PDO;
use Throwable;

final class SqliteWriter implements PropertyWriterInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {
        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    public function init(): void
    {
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
                    ':new_build' => (int) $sale->newBuild,
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
}
