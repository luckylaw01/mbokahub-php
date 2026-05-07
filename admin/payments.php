<?php
/**
 * Payment & Ledger - MbokaHub Admin
 * Managing the financial flow of the ecosystem
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';

$page_title = "Treasury & Ledger";
include 'includes/header.php';

// In a real scenario, we would have a payments table. 
// For now, we simulate financial metrics based on jobs.
try {
    $total_volume = $pdo->query("SELECT SUM(budget) FROM jobs")->fetchColumn() ?? 0;
    $escrow_stmt = $pdo->query("SELECT SUM(budget) FROM jobs WHERE status = 'in_progress'");
    $escrow_volume = $escrow_stmt->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $total_volume = $escrow_volume = 0;
}
?>

<header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
    <div>
        <h2 class="text-4xl font-black text-slate-900 tracking-tight">Treasury & Ledger</h2>
        <p class="text-slate-500 font-bold flex items-center gap-2">
            <i class="fas fa-shield-check text-emerald-500 text-xs"></i>
            Financial Integrity Module v1.0
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <button class="px-6 py-3.5 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-100 flex items-center gap-2 hover:scale-[1.02] transition-transform">
            <i class="fas fa-file-invoice-dollar"></i> Global Settlement
        </button>
    </div>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <!-- Total Volume -->
    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-indigo-50 rounded-full opacity-40 group-hover:scale-125 transition-transform duration-700"></div>
        <div class="relative">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Market Throughput</p>
            <h3 class="text-4xl font-black text-slate-900 mb-2">KES <?php echo number_format($total_volume); ?></h3>
            <p class="text-xs font-bold text-indigo-500">Gross Platform Volume</p>
        </div>
    </div>

    <!-- Escrow -->
    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-amber-50 rounded-full opacity-40 group-hover:scale-125 transition-transform duration-700"></div>
        <div class="relative">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Funds in Escrow</p>
            <h3 class="text-4xl font-black text-amber-600 mb-2">KES <?php echo number_format($escrow_volume); ?></h3>
            <p class="text-xs font-bold text-amber-500">Secured & Committed</p>
        </div>
    </div>

    <!-- Service Fees -->
    <div class="bg-slate-900 p-10 rounded-[3rem] text-white shadow-2xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent"></div>
        <div class="relative">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Platform Revenue</p>
            <h3 class="text-4xl font-black text-white mb-2">KES <?php echo number_format($total_volume * 0.10); ?></h3>
            <p class="text-xs font-bold text-emerald-400">10% Protocol Fee</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-[3.5rem] p-12 border border-slate-100">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <h3 class="text-2xl font-black text-slate-900">Transaction Journal</h3>
        <div class="flex bg-slate-100 p-1.5 rounded-2xl">
            <button class="px-6 py-2.5 bg-white text-slate-900 rounded-xl font-bold shadow-sm text-sm">All Time</button>
            <button class="px-6 py-2.5 text-slate-500 font-bold text-sm">Pending</button>
            <button class="px-6 py-2.5 text-slate-500 font-bold text-sm">Payouts</button>
        </div>
    </div>

    <!-- Empty Ledger State -->
    <div class="py-20 flex flex-col items-center justify-center text-center">
        <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mb-8 border border-slate-100">
            <i class="fas fa-receipt text-4xl"></i>
        </div>
        <h4 class="text-xl font-black text-slate-900 mb-2">No Verified Transactions</h4>
        <p class="text-slate-400 font-bold max-w-xs leading-relaxed">The ledger is currently clear. Real-time transactions will appear here once contracts are finalized.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
