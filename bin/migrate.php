<?php

declare(strict_types=1);

/*
 * Applique les migrations SQL du pilote courant (database/migrations/<driver>/*.sql),
 * dans l'ordre, une seule fois chacune (table schema_migrations).
 * Usage : php bin/migrate.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Database;

$pdo = Database::pdo();
$driver = Database::driver();

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(190) PRIMARY KEY, applied_at VARCHAR(19) NOT NULL)');
$applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);

$files = glob(BASE_PATH . '/database/migrations/' . $driver . '/*.sql') ?: [];
sort($files);

$count = 0;
foreach ($files as $file) {
    $version = basename($file, '.sql');
    if (in_array($version, $applied, true)) {
        continue;
    }

    $sql = preg_replace('/^\s*--.*$/m', '', (string) file_get_contents($file));
    $statements = array_filter(array_map('trim', explode(';', (string) $sql)));

    // MySQL valide implicitement le DDL : la transaction ne protège vraiment que sous SQLite.
    $pdo->beginTransaction();
    try {
        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
        $pdo->prepare('INSERT INTO schema_migrations (version, applied_at) VALUES (?, ?)')->execute([$version, date('Y-m-d H:i:s')]);
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "Échec de la migration $version : " . $e->getMessage() . PHP_EOL);
        exit(1);
    }

    echo "✔ Migration appliquée : $version" . PHP_EOL;
    $count++;
}

echo $count === 0 ? 'Base déjà à jour.' . PHP_EOL : "$count migration(s) appliquée(s) ($driver)." . PHP_EOL;
