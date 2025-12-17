<?php
/**
 * Fix SQL dump for server import
 * Removes DEFINER clauses that cause permission errors
 */

// Find the SQL dump file
$sqlFiles = glob('*.sql');

if (empty($sqlFiles)) {
    echo "No SQL files found in current directory.\n";
    echo "Please run this script in the directory containing your SQL dump file.\n";
    exit(1);
}

echo "Found SQL files:\n";
foreach ($sqlFiles as $index => $file) {
    echo ($index + 1) . ". {$file}\n";
}

if (count($sqlFiles) == 1) {
    $sqlFile = $sqlFiles[0];
} else {
    echo "\nEnter the number of the file to fix: ";
    $choice = trim(fgets(STDIN));
    $sqlFile = $sqlFiles[$choice - 1] ?? null;

    if (!$sqlFile || !file_exists($sqlFile)) {
        echo "Invalid choice.\n";
        exit(1);
    }
}

echo "\nProcessing: {$sqlFile}\n";

// Read the SQL file
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    echo "Error: Could not read file.\n";
    exit(1);
}

// Create backup
$backupFile = $sqlFile . '.backup';
if (!copy($sqlFile, $backupFile)) {
    echo "Error: Could not create backup.\n";
    exit(1);
}
echo "Backup created: {$backupFile}\n";

// Remove DEFINER clauses from CREATE VIEW statements
$sql = preg_replace(
    '/CREATE\s+ALGORITHM=\w+\s+DEFINER=`[^`]+`@`[^`]+`\s+SQL\s+SECURITY\s+\w+\s+VIEW/i',
    'CREATE VIEW',
    $sql
);

// Remove DEFINER clauses from CREATE TRIGGER statements
$sql = preg_replace(
    '/CREATE\s+DEFINER=`[^`]+`@`[^`]+`\s+TRIGGER/i',
    'CREATE TRIGGER',
    $sql
);

// Remove DEFINER clauses from CREATE PROCEDURE statements
$sql = preg_replace(
    '/CREATE\s+DEFINER=`[^`]+`@`[^`]+`\s+PROCEDURE/i',
    'CREATE PROCEDURE',
    $sql
);

// Remove DEFINER clauses from CREATE FUNCTION statements
$sql = preg_replace(
    '/CREATE\s+DEFINER=`[^`]+`@`[^`]+`\s+FUNCTION/i',
    'CREATE FUNCTION',
    $sql
);

// Remove DEFINER clauses from CREATE EVENT statements
$sql = preg_replace(
    '/CREATE\s+DEFINER=`[^`]+`@`[^`]+`\s+EVENT/i',
    'CREATE EVENT',
    $sql
);

// Write the fixed SQL
$outputFile = str_replace('.sql', '_fixed.sql', $sqlFile);
if (file_put_contents($outputFile, $sql) === false) {
    echo "Error: Could not write output file.\n";
    exit(1);
}

echo "\nFixed SQL file created: {$outputFile}\n";
echo "\nChanges made:\n";
echo "- Removed DEFINER clauses from VIEWs\n";
echo "- Removed DEFINER clauses from TRIGGERs\n";
echo "- Removed DEFINER clauses from PROCEDUREs\n";
echo "- Removed DEFINER clauses from FUNCTIONs\n";
echo "- Removed DEFINER clauses from EVENTs\n";

echo "\nYou can now import: {$outputFile}\n";
echo "Original file backed up as: {$backupFile}\n";
