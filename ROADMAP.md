# PropertyAnalyser — Roadmap

A standalone PHP ETL application for processing 1.28 million UK Land Registry property records.

**Stack:** PHP 8.3+, Symfony Console, SQLite, PHPUnit.

## 1. Project Setup
- [x] Initialise Git and Composer.
- [x] Configure Symfony Console.
- [x] Download Land Registry CSV.
- [x] Document installation and usage.

## 2. CSV Reader
- [x] Implement streaming CSV reader.
- [x] Validate CSV structure.
- [x] Add `app:inspect` command.
- [x] Measure execution time and memory usage.

## 3. Data Processing
- [x] Validate property records.
- [x] Normalise and transform data.
- [x] Handle invalid records.

## 4. Data Storage
- [x] Implement SQLite writer.
- [x] Add batch processing.
- [x] Support checkpoints and retries.
- [x] Add `app:import` command.

## 5. Reconciliation
- [x] Compare source and destination counts.
- [x] Verify data integrity.
- [x] Report processing errors.

## 6. Testing & Documentation
- [x] Add unit and integration tests.
- [x] Benchmark with 1M+ records.
- [x] Document architecture and scalability.
- [x] Update README with setup instructions.

## Acceptance Criteria
- Process 1M+ records with bounded memory usage.
- Handle invalid data without crashing the entire pipeline.
- Support safe recovery after interruption.
- Verify that all source records are accounted for.
- Run as a standalone CLI application.