<?php
/**
 * Job Management - MbokaHub Admin
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';

$page_title = "Job Intelligence";
include 'includes/header.php';

// Fetch jobs with stats
try {
    $stmt = $pdo->query("
        SELECT j.*, u.full_name as client_name, 
        (SELECT COUNT(*) FROM bids b WHERE b.job_id = j.id) as bid_count
        FROM jobs j
        JOIN users u ON j.client_id = u.id
        ORDER BY j.created_at DESC
    ");
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $jobs = [];
}
?>

<header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12 text-slate-900">
    <div>
        <h2 class="text-4xl font-black tracking-tight">Job Intelligence</h2>
        <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px] mt-1">Marketplace Oversight & Audit</p>
    </div>
    
    <div class="flex items-center gap-3">
        <button class="px-6 py-3.5 bg-white border border-slate-200 text-slate-700 rounded-2xl font-bold shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
            <i class="fas fa-filter text-xs opacity-50"></i> Filter Market
        </button>
    </div>
</header>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
        <div class="relative">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Total Digital Listings</p>
            <h3 class="text-4xl font-black text-slate-900 mb-2"><?php echo count($jobs); ?></h3>
            <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full" style="width: 70%"></div>
            </div>
        </div>
    </div>
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
        <div class="relative">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Active Bidding Phase</p>
            <h3 class="text-4xl font-black text-indigo-600 mb-2">
                <?php 
                    $active = array_filter($jobs, fn($j) => $j['status'] === 'open');
                    echo count($active);
                ?>
            </h3>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> Live Signals
            </div>
        </div>
    </div>
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
        <div class="relative text-emerald-600">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Fulfillment Rate</p>
            <h3 class="text-4xl font-black mb-2 tracking-tight">94.2%</h3>
            <div class="text-xs font-bold text-slate-400">Targeting 98% (KPI)</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-0">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Project Descriptor</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Client Node</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Unit Budget</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Bids</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Market Status</th>
                    <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if (empty($jobs)): ?>
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mb-4">
                                <i class="fas fa-inbox text-2xl"></i>
                            </div>
                            <p class="text-slate-400 font-bold">No market data available</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php foreach ($jobs as $job): ?>
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="font-black text-slate-800"><?php echo htmlspecialchars($job['title']); ?></span>
                            <span class="text-xs text-slate-400 font-bold"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-[10px] font-black text-slate-400">
                                <?php echo strtoupper(substr($job['client_name'], 0, 2)); ?>
                            </div>
                            <span class="font-bold text-slate-600"><?php echo htmlspecialchars($job['client_name']); ?></span>
                        </div>
                    </td>
                    <td class="px-8 py-6 font-mono font-bold text-slate-900">
                        KES <?php echo number_format($job['budget']); ?>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <span class="px-3 py-1 bg-slate-100 rounded-lg text-[10px] font-black text-slate-500">
                            <?php echo $job['bid_count']; ?>
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <?php 
                        $statusClass = [
                            'open' => 'bg-emerald-50 text-emerald-600',
                            'closed' => 'bg-slate-100 text-slate-500',
                            'in_progress' => 'bg-blue-50 text-blue-600'
                        ][$job['status']] ?? 'bg-slate-100 text-slate-500';
                        ?>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo str_replace('bg-','bg-', explode(' ', $statusClass)[0]); ?>"></span>
                            <span class="text-[10px] font-black uppercase tracking-[0.15em] <?php echo explode(' ', $statusClass)[1]; ?>">
                                <?php echo str_replace('_', ' ', $job['status']); ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                            <button title="View Intelligence" class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center shadow-lg shadow-slate-200 hover:scale-110 active:scale-90 transition-all">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            <button title="Redact Posting" class="w-10 h-10 bg-white border border-slate-200 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-50 hover:border-rose-100 transition-all">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
