<?php

declare(strict_types=1);

namespace App\Command;

use App\Pipeline\ImportPipeline;
use App\Processor\PropertyTransformer;
use App\Processor\PropertyValidator;
use App\Reader\CsvReader;
use App\Writer\SqliteWriter;
use App\Logging\LoggerFactory;
use PDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import',
    description: 'Import Land Registry CSV into SQLite'
)]
final class ImportCommand extends Command
{

    protected function configure(): void
    {

        $this
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Source CSV file'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of records per batch',
                '1000'
            );
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output
    ): int
    {

        $logger = LoggerFactory::create();
        $file = (string)$input->getArgument('file');

        $batchSize = filter_var(
            $input->getOption('batch-size'),
            FILTER_VALIDATE_INT
        );

        if ($batchSize === false || $batchSize < 1) {
            $output->writeln('<error>Invalid batch size</error>');
            return Command::INVALID;
        }
        $logger->info('Import started', [
            'file' => basename($file),
            'batch_size' => $batchSize,
        ]);

        $startedAt = microtime(true);

        try {
            $dbPath = dirname(__DIR__, 2) . '/data/properties.sqlite';

            $pdo = new PDO('sqlite:' . $dbPath);
            $writer = new SqliteWriter($pdo);
            $writer->init();

            $pipeline = new ImportPipeline(
                new CsvReader(),
                new PropertyTransformer(new PropertyValidator()),
                $writer
            );

            $startedAt = microtime(true);

            $result = $pipeline->run($file, $batchSize);

            $duration = microtime(true) - $startedAt;

            $logger->info('Import completed', [
                'processed' => $result['processed'],
                'rejected' => $result['rejected'],
                'skipped' => $result['skipped'],
                'duration_seconds' => round($duration, 2),
            ]);

            $output->writeln([
                '<info>Import completed</info>',
                'Processed: ' . $result['processed'],
                'Rejected: ' . $result['rejected'],
                'Duration: ' . sprintf('%.2f seconds', $duration),
                'Peak memory: ' . sprintf(
                    '%.2f MB',
                    memory_get_peak_usage(true) / 1048576
                ),
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $logger->error('Import failed', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            $output->writeln(
                '<error>Import failed: '
                . $exception->getMessage()
                . '</error>'
            );

            return Command::FAILURE;
        }
    }
}
