<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

// Get the connection
$connection = $container->get('doctrine.dbal.default_connection');
$schemaManager = $connection->createSchemaManager();

echo "=== Checking actual table structure ===\n\n";

// Get actual table
$actualTable = $schemaManager->introspectTable('doctrine_migration_versions');

echo "Actual table columns:\n";
foreach ($actualTable->getColumns() as $column) {
    echo sprintf(
        "  - %s: type=%s, length=%s, notnull=%s\n",
        $column->getName(),
        get_class($column->getType()),
        $column->getLength() ?? 'NULL',
        $column->getNotnull() ? 'true' : 'false'
    );
}

echo "\nActual table primary key:\n";
$pk = $actualTable->getPrimaryKey();
if ($pk) {
    echo "  - Columns: " . implode(', ', $pk->getColumns()) . "\n";
} else {
    echo "  - NO PRIMARY KEY FOUND!\n";
}

echo "\n=== Creating expected table structure ===\n\n";

// Create what Doctrine Migrations expects
$expectedTable = new Table('doctrine_migration_versions');
$expectedTable->addColumn('version', Types::STRING, ['length' => 191, 'notnull' => true]);
$expectedTable->addColumn('executed_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
$expectedTable->addColumn('execution_time', Types::INTEGER, ['notnull' => false]);
$expectedTable->setPrimaryKey(['version']);

echo "Expected table columns:\n";
foreach ($expectedTable->getColumns() as $column) {
    echo sprintf(
        "  - %s: type=%s, length=%s, notnull=%s\n",
        $column->getName(),
        get_class($column->getType()),
        $column->getLength() ?? 'NULL',
        $column->getNotnull() ? 'true' : 'false'
    );
}

echo "\nExpected table primary key:\n";
$pk = $expectedTable->getPrimaryKey();
if ($pk) {
    echo "  - Columns: " . implode(', ', $pk->getColumns()) . "\n";
}

echo "\n=== Comparing tables ===\n\n";

$comparator = $schemaManager->createComparator();
$diff = $comparator->compareTables($expectedTable, $actualTable);

if (!$diff->isEmpty()) {
    echo "✗ TABLES ARE DIFFERENT!\n\n";
    
    if (!empty($diff->getAddedColumns())) {
        echo "Added columns:\n";
        foreach ($diff->getAddedColumns() as $col) {
            echo "  - " . $col->getName() . "\n";
        }
    }
    
    if (!empty($diff->getDroppedColumns())) {
        echo "Dropped columns:\n";
        foreach ($diff->getDroppedColumns() as $col) {
            echo "  - " . $col->getName() . "\n";
        }
    }
    
    if (!empty($diff->getModifiedColumns())) {
        echo "Modified columns:\n";
        foreach ($diff->getModifiedColumns() as $colDiff) {
            echo "  - " . $colDiff->getOldColumn()->getName() . "\n";
            echo "    Old: " . get_class($colDiff->getOldColumn()->getType()) . "\n";
            echo "    New: " . get_class($colDiff->getNewColumn()->getType()) . "\n";
        }
    }
    
    if ($diff->hasChangedPrimaryKey()) {
        echo "Primary key changed!\n";
    }
} else {
    echo "✓ TABLES ARE IDENTICAL!\n";
    echo "\nThis means the table structure is correct, but Doctrine Migrations\n";
    echo "is still reporting it as out of date. This is likely a bug in\n";
    echo "Doctrine Migrations 3.8.0 with DBAL 4.x and PostgreSQL.\n";
}

echo "\n";