<?php

declare(strict_types=1);

namespace App\Command;

use App\Reader\CsvReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:inspect',
    description: 'Inspect a CSV dataset'
)]
final class InspectCommand extends Command
{
    public function __construct(
        private readonly CsvReader $reader
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'file',
            InputArgument::REQUIRED,
            'Path to the CSV file'
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $path = $input->getArgument('file');

        $records = 0;
        $invalid = 0;
        $startedAt = microtime(true);

        try {
            foreach ($this->reader->read($path) as $row) {
                $records++;

                if (count($row) !== 16) {
                    $invalid++;
                }
            }
        } catch (Throwable $exception) {
            $output->writeln(
                '<error>' .
                $exception->getMessage() .
                '</error>'
            );

            return Command::FAILURE;
        }

        $duration = microtime(true) - $startedAt;
        $memory = memory_get_peak_usage(true) / 1024 / 1024;

        $output->writeln([
            '',
            '<info>CSV Inspection Completed</info>',
            '-----------------------------',
            "Total records: {$records}",
            "Invalid rows: {$invalid}",
            sprintf('Duration: %.2f seconds', $duration),
            sprintf('Peak memory: %.2f MB', $memory),
        ]);

        return Command::SUCCESS;
    }
}
