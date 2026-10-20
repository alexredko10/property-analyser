# PropertyAnalyser

A standalone PHP ETL application for processing UK Land Registry property sales data (1.28M+ records).

**Stack:** PHP 8.3+, Composer, Symfony Console, SQLite.

## Installation

```bash
git clone https://github.com/YOUR_USERNAME/property-analyser.git
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

# Inspect CSV (planned)
php bin/console app:inspect data/pp-2021.csv

# Process records (planned)
php bin/console app:import data/pp-2021.csv --batch-size=1000

# Reconcile results (planned)
php bin/console app:reconcile
```

Currently, `app:reconcile` is a placeholder. Other commands will be implemented during development.

## Development

```bash
# Create feature branch
git switch -c feature/csv-reader

# Commit changes
git add .
git commit -m "feat: implement CSV reader"

# Merge into main
git switch main
git merge --no-ff feature/csv-reader
```

## Architecture

`CSV → Reader → Validator → Transformer → SQLite → Reconciliation`

The pipeline uses streaming, batch processing and checkpointing to support large datasets and reliable recovery.

See [ROADMAP.md](ROADMAP.md) for implementation progress.

## AI Usage

AI assistance was used for planning, documentation suggestions. All implementation and testing results are subject to developer verification.

## Performance

Dataset: HM Land Registry Price Paid Data (2021)

- Records processed: 1,281,983
- Execution time: 9.05 seconds
- Peak PHP memory: 4 MB
- Batch size: 1,000
- Storage: SQLite
- Rejected records: 0