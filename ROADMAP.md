# PropertyAnalyser — Roadmap

A standalone PHP ETL application for processing 1.28 million UK Land Registry property records.

**Stack:** PHP 8.3+, Symfony Console, SQLite, PHPUnit.

## 1. Project Setup
- [x] Initialise Git and Composer.
- [x] Configure Symfony Console.
- [x] Download Land Registry CSV.
- [ ] Document installation and usage.

## 2. CSV Reader
- [ ] Implement streaming CSV reader.
- [ ] Validate CSV structure.
- [ ] Add `app:inspect` command.
- [ ] Measure execution time and memory usage.

## 3. Data Processing
- [ ] Validate property records.
- [ ] Normalise and transform data.
- [ ] Handle invalid records.

## 4. Data Storage
- [ ] Implement SQLite writer.
- [ ] Add batch processing.
- [ ] Support checkpoints and retries.
- [ ] Add `app:import` command.

## 5. Reconciliation
- [ ] Compare source and destination counts.
- [ ] Verify data integrity.
- [ ] Report processing errors.

## 6. Testing & Documentation
- [ ] Add unit and integration tests.
- [ ] Benchmark with 1M+ records.
- [ ] Document architecture and scalability.
- [ ] Update README with setup instructions.

## Acceptance Criteria
- Process 1M+ records with bounded memory usage.
- Handle invalid data without crashing the entire pipeline.
- Support safe recovery after interruption.
- Verify that all source records are accounted for.
- Run as a standalone CLI application.