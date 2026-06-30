<?php
/**
 * Hosted vs Local Database Schema Diagnostics Tool
 * Compares the current schema of the database to the target MbokaHub specification.
 */
require_once '../includes/db_connect.php';

// Disable caching for accurate checks
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Expected database schema definition based on mbokahub_db_schema.sql
$expected_schema = [
    'categories' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'name_en' => ['type' => 'varchar(100)', 'nullable' => 'NO', 'extra' => ''],
            'name_sw' => ['type' => 'varchar(100)', 'nullable' => 'NO', 'extra' => ''],
            'icon_class' => ['type' => 'varchar(50)', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'idx_category_name_en' => ['name_en'],
            'idx_category_name_sw' => ['name_sw']
        ],
        'foreign_keys' => []
    ],
    'certifications' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'institution' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'title' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'issue_date' => ['type' => 'date', 'nullable' => 'YES', 'extra' => ''],
            'expiry_date' => ['type' => 'date', 'nullable' => 'YES', 'extra' => ''],
            'certificate_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'user_id' => ['user_id']
        ],
        'foreign_keys' => [
            'certifications_ibfk_1' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'contractor_profiles' => [
        'columns' => [
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'company_name' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'reg_number' => ['type' => 'varchar(100)', 'nullable' => 'YES', 'extra' => ''],
            'kra_pin' => ['type' => 'varchar(50)', 'nullable' => 'YES', 'extra' => ''],
            'business_description' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'website_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['user_id']
        ],
        'foreign_keys' => [
            'fk_contractor_user' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'experiences' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'company' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'role' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'start_date' => ['type' => 'date', 'nullable' => 'NO', 'extra' => ''],
            'end_date' => ['type' => 'date', 'nullable' => 'YES', 'extra' => ''],
            'description' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'user_id' => ['user_id']
        ],
        'foreign_keys' => [
            'experiences_ibfk_1' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'fundi_profiles' => [
        'columns' => [
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'category_id' => ['type' => 'int(11)', 'nullable' => 'YES', 'extra' => ''],
            'institution_id' => ['type' => 'int(11)', 'nullable' => 'YES', 'extra' => ''],
            'avatar_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'cover_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'bio' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'location' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'tvet_level' => ['type' => "enum('student','apprentice','master')", 'nullable' => 'YES', 'extra' => ''],
            'is_verified' => ['type' => 'tinyint(1)', 'nullable' => 'YES', 'extra' => ''],
            'rating' => ['type' => 'decimal(3,2)', 'nullable' => 'YES', 'extra' => ''],
            'review_count' => ['type' => 'int(11)', 'nullable' => 'YES', 'extra' => ''],
            'resume_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['user_id'],
            'fk_fundi_category' => ['category_id'],
            'fk_fundi_institution' => ['institution_id'],
            'idx_fundi_location' => ['location'],
            'idx_fundi_rating' => ['rating'],
            'idx_fundi_verified' => ['is_verified']
        ],
        'foreign_keys' => [
            'fk_fundi_category' => ['column' => 'category_id', 'ref_table' => 'categories', 'ref_column' => 'id'],
            'fk_fundi_institution' => ['column' => 'institution_id', 'ref_table' => 'institutions', 'ref_column' => 'id'],
            'fk_fundi_user' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'gigs' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'title' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'price_amount' => ['type' => 'decimal(10,2)', 'nullable' => 'NO', 'extra' => ''],
            'price_unit' => ['type' => 'varchar(50)', 'nullable' => 'YES', 'extra' => ''],
            'description' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'image_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'is_active' => ['type' => 'tinyint(1)', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'user_id' => ['user_id']
        ],
        'foreign_keys' => [
            'gigs_ibfk_1' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'institutions' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'name' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'type' => ['type' => "enum('TVET','University','College','Vocational')", 'nullable' => 'YES', 'extra' => ''],
            'location' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'website' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'contact_email' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'logo_url' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'is_partner' => ['type' => 'tinyint(1)', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id']
        ],
        'foreign_keys' => []
    ],
    'jobs' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'assigned_fundi_id' => ['type' => 'int(11)', 'nullable' => 'YES', 'extra' => ''],
            'category_id' => ['type' => 'int(11)', 'nullable' => 'YES', 'extra' => ''],
            'title' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'status' => ['type' => "enum('open','direct_request','in_progress','completed','cancelled')", 'nullable' => 'YES', 'extra' => ''],
            'description' => ['type' => 'text', 'nullable' => 'NO', 'extra' => ''],
            'location' => ['type' => 'varchar(255)', 'nullable' => 'YES', 'extra' => ''],
            'budget_range' => ['type' => 'varchar(100)', 'nullable' => 'YES', 'extra' => ''],
            'urgency' => ['type' => "enum('standard','emergency')", 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'fk_job_hirer' => ['user_id'],
            'fk_job_fundi' => ['assigned_fundi_id'],
            'fk_job_category' => ['category_id'],
            'idx_job_status' => ['status'],
            'idx_job_location' => ['location'],
            'idx_job_created_at' => ['created_at']
        ],
        'foreign_keys' => [
            'fk_job_category' => ['column' => 'category_id', 'ref_table' => 'categories', 'ref_column' => 'id'],
            'fk_job_fundi' => ['column' => 'assigned_fundi_id', 'ref_table' => 'users', 'ref_column' => 'id'],
            'fk_job_hirer' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'job_bids' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'job_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'fundi_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'proposal_text' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'status' => ['type' => "enum('pending','accepted','rejected')", 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'fk_bid_job' => ['job_id'],
            'fk_bid_fundi' => ['fundi_id'],
            'idx_bid_status' => ['status']
        ],
        'foreign_keys' => [
            'fk_bid_fundi' => ['column' => 'fundi_id', 'ref_table' => 'users', 'ref_column' => 'id'],
            'fk_bid_job' => ['column' => 'job_id', 'ref_table' => 'jobs', 'ref_column' => 'id']
        ]
    ],
    'portfolio_items' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'title' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'description' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'image_url' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'completion_date' => ['type' => 'date', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'user_id' => ['user_id']
        ],
        'foreign_keys' => [
            'portfolio_items_ibfk_1' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'reviews' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'job_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'reviewer_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'reviewee_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'rating' => ['type' => 'tinyint(4)', 'nullable' => 'NO', 'extra' => ''],
            'comment' => ['type' => 'text', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'fk_review_job' => ['job_id'],
            'fk_review_reviewer' => ['reviewer_id'],
            'fk_review_reviewee' => ['reviewee_id'],
            'idx_review_rating' => ['rating']
        ],
        'foreign_keys' => [
            'fk_review_job' => ['column' => 'job_id', 'ref_table' => 'jobs', 'ref_column' => 'id'],
            'fk_review_reviewee' => ['column' => 'reviewee_id', 'ref_table' => 'users', 'ref_column' => 'id'],
            'fk_review_reviewer' => ['column' => 'reviewer_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'saved_jobs' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'job_id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'unique_save' => ['user_id', 'job_id'],
            'fk_saved_job' => ['job_id']
        ],
        'foreign_keys' => [
            'fk_saved_job' => ['column' => 'job_id', 'ref_table' => 'jobs', 'ref_column' => 'id'],
            'fk_saved_user' => ['column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id']
        ]
    ],
    'users' => [
        'columns' => [
            'id' => ['type' => 'int(11)', 'nullable' => 'NO', 'extra' => 'auto_increment'],
            'user_name' => ['type' => 'varchar(50)', 'nullable' => 'NO', 'extra' => ''],
            'first_name' => ['type' => 'varchar(100)', 'nullable' => 'NO', 'extra' => ''],
            'last_name' => ['type' => 'varchar(100)', 'nullable' => 'NO', 'extra' => ''],
            'email' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'phone' => ['type' => 'varchar(20)', 'nullable' => 'YES', 'extra' => ''],
            'password_hash' => ['type' => 'varchar(255)', 'nullable' => 'NO', 'extra' => ''],
            'role' => ['type' => "enum('hirer','fundi','admin','contractor')", 'nullable' => 'YES', 'extra' => ''],
            'language_pref' => ['type' => "enum('en','sw')", 'nullable' => 'YES', 'extra' => ''],
            'remember_token' => ['type' => 'varchar(100)', 'nullable' => 'YES', 'extra' => ''],
            'created_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => ''],
            'updated_at' => ['type' => 'timestamp', 'nullable' => 'NO', 'extra' => '']
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
            'user_name' => ['user_name'],
            'email' => ['email'],
            'idx_user_role' => ['role'],
            'idx_user_phone' => ['phone']
        ],
        'foreign_keys' => []
    ]
];

// Helper to normalize SQL types (strips display widths for comparison compatibility)
function normalizeSqlType($type) {
    $type = strtolower($type);
    if (strpos($type, 'enum') === 0) {
        return str_replace([' ', '"', "'"], ['', '', ''], $type);
    }
    // Convert generic int sizes to simple int, tinyint(1) or tinyint(4) to tinyint, etc.
    return preg_replace('/\(.*\)/', '', $type);
}

// Check database compatibility and compile actual structure
$actual_schema = [];
$db_name = "";

try {
    $db_name = $pdo->query("SELECT DATABASE()")->fetchColumn();
    
    // 1. Fetch tables list
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $actual_schema[$table] = [
            'columns' => [],
            'indexes' => [],
            'foreign_keys' => []
        ];
        
        // 2. Fetch columns info
        $col_stmt = $pdo->prepare("
            SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, EXTRA 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
        ");
        $col_stmt->execute([$table]);
        $columns = $col_stmt->fetchAll();
        foreach ($columns as $col) {
            $actual_schema[$table]['columns'][$col['COLUMN_NAME']] = [
                'type' => $col['COLUMN_TYPE'],
                'nullable' => $col['IS_NULLABLE'],
                'extra' => strtolower($col['EXTRA'])
            ];
        }
        
        // 3. Fetch indexes info
        $idx_stmt = $pdo->prepare("
            SELECT INDEX_NAME, COLUMN_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
            ORDER BY SEQ_IN_INDEX ASC
        ");
        $idx_stmt->execute([$table]);
        $indexes = $idx_stmt->fetchAll();
        foreach ($indexes as $idx) {
            $idx_name = $idx['INDEX_NAME'];
            if (!isset($actual_schema[$table]['indexes'][$idx_name])) {
                $actual_schema[$table]['indexes'][$idx_name] = [];
            }
            $actual_schema[$table]['indexes'][$idx_name][] = $idx['COLUMN_NAME'];
        }
        
        // 4. Fetch foreign keys info
        $fk_stmt = $pdo->prepare("
            SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $fk_stmt->execute([$table]);
        $fks = $fk_stmt->fetchAll();
        foreach ($fks as $fk) {
            $actual_schema[$table]['foreign_keys'][$fk['CONSTRAINT_NAME']] = [
                'column' => $fk['COLUMN_NAME'],
                'ref_table' => $fk['REFERENCED_TABLE_NAME'],
                'ref_column' => $fk['REFERENCED_COLUMN_NAME']
            ];
        }
    }
} catch (Exception $e) {
    die("Database access error: " . $e->getMessage());
}

// Compare expected schema with actual schema
$comparison_results = [];
$has_mismatch = false;

foreach ($expected_schema as $table => $spec) {
    $comparison_results[$table] = [
        'exists' => isset($actual_schema[$table]),
        'status' => 'OK',
        'mismatches' => []
    ];
    
    if (!$comparison_results[$table]['exists']) {
        $comparison_results[$table]['status'] = 'MISSING';
        $comparison_results[$table]['mismatches'][] = "Table is missing entirely.";
        $has_mismatch = true;
        continue;
    }
    
    $actual_table = $actual_schema[$table];
    
    // Check columns
    foreach ($spec['columns'] as $col_name => $col_spec) {
        if (!isset($actual_table['columns'][$col_name])) {
            $comparison_results[$table]['status'] = 'MISMATCH';
            $comparison_results[$table]['mismatches'][] = "Missing column: <code>$col_name</code>";
            $has_mismatch = true;
        } else {
            $actual_col = $actual_table['columns'][$col_name];
            
            // Check type normalization
            $expected_type_norm = normalizeSqlType($col_spec['type']);
            $actual_type_norm = normalizeSqlType($actual_col['type']);
            
            if ($expected_type_norm !== $actual_type_norm) {
                $comparison_results[$table]['status'] = 'MISMATCH';
                $comparison_results[$table]['mismatches'][] = "Column <code>$col_name</code> type mismatch: Expected <code>{$col_spec['type']}</code>, Got <code>{$actual_col['type']}</code>";
                $has_mismatch = true;
            }
            
            // Check nullable
            if ($col_spec['nullable'] !== $actual_col['nullable']) {
                $comparison_results[$table]['status'] = 'MISMATCH';
                $comparison_results[$table]['mismatches'][] = "Column <code>$col_name</code> nullable attribute mismatch: Expected <code>{$col_spec['nullable']}</code>, Got <code>{$actual_col['nullable']}</code>";
                $has_mismatch = true;
            }
        }
    }
    
    // Check for unexpected extra columns in hosted database
    foreach ($actual_table['columns'] as $col_name => $col_spec) {
        if (!isset($spec['columns'][$col_name])) {
            $comparison_results[$table]['status'] = 'EXTRA';
            $comparison_results[$table]['mismatches'][] = "Extra column in database: <code>$col_name</code>";
            // Extra columns don't strictly break core flow, but let's notify the user
        }
    }
    
    // Check indexes
    foreach ($spec['indexes'] as $idx_name => $cols) {
        if (!isset($actual_table['indexes'][$idx_name])) {
            $comparison_results[$table]['status'] = 'MISMATCH';
            $comparison_results[$table]['mismatches'][] = "Missing index: <code>$idx_name</code>";
            $has_mismatch = true;
        } else {
            // Sort column list to compare structure regardless of index position ordering
            $expected_cols = $cols;
            $actual_cols = $actual_table['indexes'][$idx_name];
            sort($expected_cols);
            sort($actual_cols);
            
            if ($expected_cols !== $actual_cols) {
                $comparison_results[$table]['status'] = 'MISMATCH';
                $comparison_results[$table]['mismatches'][] = "Index <code>$idx_name</code> columns mismatch: Expected (" . implode(',', $expected_cols) . "), Got (" . implode(',', $actual_cols) . ")";
                $has_mismatch = true;
            }
        }
    }
    
    // Check foreign keys
    foreach ($spec['foreign_keys'] as $fk_name => $fk_spec) {
        // We look for constraint by mapping columns since FK names can be auto-generated differently
        $fk_found = false;
        foreach ($actual_table['foreign_keys'] as $actual_fk_name => $actual_fk) {
            if (
                $actual_fk['column'] === $fk_spec['column'] &&
                $actual_fk['ref_table'] === $fk_spec['ref_table'] &&
                $actual_fk['ref_column'] === $fk_spec['ref_column']
            ) {
                $fk_found = true;
                break;
            }
        }
        
        if (!$fk_found) {
            $comparison_results[$table]['status'] = 'MISMATCH';
            $comparison_results[$table]['mismatches'][] = "Missing or incorrect Foreign Key constraint on column <code>{$fk_spec['column']}</code> referencing <code>{$fk_spec['ref_table']}({$fk_spec['ref_column']})</code>";
            $has_mismatch = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MbokaHub Database Comparator Diagnostics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .vibrant-gradient { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen py-10 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="vibrant-gradient text-white p-2.5 rounded-2xl shadow-lg">
                    <i class="fas fa-database text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">MbokaHub <span class="text-emerald-500">DB Diagnostic</span></h1>
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Database Schema Consistency Checker</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg font-bold">Connected DB: <?php echo htmlspecialchars($db_name); ?></span>
            </div>
        </div>

        <!-- Summary Banner Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-xl border border-slate-100 mb-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 vibrant-gradient opacity-5 blur-2xl -mr-16 -mt-16"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-lg font-bold mb-1">Global Validation Summary</h2>
                    <p class="text-slate-500 text-sm font-semibold">Compares actual tables, columns, indexes, and FK relationships with the model specification.</p>
                </div>
                <div>
                    <?php if (!$has_mismatch): ?>
                        <div class="bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl px-6 py-4 flex items-center gap-3 shadow-lg shadow-emerald-50">
                            <i class="fas fa-check-circle text-2xl"></i>
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider leading-none">Perfect Match</p>
                                <p class="text-lg font-black leading-tight">100% In Sync</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-rose-50 text-rose-600 border border-rose-100 rounded-2xl px-6 py-4 flex items-center gap-3 shadow-lg shadow-rose-50">
                            <i class="fas fa-triangle-exclamation text-2xl animate-pulse"></i>
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider leading-none">Validation Error</p>
                                <p class="text-lg font-black leading-tight">Schema Mismatch</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Table Details Grid -->
        <div class="space-y-4">
            <?php foreach ($comparison_results as $table => $res): ?>
                <?php 
                    $card_border = "border-slate-100";
                    $status_badge = '<span class="bg-slate-100 text-slate-500 border border-slate-200 px-3 py-1 rounded-full text-xs font-black uppercase">Unknown</span>';
                    
                    if ($res['status'] === 'OK') {
                        $card_border = "hover:border-emerald-200";
                        $status_badge = '<span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1 rounded-full text-xs font-black uppercase">MATCH</span>';
                    } elseif ($res['status'] === 'MISSING') {
                        $card_border = "border-red-200 bg-red-50/10";
                        $status_badge = '<span class="bg-red-50 text-red-600 border border-red-100 px-3 py-1 rounded-full text-xs font-black uppercase">MISSING</span>';
                    } elseif ($res['status'] === 'MISMATCH') {
                        $card_border = "border-amber-200 bg-amber-50/10";
                        $status_badge = '<span class="bg-amber-50 text-amber-600 border border-amber-100 px-3 py-1 rounded-full text-xs font-black uppercase">MISMATCH</span>';
                    } elseif ($res['status'] === 'EXTRA') {
                        $card_border = "border-blue-100 hover:border-blue-200";
                        $status_badge = '<span class="bg-blue-50 text-blue-600 border border-blue-100 px-3 py-1 rounded-full text-xs font-black uppercase">EXTRA COLUMNS</span>';
                    }
                ?>
                <div class="bg-white rounded-[2rem] p-6 border-2 <?php echo $card_border; ?> transition-all shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas <?php echo $res['status'] === 'OK' ? 'fa-table text-emerald-500' : ($res['status'] === 'MISSING' ? 'fa-folder-minus text-red-500' : 'fa-circle-exclamation text-amber-500'); ?> text-lg"></i>
                            <h3 class="font-extrabold text-slate-800 text-base md:text-lg"><?php echo htmlspecialchars($table); ?></h3>
                        </div>
                        <div>
                            <?php echo $status_badge; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($res['mismatches'])): ?>
                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Identified Discrepancies</p>
                            <ul class="space-y-1.5">
                                <?php foreach ($res['mismatches'] as $mismatch): ?>
                                    <li class="text-xs md:text-sm text-slate-700 flex items-start gap-2 leading-relaxed">
                                        <i class="fas fa-minus text-red-400 mt-1 flex-shrink-0 text-[10px]"></i>
                                        <span><?php echo $mismatch; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
            MbokaHub Diagnostics Tool © <?php echo date("Y"); ?>
        </div>
    </div>
</body>
</html>
