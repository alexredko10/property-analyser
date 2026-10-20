<?php

declare(strict_types=1);

namespace Writer;

use App\DTO\PropertySale;
use App\Writer\SqliteWriter;
use PDO;
use PHPUnit\Framework\TestCase;

final class SqliteWriterTest extends TestCase
{
    public function testWritesBatch(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $writer = new SqliteWriter($pdo);
        $writer->init();

        $sale = new PropertySale(
            transactionId: 'test-123',
            price: 322000,
            transferDate: '2021-07-30',
            postcode: 'BS5 7LS',
            propertyType: 'Terraced',
            newBuild: false,
            tenure: 'F',
            town: 'BRISTOL',
            district: 'CITY OF BRISTOL',
            priceCategory: 'MEDIUM'
        );

        $writer->writeBatch([$sale]);

        $count = $pdo->query(
            'SELECT COUNT(*) FROM property_sales'
        )->fetchColumn();

        self::assertSame(1, (int) $count);
    }
}
