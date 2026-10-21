# PropertyAnalyser

A standalone PHP ETL application for processing UK Land Registry property sales data (1.28M+ records).

**Stack:** PHP 8.3+, Composer, Symfony Console, SQLite.

## Installation

```bash
git clone https://github.com/alexredko10/property-analyser.git
cd property-analyser
composer install
```

Requires PHP 8.3+ with PDO SQLite enabled.

## Dataset

Download the 2021 HM Land Registry Price Paid Data:

```bash
mkdir -p data

curl -fL \
  "https://price-paid-data.publicdata.landregistry.gov.uk/pp-2021.csv" \
  -o data/pp-2021.csv
```

Source: [HM Land Registry](https://www.gov.uk/government/statistical-data-sets/price-paid-data-yearly-file).

The dataset is excluded from Git. Contains HM Land Registry data © Crown copyright and database right.

## Usage

```bash
# List available commands
php bin/console list

# Inspect CSV
php bin/console app:inspect data/pp-2021.csv

# Import records into SQLite
php bin/console app:import data/pp-2021.csv --batch-size=1000

# Reconcile results
php bin/console app:reconcile
```

Imports are stored in `data/properties.sqlite`. Re-running an import resumes after the last committed batch for the same source file.

`app:reconcile` compares the record count and total sale price in `data/pp-2021.csv` with the stored records.

## Architecture

`CSV → Reader → Validator → Transformer → SQLite → Reconciliation`

The pipeline uses streaming, batch processing and checkpointing to support large datasets and reliable recovery.

See [ROADMAP.md](ROADMAP.md) for implementation progress.

## Naive vs Modular Implementation

The project includes two approaches to processing the same HM Land Registry dataset.

**Naive approach** — a single PHP script with sequential processing, simplified validation and one database transaction.

**Modular approach** — a structured ETL pipeline with validation, batch processing, checkpoints, logging and reconciliation.

### Usage

```bash
# Naive implementation
php -d memory_limit=128M naive.php

# Modular implementation (fresh database required for full import)
php -d memory_limit=128M bin/console app:import data/pp-2021.csv --batch-size=1000
```

### Performance

| Metric | Naive | Modular |
|---|---|---|
| Records | 1,281,983 | 1,281,983 |
| Duration | 4.28 s | 9.05 s |
| Peak PHP memory | 2 MB | 4 MB |
| Checkpoint recovery | No | Yes |
| Structured logging | No | Yes |

## Limitations

- Reconciliation validates record counts and total prices, not individual records.
- Checkpoints support resuming imports of unchanged source files, but overlapping records across different files are not handled automatically.
- Rejected records are counted but not persisted with their reasons.
- The naive implementation recreates its output table on each run.

## AI Assistance

AI tools were used for architecture discussions, code suggestions and documentation. The implementation was reviewed and tested locally.