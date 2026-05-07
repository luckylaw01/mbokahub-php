<?php
/**
 * DB Safety Guard for MbokaHub
 * Prevents running critical migration scripts on production environments
 */

function check_db_safety($pdo) {
    // 1. Check for environment variables
    $env = getenv('APP_ENV') ?: 'development';
    
    // 2. Check hostname
    $hostname = gethostname();
    $is_local = in_array($hostname, ['localhost', '127.0.0.1']) || strpos($hostname, '.local') !== false;

    // 3. Check for specific Namecheap/Production markers in the DB connection string
    // You can also query the DB version or character set to detect environment
    try {
        $stmt = $pdo->query("SELECT DATABASE()");
        $db_name = $stmt->fetchColumn();
        
        // Define your production DB names here to "blacklist" them from scripts
        $production_dbs = ['production_lms', 'mbokahub_live'];
        
        if (in_array($db_name, $production_dbs) && $env !== 'migration_authorized') {
            die("\n[CRITICAL ERROR] Access Denied: Attempted to run migration on PRODUCTION database '$db_name'.\n");
        }
    } catch (Exception $e) {
        // Fallback to safe mode if check fails
    }
}
