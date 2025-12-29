<?php
// backend/migrate_production.php

// 1. Load configuration
require_once __DIR__ . '/config/config.production.php';
require_once __DIR__ . '/config/database.production.php';

echo "<h1>Production Database Migration</h1>";

try {
    // 2. Connect to Database
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p>✅ Database connection established.</p>";

    // 3. Read Schema File
    $schemaFile = __DIR__ . '/config/schema.postgresql.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found at: " . $schemaFile);
    }
    
    $sql = file_get_contents($schemaFile);
    if (!$sql) {
        throw new Exception("Schema file is empty or could not be read.");
    }
    echo "<p>✅ Schema file read successfully (" . strlen($sql) . " bytes).</p>";

    // 4. Split and Execute SQL (Simple implementation)
    // For PostgreSQL, we can often execute the whole block, but splitting by ; is safer for some drivers
    // However, for complex schemas with functions/procedures, executing as one block is often better if the driver supports it.
    // PDO supports multiple statements in one go for PostgreSQL.
    
    $conn->exec($sql);
    echo "<p>✅ SQL executed successfully.</p>";
    
    echo "<h2>🎉 Migration Complete!</h2>";
    echo "<p>You can now delete this file.</p>";

} catch (Exception $e) {
    echo "<h2>❌ Migration Failed</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    // Print stack trace for debugging if strict mode is off
}
?>
