<?php

declare(strict_types=1);

namespace App\Command;

use App\Logging\LoggerFactory;
use PDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reconcile',
    description: 'Verify imported property records'
)]
final class ReconcileCommand extends Command
{
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $file = dirname(__DIR__, 2) . '/data/pp-2021.csv';
        $database = dirname(__DIR__, 2) . '/data/properties.sqlite';
        $logger = LoggerFactory::create();

        if (!is_file($file) || !is_file($database)) {
            $output->writeln('<error>Source or database missing</error>');
            return Command::FAILURE;
        }

        $sourceCount = 0;
        $sourceTotal = 0;

        $handle = fopen($file, 'rb');

        if ($handle === false) {
            return Command::FAILURE;
        }

        try {
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $sourceCount++;

                if (count($row) !== 16
                    || filter_var($row[1], FILTER_VALIDATE_INT) === false
                    || (int) $row[1] <= 0) {
                    $output->writeln('<error>Invalid source data</error>');
                    return Command::FAILURE;
                }

                $sourceTotal += (int) $row[1];
            }
        } finally {
            fclose($handle);
        }

        $pdo = new PDO('sqlite:' . $database);
        $stored = $pdo->query(
            'SELECT COUNT(*) AS count, SUM(price) AS total
             FROM property_sales'
        )->fetch(PDO::FETCH_ASSOC);

        $storedCount = (int) $stored['count'];
        $storedTotal = (int) $stored['total'];

        $passed = $sourceCount === $storedCount
            && $sourceTotal === $storedTotal;
        $logger->log(
            $passed ? 'info' : 'error',
            'Reconciliation completed',
            [
                'source_records' => $sourceCount,
                'stored_records' => $storedCount,
                'source_total' => $sourceTotal,
                'stored_total' => $storedTotal,
                'status' => $passed ? 'PASS' : 'FAIL',
            ]
        );
        $output->writeln([
            'Reconciliation Report',
            '---------------------',
            "Source records: {$sourceCount}",
            "Stored records: {$storedCount}",
            "Source price total: {$sourceTotal}",
            "Stored price total: {$storedTotal}",
            'Status: ' . ($passed ? 'PASS' : 'FAIL'),
        ]);

        return $passed ? Command::SUCCESS : Command::FAILURE;
    }
}
