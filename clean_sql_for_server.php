<?php
/**
 * Clean SQL Dump for Server Upload
 *
 * This script removes DEFINER clauses and SQL SECURITY DEFINER from SQL dumps
 * to prevent privilege errors when importing to shared hosting environments.
 *
 * Usage: php clean_sql_for_server.php
 */

$inputFile = __DIR__ . '/truckers_africa_database.sql';
$outputFile = __DIR__ . '/truckers_africa_database_clean.sql';

echo "Cleaning SQL file for server upload...\n";
echo "Input:  {$inputFile}\n";
echo "Output: {$outputFile}\n\n";

if (!file_exists($inputFile)) {
    die("ERROR: Input file not found: {$inputFile}\n");
}

// Read the SQL file
$sql = file_get_contents($inputFile);

if ($sql === false) {
    die("ERROR: Could not read input file\n");
}

$originalSize = strlen($sql);

// Remove DEFINER clauses
// Pattern matches: DEFINER=`user`@`host`
$sql = preg_replace(
    '/DEFINER\s*=\s*`[^`]+`@`[^`]+`/i',
    '',
    $sql
);

// Remove SQL SECURITY DEFINER
$sql = preg_replace(
    '/SQL\s+SECURITY\s+DEFINER/i',
    '',
    $sql
);

// Clean up multiple consecutive spaces (but preserve line breaks)
$sql = preg_replace('/[ \t]+/', ' ', $sql);

// Clean up spaces around common SQL keywords to improve formatting
$sql = str_replace('CREATE ALGORITHM=UNDEFINED  VIEW', 'CREATE ALGORITHM=UNDEFINED VIEW', $sql);
$sql = str_replace('CREATE  VIEW', 'CREATE VIEW', $sql);

$cleanedSize = strlen($sql);

// Write the cleaned SQL file
if (file_put_contents($outputFile, $sql) === false) {
    die("ERROR: Could not write output file\n");
}

echo "SUCCESS!\n\n";
echo "Original size: " . number_format($originalSize) . " bytes\n";
echo "Cleaned size:  " . number_format($cleanedSize) . " bytes\n";
echo "Removed:       " . number_format($originalSize - $cleanedSize) . " bytes\n\n";
echo "The cleaned SQL file is ready for upload: truckers_africa_database_clean.sql\n";
echo "\nYou can now upload this file to your server database via phpMyAdmin.\n";
