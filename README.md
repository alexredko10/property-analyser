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

## Performance

Dataset: HM Land Registry Price Paid Data (2021)

- Records processed: 1,281,983
- Execution time: 9.05 seconds
- Peak PHP memory: 4 MB
- Batch size: 1,000
- Storage: SQLite
- Rejected records: 0