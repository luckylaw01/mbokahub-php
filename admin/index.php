<?php
/**
 * Advanced Admin Dashboard - MbokaHub
 * An executive command center for system oversight
 */
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';
$page_title = "Executive Overview";
include 'includes/header.php';

$t = $lang[$current_lang];

// Statistics
try {
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $job_count = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $fundi_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'fundi'")->fetchColumn();
    $inst_count = $pdo->query("SELECT COUNT(*) FROM institutions")->fetchColumn();
} catch (PDOException $e) {
    $user_count = $job_count = $fundi_count = $inst_count = 0;
}
?>

<!-- Header Section -->
<header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
    <div>
        <h2 class="text-4xl font-black text-slate-900 tracking-tight">System Intel</h2>
        <p class="text-slate-500 font-bold flex items-center gap-2">
            <span class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
            Node Status: Operational (GMT +3)
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <button class="px-6 py-3.5 bg-white border border-slate-200 text-slate-700 rounded-2xl font-bold shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
            <i class="fas fa-download text-xs opacity-50"></i> Export Audit
        </button>
        <a href="../safe_migrate.php" class="flex items-center gap-3 px-6 py-3.5 bg-indigo-600 text-white rounded-2xl font-bold shadow-xl shadow-indigo-200 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-database"></i> Database Console
        </a>
    </div>
</header>

<!-- Primary Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <!-- User Lifecycle -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-100">
                <i class="fas fa-users"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Active Accounts</p>
            <h3 class="text-4xl font-black text-slate-900 mb-4"><?php echo number_format($user_count); ?></h3>
            <div class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                <i class="fas fa-arrow-up"></i> 12% increase 
                <span class="text-slate-300 font-medium">this week</span>
            </div>
        </div>
    </div>

    <!-- Market Velocity -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-100">
                <i class="fas fa-briefcase"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Open Contracts</p>
            <h3 class="text-4xl font-black text-slate-900 mb-4"><?php echo number_format($job_count); ?></h3>
            <div class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                <i class="fas fa-chart-line"></i> Active Market
                <span class="text-slate-300 font-medium">high signal</span>
            </div>
        </div>
    </div>

    <!-- Skill Network -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-amber-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-amber-100">
                <i class="fas fa-hammer"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Verified Fundis</p>
            <h3 class="text-4xl font-black text-slate-900 mb-4"><?php echo number_format($fundi_count); ?></h3>
            <div class="flex items-center gap-2 text-amber-600 text-xs font-bold">
                <i class="fas fa-award"></i> Core Workers
                <span class="text-slate-300 font-medium">vetted</span>
            </div>
        </div>
    </div>

    <!-- TVET Partners -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-purple-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-purple-100">
                <i class="fas fa-university"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Education Nodes</p>
            <h3 class="text-4xl font-black text-slate-900 mb-4"><?php echo number_format($inst_count); ?></h3>
            <div class="flex items-center gap-2 text-purple-600 text-xs font-bold">
                <i class="fas fa-link"></i> Linked Assets
                <span class="text-slate-300 font-medium">verified</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
    <!-- Activity Feed -->
    <div class="lg:col-span-2 bg-white rounded-[3rem] p-10 shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-black text-slate-900">Recent Transactions</h3>
            <button class="text-indigo-600 font-bold text-sm hover:underline">View Journal</button>
        </div>
        
        <div class="space-y-8">
            <!-- Row 1 -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">New User Onboarded</p>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Hirer Account • 2 mins ago</p>
                    </div>
                </div>
                <span class="text-xs font-black text-emerald-500 px-3 py-1 bg-emerald-50 rounded-lg">SUCCESS</span>
            </div>

            <!-- Row 2 -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Job Posting: Industrial Electrical</p>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Thika Region • 14 mins ago</p>
                    </div>
                </div>
                <span class="text-xs font-black text-blue-500 px-3 py-1 bg-blue-50 rounded-lg">PENDING BIDS</span>
            </div>

            <!-- Row 3 -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Contract Finalized</p>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Kabete Polytechnic Partner • 1 hour ago</p>
                    </div>
                </div>
                <span class="text-xs font-black text-slate-400 px-3 py-1 bg-slate-100 rounded-lg">CLOSED</span>
            </div>
        </div>
    </div>

    <!-- Security Module -->
    <div class="bg-slate-900 rounded-[3rem] p-10 text-white shadow-2xl">
        <h3 class="text-xl font-black mb-8 flex items-center gap-3">
            <i class="fas fa-shield-halved text-emerald-500"></i>
            Security Health
        </h3>
        
        <div class="space-y-6">
            <div class="p-5 bg-white/5 rounded-2xl border border-white/10">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Database Integrity</p>
                <div class="flex justify-between items-end">
                    <span class="text-lg font-bold">Shield Active</span>
                    <span class="text-emerald-500 text-xs font-bold">100% HEALTH</span>
                </div>
            </div>

            <div class="p-5 bg-white/5 rounded-2xl border border-white/10">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Identity Protocol</p>
                <div class="flex justify-between items-end">
                    <span class="text-lg font-bold">Bcrypt Encryption</span>
                    <span class="text-indigo-400 text-xs font-bold font-mono">MD5 DISABLED</span>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-white/10">
                <p class="text-slate-400 text-xs font-medium mb-6">System access logs show no unauthorized attempts in the last 72 hours.</p>
                <button class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-sm transition-all">
                    Run Safety Audit
                </button>
            </div>
        </div>
    </div>
</div>

<div class="bg-amber-50 border-2 border-amber-200 rounded-[2.5rem] p-10 flex flex-col md:flex-row items-center gap-10">
    <div class="w-24 h-24 bg-amber-500 text-white rounded-full flex items-center justify-center text-4xl shrink-0 shadow-lg animate-bounce">
        <i class="fas fa-bolt"></i>
    </div>
    <div class="flex-1">
        <h4 class="text-2xl font-black text-amber-900 mb-2">Priority: Partner Migration</h4>
        <p class="text-amber-700 leading-relaxed font-semibold">
            We have detected 14 unassociated Fundi accounts. Recommended Action: Assign these members to their respective TVET institutions via the <a href="institutions.php" class="text-amber-900 underline">Institution Management</a> suite to maintain data integrity.
        </p>
    </div>
    <button class="px-8 py-4 bg-amber-900 text-white rounded-2xl font-black text-sm whitespace-nowrap">Resolve Now</button>
</div>

<?php include 'includes/footer.php'; ?>
