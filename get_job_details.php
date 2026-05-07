<?php
/**
 * View Job Details & Management - MbokaHub
 * Dynamic view for bids, status updates, and reviews.
 */
require_once 'includes/db_connect.php';
require_once 'includes/translations.php';
require_once 'includes/rating_helper.php';
session_start();

$page_title = "Job Details";
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = (int)$_GET['id'];
$user_role = $_SESSION['role'];

try {
    // Fetch Job Details with Category and Hirer info
    $stmt = $pdo->prepare("
        SELECT j.*, u.first_name, u.last_name, u.email as hirer_email, c.name_en as cat_name, c.icon_class 
        FROM jobs j 
        JOIN users u ON j.user_id = u.id 
        LEFT JOIN categories c ON j.category_id = c.id 
        WHERE j.id = ?
    ");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();

    if (!$job) {
        die("<div class='max-w-7xl mx-auto px-6 py-20 text-center font-black text-slate-400'>Job not found.</div>");
    }

    // Authorization: Fundis see open jobs, Hirers see their own, Assigned fundis see theirs
    $is_owner = ($job['user_id'] == $user_id);
    $is_assigned = ($job['assigned_fundi_id'] == $user_id);
    
    // Fetch Bids if Owner
    $bids = [];
    if ($is_owner) {
        $stmt_bids = $pdo->prepare("
            SELECT b.*, u.first_name, u.last_name, f.rating, f.location, f.avatar_url, f.review_count
            FROM job_bids b 
            JOIN users u ON b.fundi_id = u.id 
            JOIN fundi_profiles f ON u.id = f.user_id
            WHERE b.job_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt_bids->execute([$job_id]);
        $bids = $stmt_bids->fetchAll();
    }

    // Check if current fundi has bid
    $my_bid = null;
    if ($user_role === 'fundi') {
        $stmt_my_bid = $pdo->prepare("SELECT * FROM job_bids WHERE job_id = ? AND fundi_id = ?");
        $stmt_my_bid->execute([$job_id, $user_id]);
        $my_bid = $stmt_my_bid->fetch();
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<main class="max-w-7xl mx-auto px-4 md:px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Job Details -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[2.5rem] border border-slate-100 overflow-hidden shadow-sm">
                <!-- Status Header -->
                <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-emerald-500">
                            <i class="fas <?php echo $job['icon_class'] ?: 'fa-briefcase'; ?>"></i>
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400"><?php echo htmlspecialchars($job['cat_name'] ?? 'General'); ?></span>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border 
                        <?php echo ($job['status'] === 'open') ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100'; ?>">
                        <?php echo str_replace('_', ' ', $job['status']); ?>
                    </span>
                </div>

                <div class="p-8 md:p-10">
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 mb-6 leading-tight"><?php echo htmlspecialchars($job['title']); ?></h1>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Budget</p>
                            <p class="text-lg font-black text-slate-900">Ksh <?php echo number_format($job['budget_range'] ?? 0); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Urgency</p>
                            <p class="text-sm font-black text-slate-900 capitalize"><?php echo $job['urgency']; ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Posted</p>
                            <p class="text-sm font-black text-slate-900"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Location</p>
                            <p class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($job['location'] ?? 'Remote / TBD'); ?></p>
                        </div>
                    </div>

                    <div class="prose prose-slate max-w-none">
                        <h3 class="text-lg font-black text-slate-900 mb-4">Project Overview</h3>
                        <p class="text-slate-600 font-medium leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($job['description']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Bids Section (Hirer View) -->
            <?php if ($is_owner): ?>
            <div class="space-y-6">
                <h3 class="text-2xl font-black text-slate-900 px-2">Proposals (<?php echo count($bids); ?>)</h3>
                <?php if (empty($bids)): ?>
                    <div class="bg-white rounded-[2.5rem] p-12 text-center border border-slate-100 border-dashed">
                        <p class="text-slate-400 font-bold">Waiting for proposals from experts...</p>
                    </div>
                <?php else: ?>
                    <?php foreach($bids as $bid): ?>
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm hover:shadow-xl transition-all">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 overflow-hidden flex-shrink-0">
                                <img src="<?php echo $bid['avatar_url'] ?: 'assets/images/profiles/default.png'; ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xl font-black text-slate-900"><?php echo $bid['first_name'] . ' ' . $bid['last_name']; ?></h4>
                                    <div class="flex items-center gap-1 text-amber-400">
                                        <?php echo renderRating($bid['rating']); ?>
                                        <span class="text-xs font-black text-slate-400 ml-1">(<?php echo $bid['review_count']; ?>)</span>
                                    </div>
                                </div>
                                <p class="text-slate-500 text-sm font-medium mb-4 italic leading-relaxed">"<?php echo htmlspecialchars($bid['proposal_text']); ?>"</p>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase text-slate-400"><?php echo date('M d, g:i a', strtotime($bid['created_at'])); ?></span>
                                    <?php if ($job['status'] === 'open'): ?>
                                    <button onclick="acceptBid(<?php echo $bid['id']; ?>)" class="px-6 py-2 bg-emerald-500 text-white rounded-xl font-black text-xs hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
                                        Accept Proposal
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Bid Input (Fundi View) -->
            <?php if ($user_role === 'fundi' && !$is_assigned && $job['status'] === 'open'): ?>
            <div id="bidSection" class="bg-slate-900 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl shadow-slate-200">
                <?php if ($my_bid): ?>
                    <div class="text-center py-6">
                        <div class="w-16 h-16 bg-emerald-500/20 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-black mb-2">Proposal Submitted</h3>
                        <p class="text-slate-400 font-medium">Your request is being reviewed by the hirer. We'll notify you if accepted.</p>
                    </div>
                <?php else: ?>
                    <h3 class="text-2xl font-black mb-6">Pitch for this Project</h3>
                    <form id="bidForm" class="space-y-6">
                        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Your Proposal / Cover Message</label>
                            <textarea name="proposal_text" required rows="4" placeholder="Briefly explain why you're the best fit for this task..." 
                                class="w-full px-6 py-4 bg-white/5 border border-white/10 rounded-2xl text-white font-bold placeholder:text-slate-500 focus:ring-2 focus:ring-emerald-500/50 outline-none transition-all"></textarea>
                        </div>
                        <button type="submit" class="w-full py-5 bg-emerald-500 text-white rounded-2xl font-black text-sm hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                            Submit My Pitch
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right Column: Sidebar -->
        <div class="space-y-8">
            <!-- Client Info Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
                <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-6">About the Client</h3>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center font-black text-xl">
                        <?php echo strtoupper(substr($job['first_name'] ?? 'C', 0, 1)); ?>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900"><?php echo htmlspecialchars(($job['first_name'] ?? 'Client') . ' ' . ($job['last_name'] ?? '')); ?></h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Registered Member</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-slate-50">
                        <span class="text-xs font-bold text-slate-500">Member Since</span>
                        <span class="text-xs font-black text-slate-900">2026</span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-xs font-bold text-slate-500">Contact Method</span>
                        <span class="text-xs font-black text-slate-900">MbokaChat</span>
                    </div>
                </div>
            </div>

            <!-- Urgent Actions (If assigned/owner) -->
            <?php if (($is_owner || $is_assigned) && $job['status'] === 'in_progress'): ?>
            <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100">
                <h3 class="text-xl font-black mb-4">Active Workflow</h3>
                <p class="text-indigo-100 text-sm font-medium mb-6 leading-relaxed">This project is currently active. Once finished, <?php echo $is_owner ? 'please mark as complete to release review' : 'ask the client to verify completion'; ?>.</p>
                <?php if ($is_owner): ?>
                <button onclick="openCompletionModal()" class="w-full py-4 bg-white text-indigo-600 rounded-2xl font-black text-xs hover:bg-slate-50 transition-all">
                    Finalize & Accept Work
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- Completion & Rating Modal -->
<div id="completionModal" class="fixed inset-0 z-[150] hidden">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeCompletionModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-[3rem] shadow-2xl overflow-hidden p-8 md:p-12 animate-in zoom-in duration-300">
        <h2 class="text-2xl font-black text-slate-900 mb-2">Mark as Complete</h2>
        <p class="text-slate-500 mb-8 font-medium">How would you rate the experience with this professional?</p>
        
        <form id="completionForm" class="space-y-6">
            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
            
            <!-- Star Rating Input -->
            <div class="flex flex-col items-center gap-4">
                <div class="flex gap-2 text-3xl text-slate-200" id="star-rating-input">
                    <i class="fas fa-star cursor-pointer hover:text-amber-400 transition-colors" data-value="1"></i>
                    <i class="fas fa-star cursor-pointer hover:text-amber-400 transition-colors" data-value="2"></i>
                    <i class="fas fa-star cursor-pointer hover:text-amber-400 transition-colors" data-value="3"></i>
                    <i class="fas fa-star cursor-pointer hover:text-amber-400 transition-colors" data-value="4"></i>
                    <i class="fas fa-star cursor-pointer hover:text-amber-400 transition-colors" data-value="5"></i>
                </div>
                <input type="hidden" name="rating" id="rating-value" value="0">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest" id="rating-label">Choose a rating</p>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Your Review (Optional)</label>
                <textarea name="comment" rows="4" placeholder="Tell others about the quality of work..." 
                    class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl text-slate-900 font-bold outline-none transition-all"></textarea>
            </div>

            <button type="submit" class="w-full py-5 bg-emerald-500 text-white rounded-2xl font-black text-sm hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                Complete Project & Post Review
            </button>
        </form>

        <button onclick="closeCompletionModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- Scripts -->
<script>
function openCompletionModal() {
    document.getElementById('completionModal').classList.remove('hidden');
}

function closeCompletionModal() {
    document.getElementById('completionModal').classList.add('hidden');
}

// Star Rating Logic
const stars = document.querySelectorAll('#star-rating-input i');
const ratingInput = document.getElementById('rating-value');
const ratingLabel = document.getElementById('rating-label');
const labels = ['Wait...', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent!'];

stars.forEach(star => {
    star.addEventListener('click', () => {
        const val = parseInt(star.getAttribute('data-value'));
        ratingInput.value = val;
        ratingLabel.innerText = labels[val];
        
        stars.forEach((s, index) => {
            if (index < val) {
                s.classList.remove('text-slate-200');
                s.classList.add('text-amber-400');
            } else {
                s.classList.add('text-slate-200');
                s.classList.remove('text-amber-400');
            }
        });
    });
});

const completionForm = document.getElementById('completionForm');
if (completionForm) {
    completionForm.onsubmit = async (e) => {
        e.preventDefault();
        if (ratingInput.value === "0") {
            alert('Please select a star rating.');
            return;
        }

        const formData = new FormData(completionForm);
        try {
            const resp = await fetch('ajax/complete_job.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error('Completion error:', err);
        }
    };
}

const bidForm = document.getElementById('bidForm');
if (bidForm) {
    bidForm.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(bidForm);
        
        try {
            const resp = await fetch('ajax/submit_bid.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error('Bid error:', err);
        }
    };
}

async function acceptBid(bidId) {
    if (!confirm('Are you sure you want to hire this professional? This will move the project to In Progress.')) return;
    
    try {
        const resp = await fetch('ajax/accept_bid.php', {
            method: 'POST',
            body: new URLSearchParams({ bid_id: bidId })
        });
        const data = await resp.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (err) {
        console.error('Accept error:', err);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
