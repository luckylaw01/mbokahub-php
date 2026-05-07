<?php
/**
 * CRITICAL MIGRATION RUNNER - SAFETY FIRST
 * This tool intercepts SQL and demands multiple confirmations if destructive keywords are detected.
 */
session_start();
require_once 'includes/db_connect.php';

// SIMPLE PASSWORD PROTECTION (Change this immediately!)
$MIGRATION_PASSWORD = 'mboka_admin_2026'; 

$message = "";
$error = "";
$show_confirmations = false;
$sql_to_run = $_POST['sql_query'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Check Password
    if (($_POST['admin_password'] ?? '') !== $MIGRATION_PASSWORD) {
        $error = "Incorrect Administration Password.";
    } else {
        // 2. Scan for dangerous keywords
        $dangerous = false;
        $keywords = ['ALTER', 'DROP', 'TRUNCATE', 'DELETE', 'UPDATE'];
        foreach ($keywords as $word) {
            if (stripos($sql_to_run, $word) !== false) {
                $dangerous = true;
                break;
            }
        }

        // 3. Handle Confirmations
        if ($dangerous && (!isset($_POST['confirm_1']) || !isset($_POST['confirm_2']) || !isset($_POST['confirm_3']))) {
            $show_confirmations = true;
            $message = "⚠️ DANGEROUS SQL DETECTED: This query modifies or deletes data/structure.";
        } else {
            // 4. Execution
            try {
                $pdo->exec($sql_to_run);
                $message = "✅ Success: Query executed successfully on " . $pdo->query("SELECT DATABASE()")->fetchColumn();
                $sql_to_run = ""; // Clear after success
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MbokaHub Safe Migration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-10 font-mono">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-emerald-500 mb-6">SQL Security Guard</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 p-4 mb-6 rounded text-red-100"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="bg-blue-500/20 border border-blue-500 p-4 mb-6 rounded text-blue-100"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-gray-400">Paste SQL Query:</label>
                <textarea name="sql_query" rows="10" class="w-full bg-gray-800 border-2 border-gray-700 rounded p-4 text-emerald-400 focus:border-emerald-500 outline-none" placeholder="ALTER TABLE..."><?php echo htmlspecialchars($sql_to_run); ?></textarea>
            </div>

            <div class="space-y-2">
                <label class="block text-gray-400">Admin Password:</label>
                <input type="password" name="admin_password" class="w-full bg-gray-800 border-2 border-gray-700 rounded p-3" required>
            </div>

            <?php if ($show_confirmations): ?>
                <div class="bg-amber-500/10 border-l-4 border-amber-500 p-6 space-y-4">
                    <h3 class="font-bold text-amber-500">TRIPLE CONFIRMATION REQUIRED</h3>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="confirm_1" required>
                        <label>I have verified that I am connected to the <b>RIGHT DATABASE</b>.</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="confirm_2" required>
                        <label>I understand that <b>ALTER/DROP</b> is irreversible without a backup.</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="confirm_3" required>
                        <label>This is not the LMS database, but the <b>MBOKAHUB</b> database.</label>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="w-full py-4 rounded font-bold transition-all <?php echo $show_confirmations ? 'bg-amber-600 hover:bg-amber-500' : 'bg-emerald-600 hover:bg-emerald-500'; ?>">
                <?php echo $show_confirmations ? 'FINAL EXECUTE' : 'RUN QUERY'; ?>
            </button>
        </form>
    </div>
</body>
</html>