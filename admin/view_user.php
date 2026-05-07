<?php
/**
 * Admin - View User Profile
 */
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: users.php");
    exit();
}

// Fetch User Data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found in system.");
    }

    // Role safety
    $role_slug = strtolower(trim($user['role']));

    // Fetch Role Specific Data
    $profile = null;
    if ($role_slug === 'fundi') {
        $stmt = $pdo->prepare("SELECT * FROM fundi_profiles WHERE user_id = ?");
        $stmt->execute([$id]);
        $profile = $stmt->fetch();
    } elseif ($role_slug === 'contractor') {
        $stmt = $pdo->prepare("SELECT * FROM contractor_profiles WHERE user_id = ?");
        $stmt->execute([$id]);
        $profile = $stmt->fetch();
    }

    // Fetch Stats
    $job_count = 0;
    if ($role_slug === 'hirer') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE user_id = ?");
        $stmt->execute([$id]);
        $job_count = $stmt->fetchColumn();
    } elseif ($role_slug === 'fundi' || $role_slug === 'contractor') {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_bids WHERE fundi_id = ?");
            $stmt->execute([$id]);
            $job_count = $stmt->fetchColumn(); 
        } catch (Exception $e) {
            $job_count = 0;
        }
    }

} catch (PDOException $e) {
    die("Error fetching user details");
}

$page_title = "Member Audit: " . htmlspecialchars($user['first_name']);
include 'includes/header.php';
?>

<div class="mb-8">
    <a href="users.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-100 rounded-2xl text-slate-500 hover:text-slate-900 shadow-sm transition-all hover:-translate-x-1">
        <i class="fas fa-arrow-left text-xs"></i>
        <span class="text-sm font-black uppercase tracking-wider">Back to Roster</span>
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <!-- Left Col: Identity Card -->
    <div class="md:col-span-1 space-y-6">
        <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-indigo-500 to-purple-600 opacity-10"></div>
            
            <div class="relative">
                <div class="w-28 h-28 rounded-[2.5rem] bg-indigo-600 text-white flex items-center justify-center text-4xl font-black mx-auto mb-6 shadow-xl shadow-indigo-200">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <h2 class="text-2xl font-black text-slate-900 mb-1">
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h2>
                <p class="text-slate-400 font-bold text-sm mb-6">@<?php echo htmlspecialchars($user['user_name'] ?? 'member'); ?></p>
                
                <div class="flex flex-col gap-2">
                    <span class="px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em] bg-slate-900 text-white inline-block">
                        <?php echo $user['role']; ?>
                    </span>
                    <span class="text-[11px] font-bold text-slate-400">UID: #<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>

        <!-- System Stats -->
        <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-slate-200">
            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-6">Activity Footprint</h4>
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <span class="text-slate-400 font-bold text-sm"><?php echo $role_slug === 'hirer' ? 'Jobs Posted' : 'Bids Placed'; ?></span>
                    <span class="text-2xl font-black"><?php echo $job_count; ?></span>
                </div>
                <div class="w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500" style="width: <?php echo min(100, $job_count * 10); ?>%"></div>
                </div>
                <div class="pt-4 border-t border-slate-800">
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2">Registration Log</p>
                    <p class="text-sm font-bold"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Col: Detailed Records -->
    <div class="md:col-span-2 space-y-8">
        <div class="bg-white rounded-[3rem] p-10 shadow-sm border border-slate-100">
            <div class="flex items-center gap-4 mb-10">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-900">Security & Profile</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Verified Email</p>
                    <p class="text-lg font-bold text-slate-700"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone Network</p>
                    <p class="text-lg font-bold text-slate-700"><?php echo htmlspecialchars($user['phone'] ?? 'Not Linked'); ?></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Digital Signature</p>
                    <p class="text-[13px] font-mono text-slate-400 break-all"><?php echo substr($user['password_hash'] ?? 'No Hash Available', 0, 30); ?>...</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Access Status</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                        <span class="text-lg font-bold text-slate-700">Active Node</span>
                    </div>
                </div>
            </div>

            <?php if ($profile): ?>
            <div class="mt-12 pt-12 border-t border-slate-100">
                <h4 class="text-lg font-black text-slate-900 mb-6">Professional Credentials</h4>
                <div class="bg-slate-50 p-6 rounded-3xl border border-dashed border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Skill Category</p>
                            <p class="font-bold"><?php echo htmlspecialchars($profile['profession'] ?? $profile['specialization'] ?? 'General Specialist'); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Service Radius</p>
                            <p class="font-bold"><?php echo htmlspecialchars($profile['location'] ?? 'Global'); ?></p>
                        </div>
                    </div>
                    <?php if (isset($profile['bio'])): ?>
                    <div class="mt-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Professional Abstract</p>
                        <p class="text-sm text-slate-600 leading-relaxed italic">"<?php echo htmlspecialchars($profile['bio']); ?>"</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="flex gap-4">
            <button onclick="window.history.back()" class="flex-1 py-5 bg-white border border-slate-200 text-slate-600 rounded-[2rem] font-black text-sm hover:bg-slate-50 transition-all shadow-sm">
                Exit Audit
            </button>
            <button class="flex-1 py-5 bg-rose-50 text-rose-600 rounded-[2rem] font-black text-sm hover:bg-rose-100 transition-all border border-rose-100">
                Suspend Account
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
