<?php

$url = 'https://price-paid-data.publicdata.landregistry.gov.uk/pp-2021.csv';
$dataDir = __DIR__ . '/data';
$csv = $dataDir . '/pp-2021.csv';
$dbFile = $dataDir . '/naive-properties.sqlite';

if (!is_dir($dataDir) && !mkdir($dataDir, 0775, true) && !is_dir($dataDir)) {
    throw new RuntimeException('Cannot create data directory');
}

try {
    if (!is_file($csv)) {
        echo "Downloading Land Registry CSV...\n";
        $remote = @fopen($url, 'rb');
        if ($remote === false) {
            throw new RuntimeException('Cannot download CSV');
        }
        $tempFile = $csv . '.part';
        $fp = @fopen($tempFile, 'wb');
        if ($fp === false) {
            fclose($remote);
            throw new RuntimeException('Cannot create download file');
        }
        try {
            $bytes = stream_copy_to_stream($remote, $fp);
            if ($bytes === false || $bytes === 0) {
                throw new RuntimeException('CSV download failed');
            }
        } catch (Throwable $e) {
            fclose($fp);
            fclose($remote);
            @unlink($tempFile);
            throw $e;
        }
        fclose($fp);
        fclose($remote);
        if (!rename($tempFile, $csv)) {
            throw new RuntimeException('Cannot save downloaded CSV');
        }
        echo "Downloaded $bytes bytes.\n";
    }

    // This naive version starts from an empty output table on each run.
    $pdo = new PDO('sqlite:' . $dbFile, options: [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('DROP TABLE IF EXISTS property_sales');
    $pdo->exec('CREATE TABLE property_sales (
        transaction_id TEXT PRIMARY KEY,
        price INTEGER NOT NULL,
        sale_date TEXT NOT NULL,
        postcode TEXT,
        property_type TEXT NOT NULL,
        town TEXT NOT NULL
    )');

    $insert = $pdo->prepare('INSERT INTO property_sales
        (transaction_id, price, sale_date, postcode, property_type, town)
        VALUES (?, ?, ?, ?, ?, ?)');

    $file = @fopen($csv, 'rb');
    if ($file === false) {
        throw new RuntimeException('Cannot open input CSV');
    }

    $started = microtime(true);
    $processed = 0;
    $rejected = 0;
    $sourceTotal = 0;

    // Single transaction for speed, but without recovery on crash
    $pdo->beginTransaction();
    try {
        while (($row = fgetcsv($file, 0, ',', '"', '')) !== false) {
            $isInvalid = count($row) !== 16 ||
                trim($row[0], '{} ') === '' ||
                filter_var($row[1], FILTER_VALIDATE_INT) === false ||
                (int)$row[1] <= 0 ||
                !preg_match('/^\d{4}-\d{2}-\d{2} /', $row[2]);
            if ($isInvalid) {
                $rejected++;
                continue;
            }

            $id = trim($row[0], '{} ');
            $price = (int) $row[1];
            $date = substr($row[2], 0, 10);
            $postcode = trim($row[3]) ?: null;
            $type = match ($row[4]) {
                'D' => 'Detached',
                'S' => 'Semi-detached',
                'T' => 'Terraced',
                'F' => 'Flat',
                'O' => 'Other',
                default => 'Unknown',
            };

            $insert->execute([$id, $price, $date, $postcode, $type, trim($row[11])]);
            $processed++;
            $sourceTotal += $price;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    } finally {
        fclose($file);
    }

    $result = $pdo->query('SELECT COUNT(*) AS total_rows, SUM(price) AS total_price FROM property_sales')
        ->fetch(PDO::FETCH_ASSOC);

    echo "Processed: $processed\n";
    echo "Rejected: $rejected\n";
    echo 'Injected: ' . $result['total_rows'] . "\n";
    echo 'Totals: ' . ((int) $result['total_price'] === $sourceTotal ? 'YES' : 'NO') . "\n";
    echo sprintf("Duration: %.2f seconds\n", microtime(true) - $started);
    echo sprintf("Peak PHP memory: %.2f MB\n", memory_get_peak_usage(true) / 1048576);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
