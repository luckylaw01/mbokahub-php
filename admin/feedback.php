<?php
/**
 * Placeholder Template - MbokaHub Admin
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';

$page_name = basename($_SERVER['PHP_SELF'], ".php");
$page_title = ucwords(str_replace('_', ' ', $page_name));
include 'includes/header.php';
?>

<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-32 h-32 bg-slate-100 text-slate-300 rounded-[3rem] flex items-center justify-center mb-8 animate-pulse">
        <i class="fas fa-hammer text-5xl"></i>
    </div>
    
    <h2 class="text-4xl font-black text-slate-900 mb-4 tracking-tight">
        <?php echo $page_title; ?> Module
    </h2>
    
    <div class="bg-indigo-50 text-indigo-600 px-6 py-2 rounded-full font-black uppercase tracking-[0.2em] text-[10px] mb-8 border border-indigo-100">
        Construction in Progress
    </div>

    <p class="text-slate-500 max-w-md mx-auto leading-relaxed font-medium">
        We are currently architecting the <b><?php echo $page_title; ?></b> suite. This component will feature real-time analytics, automated workflows, and secure management tools.
    </p>

    <div class="mt-12 flex gap-4">
        <a href="index.php" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl shadow-slate-200 hover:-translate-y-1 transition-all active:scale-95">
            Return to Dashboard
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
