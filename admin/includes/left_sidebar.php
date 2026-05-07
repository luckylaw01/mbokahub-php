<?php
/**
 * Admin Sidebar - Shared Navigation
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-white border-r border-slate-200 flex flex-col">
    <div class="p-6">
        <a href="../index.php" class="flex items-center gap-3">
            <div class="vibrant-gradient text-white p-2 rounded-xl shadow-lg">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="text-xl font-bold tracking-tight">Mboka<span class="text-emerald-500">Hub</span></h1>
        </a>
    </div>
    
    <nav class="flex-1 px-4 space-y-1">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'index.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="users.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'users.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-users"></i> User Management
        </a>
        <a href="institutions.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'institutions.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-university"></i> Institution Management
        </a>
        <a href="jobs.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'jobs.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-briefcase"></i> Job Management
        </a>
        <a href="payments.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'payments.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-credit-card"></i> Payments
        </a>
        <a href="communications.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'communications.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-comment-dots"></i> Communications
        </a>
        <a href="feeds.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'feeds.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-rss"></i> Feed Management
        </a>
        <a href="community.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'community.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-users-rectangle"></i> Community
        </a>
        <a href="feedback.php" class="flex items-center gap-3 px-4 py-3 <?php echo $current_page == 'feedback.php' ? 'bg-emerald-50 text-emerald-600 rounded-xl font-bold' : 'text-slate-500 hover:bg-slate-50 rounded-xl transition-all'; ?>">
            <i class="fas fa-bullhorn"></i> Feedback
        </a>
    </nav>

    <div class="p-4 border-t border-slate-100">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-rose-500 hover:bg-rose-50 rounded-xl transition-all font-bold">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
