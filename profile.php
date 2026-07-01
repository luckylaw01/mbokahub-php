<?php
require_once "includes/translations.php";
require_once "includes/db_connect.php";
require_once "includes/rating_helper.php";
session_start();

// Auth Check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$current_lang = $_SESSION["current_lang"] ?? "en";
$t = $lang[$current_lang];
$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["name"];
$role = $_SESSION["role"];

// Fetch Categories for the edit modal
$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en ASC");
$all_categories = $categories_stmt->fetchAll();

// Fetch real profile data
try {
    if ($role === "fundi") {
        $stmt = $pdo->prepare("
            SELECT u.*, f.*, c.name_en as cat_name 
            FROM users u 
            JOIN fundi_profiles f ON u.id = f.user_id 
            LEFT JOIN categories c ON f.category_id = c.id
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $full_profile = $stmt->fetch();

        // Phase 4: Fetch completed jobs count
        $stmt_jobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE assigned_fundi_id = ? AND status = 'completed'");
        $stmt_jobs->execute([$user_id]);
        $completed_count = $stmt_jobs->fetchColumn();
        
        $profile = [
            "rating" => $full_profile["rating"] ?? 0.0,
            "reviews" => $full_profile["review_count"] ?? 0,
            "completed" => $completed_count,
            "joined" => date("M Y", strtotime($full_profile["created_at"])),
            "specialty" => $full_profile["cat_name"] ?? ($role === "fundi" ? "Artisan" : "Client"),
            "location" => $full_profile["location"] ?? "Kenya",
            "bio" => $full_profile["bio"] ?? "",
            "avatar" => $full_profile["avatar_url"] ?? null,
            "tvet_level" => $full_profile["tvet_level"] ?? "student",
            "is_verified" => $full_profile["is_verified"] ?? 0,
            "resume_url" => $full_profile["resume_url"] ?? null,
            "skills" => $full_profile["skills"] ?? ""
        ];
    } elseif ($role === "contractor") {
        $stmt = $pdo->prepare("
            SELECT u.*, cp.* 
            FROM users u 
            JOIN contractor_profiles cp ON u.id = cp.user_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $full_profile = $stmt->fetch();
        
        $profile = [
            "rating" => 0,
            "reviews" => 0,
            "completed" => 0,
            "joined" => date("M Y", strtotime($full_profile["created_at"])),
            "specialty" => "Professional Contractor",
            "company_name" => $full_profile["company_name"] ?? "Independent Contractor",
            "location" => "Kenya",
            "bio" => $full_profile["business_description"] ?? "",
            "avatar" => null,
            "is_verified" => 0,
            "skills" => ""
        ];
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $full_profile = $stmt->fetch();
        $profile = [
            "rating" => 0,
            "reviews" => 0,
            "completed" => 0,
            "joined" => date("M Y", strtotime($full_profile["created_at"])),
            "specialty" => "Property Manager",
            "location" => "Nairobi",
            "bio" => "",
            "avatar" => null,
            "is_verified" => 0,
            "skills" => ""
        ];

        // Phase 3: Fetch active jobs for hirers
        $stmt = $pdo->prepare("
            SELECT j.*, f.first_name as fundi_name, c.name_en as cat_name
            FROM jobs j
            LEFT JOIN users f ON j.assigned_fundi_id = f.id
            LEFT JOIN categories c ON j.category_id = c.id
            WHERE j.user_id = ? AND j.status != 'completed' AND j.status != 'cancelled'
            ORDER BY j.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $active_jobs = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Error fetching profile: " . $e->getMessage());
}

// Fetch portfolio items
$portfolio_items = [];
$experiences = [];
$certifications = [];
$gigs = [];
$education = [];
$references = [];
$achievements = [];

if ($role === "fundi") {
    try {
        $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $portfolio_items = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM experiences WHERE user_id = ? ORDER BY start_date DESC");
        $stmt->execute([$user_id]);
        $experiences = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM certifications WHERE user_id = ? ORDER BY issue_date DESC");
        $stmt->execute([$user_id]);
        $certifications = $stmt->fetchAll();

        // Phase 3: Fetch Reviews
        $stmt = $pdo->prepare("
            SELECT r.*, u.first_name, u.last_name, u.role
            FROM reviews r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.reviewee_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $reviews_data = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM gigs WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $gigs = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ? ORDER BY start_date DESC");
        $stmt->execute([$user_id]);
        $education = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM character_references WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $references = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY date_awarded DESC, created_at DESC");
        $stmt->execute([$user_id]);
        $achievements = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Silently fail or log
    }
}

$page_title = $user_name . " - MbokaHub Profile";

include "includes/header.php";
?>


    <main class="max-w-5xl mx-auto p-4 md:p-8">
        <!-- Profile Header Card -->
        <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-slate-200 border border-slate-50 relative overflow-hidden mb-8">
            <div class="absolute top-0 right-0 w-64 h-64 vibrant-gradient opacity-10 blur-3xl -mr-32 -mt-32"></div>
            
            <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                <div class="relative group">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-[2.5rem] bg-slate-100 flex items-center justify-center text-4xl md:text-5xl font-black text-slate-300 border-4 border-white shadow-xl overflow-hidden">
                        <?php if ($profile["avatar"]): ?>
                            <img src="<?php echo htmlspecialchars($profile["avatar"]); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo substr($user_name, 0, 1); ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($role === "fundi"): ?>
                    <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white w-10 h-10 rounded-2xl flex items-center justify-center border-4 border-white shadow-lg">
                        <i class="fas fa-check text-xs"></i>
                    </div>
                    <?php elseif ($role === "contractor"): ?>
                    <div class="absolute -bottom-2 -right-2 bg-blue-500 text-white w-10 h-10 rounded-2xl flex items-center justify-center border-4 border-white shadow-lg">
                        <i class="fas fa-shield-halved text-xs"></i>
                    </div>
                    <?php endif; ?>

                    <!-- Quick Upload Overlay (Shown on Hover) -->
                    <label for="avatar-input" class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-all cursor-pointer flex items-center justify-center rounded-[2.5rem] text-white overflow-hidden">
                        <i class="fas fa-camera text-2xl"></i>
                        <input type="file" id="avatar-input" class="hidden" accept="image/*" onchange="uploadAvatar(this)">
                    </label>
                </div>

                <div class="text-center md:text-left flex-1">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-2">
                        <?php echo htmlspecialchars($user_name); ?>
                        <?php if ($profile["is_verified"]): ?>
                            <i class="fas fa-check-circle text-blue-500 text-xl md:text-2xl ml-1" title="Verified Account"></i>
                        <?php endif; ?>
                    </h2>
                    <?php if ($role === "contractor"): ?>
                        <p class="text-emerald-600 font-black mb-2 flex items-center justify-center md:justify-start gap-2 text-lg">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($profile["company_name"]); ?>
                        </p>
                    <?php endif; ?>
                    <p class="text-slate-500 font-bold mb-6 flex items-center justify-center md:justify-start gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-hammer text-emerald-500"></i>
                        <?php echo $profile["specialty"]; ?>
                        <span class="text-slate-200">|</span>
                        <i class="fas fa-location-dot text-blue-500"></i>
                         <span id="display-location"><?php echo $profile["location"]; ?></span>
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1"><?php echo $t["trust_index"] ?? "Trust Index"; ?></span>
                            <?php echo renderRating($profile["rating"], $profile["reviews"]); ?>
                        </div>
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">Joined</span>
                            <span class="text-xl font-black text-slate-900"><?php echo $profile["joined"]; ?></span>
                        </div>
                        <?php if ($role === "fundi"): ?>
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">Projects Done</span>
                            <span class="text-xl font-black text-slate-900"><?php echo $profile["completed"]; ?></span>
                        </div>
                        <div class="bg-indigo-50/50 px-6 py-3 rounded-2xl border border-indigo-100">
                            <span class="block text-[10px] font-black text-indigo-400 uppercase tracking-tighter mb-1">TVET Level</span>
                            <span class="text-xs font-black text-indigo-600 uppercase"><?php echo htmlspecialchars($profile["tvet_level"]); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-3 w-full md:w-auto">
                    <button onclick="openEditProfile()" class="w-full md:w-48 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl hover:scale-105 transition-all">Edit Profile</button>
                    
                    <!-- Resume / CV Auto-Generation & Download -->
                    <?php if ($role === "fundi"): ?>
                    <a href="generate_resume.php?id=<?php echo $user_id; ?>" target="_blank" class="w-full md:w-48 py-4 bg-indigo-600 text-white text-center rounded-2xl font-bold shadow-xl hover:scale-105 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-file-pdf text-rose-300"></i> Download Resume
                    </a>
                    <?php endif; ?>

                    <!-- Shareable Portfolio Link -->
                    <?php if ($role === "fundi"): ?>
                    <button onclick="copyPortfolioLink()" class="w-full md:w-48 py-4 bg-emerald-50 text-emerald-600 rounded-2xl font-bold hover:bg-emerald-100 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-share-nodes"></i> Share Portfolio
                    </button>
                    <?php endif; ?>
                    <a href="logout.php" class="w-full md:w-48 py-4 bg-rose-50 text-rose-600 text-center rounded-2xl font-bold hover:bg-rose-100 transition-all">Sign Out</a>
                </div>
            </div>

            <?php if ($role === "fundi" && $profile["bio"]): ?>
            <div class="mt-8 pt-8 border-t border-slate-100">
                <p class="text-slate-600 leading-relaxed text-sm max-w-2xl italic" id="display-bio">
                    "<?php echo htmlspecialchars($profile["bio"]); ?>"
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Edit Profile Modal Overlay -->
        <div id="edit-profile-modal" class="fixed inset-0 z-[120] hidden overflow-y-auto no-scrollbar">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeEditProfile()"></div>
            <div class="relative w-full min-h-screen flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl overflow-hidden p-8 md:p-12 animate-in fade-in zoom-in duration-300">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-slate-900">Edit Profile</h3>
                        <button onclick="closeEditProfile()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-rose-500 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="edit-profile-form" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($full_profile["first_name"]); ?>" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($full_profile["last_name"]); ?>" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($profile["location"]); ?>" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Phone Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($full_profile["phone"] ?? ""); ?>" placeholder="e.g. +254 712 345 678" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all">
                        </div>

                        <?php if ($role === "fundi"): ?>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Category / Specialization</label>
                            <select name="category_id" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                                <option value="">Select a Category</option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($full_profile['category_id']) && $full_profile['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">TVET Certification Level</label>
                            <select name="tvet_level" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                                <option value="None" <?php echo ($full_profile['tvet_level'] == 'None') ? 'selected' : ''; ?>>Not Certified / Level 0</option>
                                <option value="Level 1" <?php echo ($full_profile['tvet_level'] == 'Level 1') ? 'selected' : ''; ?>>TVET Level 1 (Artisan)</option>
                                <option value="Level 2" <?php echo ($full_profile['tvet_level'] == 'Level 2') ? 'selected' : ''; ?>>TVET Level 2 (Craft)</option>
                                <option value="Level 3" <?php echo ($full_profile['tvet_level'] == 'Level 3') ? 'selected' : ''; ?>>TVET Level 3 (Diploma)</option>
                                <option value="Level 4" <?php echo ($full_profile['tvet_level'] == 'Level 4') ? 'selected' : ''; ?>>TVET Level 4 (Technical Diploma)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Skills (Comma-separated)</label>
                            <input type="text" name="skills" value="<?php echo htmlspecialchars($profile["skills"]); ?>" placeholder="e.g. Plumbing, Leak Detection, Pipe Threading" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all">
                        </div>
                        <?php endif; ?>

                        <?php if ($role === "fundi"): ?>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Bio / Professional Summary</label>
                            <textarea name="bio" rows="4" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none transition-all"><?php echo htmlspecialchars($profile["bio"]); ?></textarea>
                        </div>
                        <?php endif; ?>

                        <div class="pt-4 flex gap-4">
                            <button type="submit" class="flex-1 py-4 bg-emerald-500 text-white rounded-2xl font-bold shadow-xl shadow-emerald-200 hover:scale-105 active:scale-95 transition-all">Save Changes</button>
                            <button type="button" onclick="closeEditProfile()" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-200 transition-all">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Portfolio & Professional Info (If Fundi) -->
        <?php if ($role === "fundi"): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left Side: Portfolio Gallery (Wide) -->
            <div class="md:col-span-2 space-y-8">
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900"><?php echo $t["view_portfolio"] ?? "Gallery"; ?></h3>
                        <button onclick="openAddPortfolio()" class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php if (empty($portfolio_items)): ?>
                        <div onclick="openAddPortfolio()" class="aspect-square bg-slate-50 rounded-[2.5rem] border-4 border-dashed border-slate-100 flex flex-col items-center justify-center group cursor-pointer hover:bg-white hover:border-emerald-500/20 transition-all gap-4">
                            <i class="fas fa-camera text-slate-200 group-hover:text-emerald-500 text-3xl"></i>
                            <span class="text-[10px] font-black uppercase text-slate-400">Add Project</span>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($portfolio_items as $item): ?>
                        <div class="aspect-square bg-slate-100 rounded-[2.5rem] overflow-hidden relative group cursor-pointer shadow-lg">
                           <img src="<?php echo htmlspecialchars($item["image_url"]); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                           <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 to-transparent opacity-0 group-hover:opacity-100 transition-all flex flex-col justify-end p-6">
                               <span class="text-white font-black text-sm mb-1"><?php echo htmlspecialchars($item["title"]); ?></span>
                               <span class="text-white/60 text-[10px] uppercase tracking-widest"><?php echo date("M Y", strtotime($item["completion_date"])); ?></span>
                           </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Experience</h3>
                        <button onclick="openAddExperience()" class="w-10 h-10 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($experiences as $exp): ?>
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-50 shadow-sm flex items-start gap-4">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-slate-900"><?php echo htmlspecialchars($exp["role"]); ?></h4>
                                <p class="text-xs font-bold text-slate-500 mb-2"><?php echo htmlspecialchars($exp["company"]); ?> • <?php echo date("M Y", strtotime($exp["start_date"])); ?> - <?php echo $exp["end_date"] ? date("M Y", strtotime($exp["end_date"])) : "Present"; ?></p>
                                <p class="text-xs text-slate-400 leading-relaxed"><?php echo htmlspecialchars($exp["description"]); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Phase 3: Reviews System -->
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Client Reviews</h3>
                        <div class="flex items-center gap-2 bg-emerald-50 px-4 py-2 rounded-2xl">
                            <span class="text-emerald-700 font-black"><?php echo number_format($profile["rating"], 1); ?></span>
                            <?php echo renderRating($profile["rating"]); ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <?php if (empty($reviews_data)): ?>
                        <div class="py-12 text-center bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-100">
                            <i class="fas fa-comment-slash text-3xl text-slate-200 mb-4"></i>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No reviews yet</p>
                        </div>
                        <?php else: ?>
                            <?php foreach ($reviews_data as $rev): ?>
                            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-50 shadow-sm hover:shadow-md transition-all">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 vibrant-gradient rounded-full flex items-center justify-center text-white font-bold text-xs">
                                            <?php echo substr($rev['first_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']); ?></h5>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase"><?php echo htmlspecialchars($rev['role']); ?> • <?php echo date("M Y", strtotime($rev['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex gap-0.5 text-amber-400 text-[10px]">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $rev['rating'] ? '' : 'text-slate-200'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-600 leading-relaxed italic">
                                    "<?php echo htmlspecialchars($rev['comment']); ?>"
                                </p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Education Section -->
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Education</h3>
                        <button onclick="openAddEducation()" class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center hover:bg-indigo-500 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($education)): ?>
                        <div class="text-center p-8 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                             <p class="text-[10px] font-black uppercase text-slate-300">No education listings added yet</p>
                        </div>
                        <?php else: ?>
                            <?php foreach ($education as $edu): ?>
                            <div class="bg-white p-6 rounded-[2rem] border border-slate-50 shadow-sm flex items-start gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-indigo-500 shrink-0">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-black text-slate-900"><?php echo htmlspecialchars($edu["credential"]); ?></h4>
                                    <p class="text-xs font-bold text-slate-500 mb-2"><?php echo htmlspecialchars($edu["institution"]); ?> • <?php echo date("M Y", strtotime($edu["start_date"])); ?> - <?php echo $edu["end_date"] ? date("M Y", strtotime($edu["end_date"])) : "Present"; ?></p>
                                    <?php if ($edu["description"]): ?>
                                    <p class="text-xs text-slate-400 leading-relaxed"><?php echo htmlspecialchars($edu["description"]); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Achievements Section -->
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Achievements</h3>
                        <button onclick="openAddAchievement()" class="w-10 h-10 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($achievements)): ?>
                        <div class="text-center p-8 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                             <p class="text-[10px] font-black uppercase text-slate-300">No achievements listed yet</p>
                        </div>
                        <?php else: ?>
                            <?php foreach ($achievements as $ach): ?>
                            <div class="bg-white p-6 rounded-[2rem] border border-slate-50 shadow-sm flex items-start gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-amber-500 shrink-0">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-black text-slate-900"><?php echo htmlspecialchars($ach["title"]); ?></h4>
                                    <?php if ($ach["date_awarded"]): ?>
                                    <p class="text-xs font-bold text-slate-400 mb-2">Awarded: <?php echo date("M Y", strtotime($ach["date_awarded"])); ?></p>
                                    <?php endif; ?>
                                    <p class="text-xs text-slate-500 leading-relaxed"><?php echo htmlspecialchars($ach["description"]); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Right Side: Certs & Skills -->
            <div class="space-y-8">
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Certifications</h3>
                        <button onclick="openAddCert()" class="w-10 h-10 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($certifications as $cert): ?>
                        <div class="bg-amber-50/50 p-6 rounded-[2rem] border border-amber-50 shadow-sm relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-400/10 rounded-full group-hover:scale-150 transition-all"></div>
                            <h4 class="font-black text-slate-900 text-sm mb-1"><?php echo htmlspecialchars($cert["title"]); ?></h4>
                            <p class="text-[10px] font-bold text-amber-700 uppercase tracking-widest"><?php echo htmlspecialchars($cert["institution"]); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Active Gigs</h3>
                        <button onclick="openAddGig()" class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center hover:bg-indigo-500 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-bolt"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($gigs)): ?>
                        <div class="text-center p-8 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                             <p class="text-[10px] font-black uppercase text-slate-300">No gigs listed yet</p>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($gigs as $gig): ?>
                        <div class="bg-indigo-50/30 p-5 rounded-[2.5rem] border <?php echo $gig['is_active'] ? 'border-indigo-100/50' : 'border-slate-200 opacity-75'; ?> flex items-center gap-4 group hover:bg-white transition-all cursor-pointer">
                            <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-indigo-500 overflow-hidden">
                                <?php if ($gig["image_url"]): ?>
                                    <img src="<?php echo htmlspecialchars($gig["image_url"]); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas <?php echo $gig['is_active'] ? 'fa-tools' : 'fa-check-circle'; ?> text-xl"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                    <?php echo htmlspecialchars($gig["title"]); ?>
                                    <?php if (!$gig['is_active']): ?>
                                        <span class="ml-1 text-[8px] px-2 py-0.5 bg-slate-200 text-slate-500 rounded-full uppercase">Verified Completion</span>
                                    <?php endif; ?>
                                </h4>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter">Starting at KSh <?php echo number_format($gig["price_amount"]); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Skills Section -->
                <?php if ($role === "fundi" && !empty($profile["skills"])): ?>
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">Skills</h3>
                    </div>
                    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-wrap gap-2">
                        <?php 
                        $skills_arr = array_map('trim', explode(',', $profile["skills"]));
                        foreach ($skills_arr as $skill): 
                            if (!empty($skill)):
                        ?>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100/60 px-3.5 py-2 rounded-xl text-xs font-bold"><?php echo htmlspecialchars($skill); ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Character References Section -->
                <section>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <h3 class="text-2xl font-black text-slate-900">References</h3>
                        <button onclick="openAddReference()" class="w-10 h-10 bg-slate-50 text-slate-600 rounded-2xl flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all shadow-lg active:scale-95">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($references)): ?>
                        <div class="text-center p-8 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                             <p class="text-[10px] font-black uppercase text-slate-300">No references listed yet</p>
                        </div>
                        <?php else: ?>
                            <?php foreach ($references as $ref): ?>
                            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                                <div class="absolute -right-4 -top-4 w-12 h-12 bg-slate-100 rounded-full group-hover:scale-150 transition-all"></div>
                                <h4 class="font-black text-slate-900 text-sm mb-1"><?php echo htmlspecialchars($ref["name"]); ?></h4>
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">
                                    <?php echo htmlspecialchars($ref["organization"] ?: 'N/A'); ?> 
                                    <?php if ($ref["relationship"]): ?>
                                        • <?php echo htmlspecialchars($ref["relationship"]); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-3">
                                    <i class="fas fa-phone text-slate-300"></i>
                                    <?php echo htmlspecialchars($ref["contact_info"]); ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
        <?php endif; ?>

        <!-- Phase 3: Hirer Job Management -->
        <?php if ($role === "hirer"): ?>
        <section class="mt-12">
            <div class="flex items-center justify-between mb-8 px-2">
                <h3 class="text-2xl font-black text-slate-900">Your Active Jobs</h3>
                <a href="index.php" class="bg-emerald-500 text-white px-6 py-3 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg shadow-emerald-200">
                    <i class="fas fa-plus mr-2"></i>Post New Job
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($active_jobs)): ?>
                <div class="col-span-full py-16 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-100">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                        <i class="fas fa-briefcase text-2xl"></i>
                    </div>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No active jobs found</p>
                </div>
                <?php else: ?>
                    <?php foreach ($active_jobs as $job): ?>
                    <div class="bg-white p-8 rounded-[3rem] border border-slate-50 shadow-xl shadow-slate-200/40 relative overflow-hidden group">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-hammer"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 group-hover:text-emerald-600 transition-colors"><?php echo htmlspecialchars($job['title']); ?></h4>
                                <span class="bg-emerald-100 text-emerald-700 text-[8px] font-black px-2 py-0.5 rounded-full uppercase"><?php echo $job['status']; ?></span>
                            </div>
                        </div>

                        <div class="space-y-3 mb-8">
                            <div class="flex items-center gap-3 text-xs font-bold text-slate-500">
                                <i class="fas fa-user-circle w-4"></i>
                                <span>Expert: <?php echo $job['fundi_name'] ? htmlspecialchars($job['fundi_name']) : '<span class="text-emerald-500">Awaiting Bids</span>'; ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-bold text-slate-500">
                                <i class="fas fa-calendar-alt w-4"></i>
                                <span>Posted: <?php echo date("M d, Y", strtotime($job['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-50 flex gap-2">
                            <?php if ($job['status'] === 'in_progress' || ($job['status'] === 'open' && $job['assigned_fundi_id'])): ?>
                                <button onclick="openReviewModal(<?php echo $job['id']; ?>, '<?php echo addslashes($job['title']); ?>')" class="flex-1 py-3 bg-slate-900 text-white rounded-2xl font-bold text-xs hover:bg-emerald-600 transition-all shadow-lg">
                                    Mark Complete
                                </button>
                            <?php endif; ?>
                            <button onclick="cancelJob(<?php echo $job['id']; ?>)" class="px-4 py-3 bg-slate-50 text-slate-400 rounded-2xl font-bold text-xs hover:bg-rose-50 hover:text-rose-500 transition-all">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Review Modal -->
        <div id="review-modal" class="fixed inset-0 z-[200] hidden overflow-y-auto">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeReviewModal()"></div>
            <div class="relative w-full min-h-screen flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl p-10 animate-in zoom-in duration-300">
                    <h3 class="text-2xl font-black text-slate-900 mb-2">Rate Your Expert</h3>
                    <p id="review-job-title" class="text-sm font-bold text-emerald-500 mb-8 uppercase tracking-widest"></p>
                    
                    <form id="review-form" class="space-y-6">
                        <input type="hidden" name="job_id" id="review-job-id">
                        
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Your Rating</label>
                            <div class="flex justify-between items-center bg-slate-50 p-6 rounded-3xl">
                                <?php for($i=1; $i<=5; $i++): ?>
                                <button type="button" onclick="setStar(<?php echo $i; ?>)" class="star-btn text-2xl text-slate-200 hover:scale-125 transition-all">
                                    <i class="fas fa-star" id="star-<?php echo $i; ?>"></i>
                                </button>
                                <?php endfor; ?>
                                <input type="hidden" name="rating" id="review-rating-val" required>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Comment</label>
                            <textarea name="comment" rows="4" placeholder="How was the service? (Optional)" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-[2rem] p-6 text-sm font-bold outline-none transition-all"></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 py-4 bg-emerald-500 text-white rounded-2xl font-bold shadow-xl shadow-emerald-200 hover:scale-105 active:scale-95 transition-all">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Generic Portfolio Modal (Reuse logic) -->
    <div id="portfolio-modal" class="fixed inset-0 z-[130] hidden overflow-y-auto no-scrollbar">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closePortfolioModal()"></div>
        <div class="relative w-full min-h-screen flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-[3rem] shadow-2xl overflow-hidden p-8 md:p-12">
                <div class="flex items-center justify-between mb-8">
                    <h3 id="modal-title" class="text-2xl font-black text-slate-900">Add Project</h3>
                    <button onclick="closePortfolioModal()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-rose-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="portfolio-form" class="space-y-6">
                    <input type="hidden" name="action" id="portfolio-action">
                    
                    <div id="project-upload-area" class="space-y-2 hidden">
                        <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Project Image</label>
                        <label class="w-full h-48 bg-slate-100 rounded-[2rem] flex flex-col items-center justify-center border-4 border-dashed border-slate-200 cursor-pointer overflow-hidden group">
                           <input type="file" name="image" class="hidden" accept="image/*" onchange="previewProjectImage(this)">
                           <div id="project-preview" class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                               <i class="fas fa-cloud-upload-alt text-4xl mb-2 group-hover:text-emerald-500 transition-colors"></i>
                               <span class="text-xs font-bold uppercase tracking-widest">Select Photo</span>
                           </div>
                        </label>
                    </div>

                    <div class="space-y-2">
                        <label id="label-title" class="block text-[10px] font-black uppercase text-slate-400 ml-2">Title</label>
                        <input type="text" name="title" id="input-title" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                    </div>

                    <div id="dynamic-fields" class="space-y-6">
                        <!-- Dynamic content based on action -->
                    </div>

                    <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl hover:scale-105 active:scale-95 transition-all">Submit Entry</button>
                </form>
            </div>
        </div>
    </div>
    </main>

<?php include "includes/footer.php"; ?>

<script>
function openEditProfile() {
    document.getElementById('edit-profile-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditProfile() {
    document.getElementById('edit-profile-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openAddPortfolio() { openPortfolioModal('add_portfolio', 'Add Project'); }
function openAddExperience() { openPortfolioModal('add_experience', 'Add Experience'); }
function openAddCert() { openPortfolioModal('add_cert', 'Add Certification'); }
function openAddGig() { openPortfolioModal('add_gig', 'Create Quick Gig'); }
function openAddEducation() { openPortfolioModal('add_education', 'Add Education Detail'); }
function openAddReference() { openPortfolioModal('add_reference', 'Add Character Reference'); }
function openAddAchievement() { openPortfolioModal('add_achievement', 'Add Achievement'); }

function uploadAvatar(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('avatar', input.files[0]);

        // Feedback
        const container = input.closest('.group').querySelector('div');
        const originalContent = container.innerHTML;
        container.innerHTML = '<i class="fas fa-circle-notch fa-spin text-emerald-500"></i>';

        fetch('ajax/update_profile.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
                container.innerHTML = originalContent;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Upload failed');
            container.innerHTML = originalContent;
        });
    }
}

const editProfileForm = document.getElementById('edit-profile-form');
if (editProfileForm) {
    editProfileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('ajax/update_profile.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Profile updated!');
                location.reload();
            } else {
                alert(result.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Update failed');
        });
    });
}

// Portfolio & Professional Management JS
function openPortfolioModal(action, title) {
    document.getElementById('portfolio-modal').classList.remove('hidden');
    document.getElementById('portfolio-action').value = action;
    document.getElementById('modal-title').innerText = title;
    document.body.style.overflow = 'hidden';

    const fields = document.getElementById('dynamic-fields');
    const uploadArea = document.getElementById('project-upload-area');

    const titleLabel = document.getElementById('label-title');
    
    fields.innerHTML = '';
    uploadArea.classList.add('hidden');
    titleLabel.innerText = 'Title';

    if (action === 'add_portfolio') {
        uploadArea.classList.remove('hidden');
        titleLabel.innerText = 'Project Name';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Description</label>
                <textarea name="description" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Completion Date</label>
                <input type="date" name="completion_date" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
        `;
    } else if (action === 'add_experience') {
        titleLabel.innerText = 'Position / Role';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Company / Workshop Name</label>
                <input type="text" name="company" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Start Date</label>
                    <input type="date" name="start_date" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">End Date (Optional)</label>
                    <input type="date" name="end_date" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Description of Work</label>
                <textarea name="description" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
        `;
    } else if (action === 'add_cert') {
        titleLabel.innerText = 'Certification Title';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Issuing Institution</label>
                <input type="text" name="institution" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Issue Date</label>
                <input type="date" name="issue_date" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
        `;
    } else if (action === 'add_gig') {
        uploadArea.classList.remove('hidden');
        titleLabel.innerText = 'Service Title (e.g. Toilet Repair)';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Starting Price (KSh)</label>
                <input type="number" name="price" required placeholder="1500" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Quick Description</label>
                <textarea name="description" placeholder="Short summary of what you offer..." class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
        `;
    } else if (action === 'add_education') {
        titleLabel.innerText = 'Institution / School';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Degree / Certificate / Course</label>
                <input type="text" name="credential" required placeholder="e.g. Grade III Plumber Certificate" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Start Date</label>
                    <input type="date" name="start_date" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">End Date (Optional)</label>
                    <input type="date" name="end_date" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Description / Achievements</label>
                <textarea name="description" placeholder="Any details..." class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
        `;
    } else if (action === 'add_reference') {
        titleLabel.innerText = 'Reference Name';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Organization / Company</label>
                <input type="text" name="organization" placeholder="e.g. Kenya Power" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Relationship</label>
                <input type="text" name="relationship" placeholder="e.g. Former Supervisor" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Contact Details (Phone / Email)</label>
                <input type="text" name="contact_info" required placeholder="e.g. +254 712345678 or ref@mail.com" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
        `;
    } else if (action === 'add_achievement') {
        titleLabel.innerText = 'Achievement Title';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Date Awarded / Achieved</label>
                <input type="date" name="date_awarded" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Description</label>
                <textarea name="description" placeholder="Briefly describe what was accomplished..." class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
        `;
    }
}

// Phase 3: Review & Job Management JS
function openReviewModal(jobId, jobTitle) {
    document.getElementById('review-job-id').value = jobId;
    document.getElementById('review-job-title').innerText = jobTitle;
    document.getElementById('review-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal() {
    document.getElementById('review-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function setStar(val) {
    document.getElementById('review-rating-val').value = val;
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-' + i);
        if (i <= val) {
            star.classList.remove('text-slate-200');
            star.classList.add('text-amber-400');
        } else {
            star.classList.remove('text-amber-400');
            star.classList.add('text-slate-200');
        }
    }
}

const reviewForm = document.getElementById('review-form');
if (reviewForm) {
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // First Mark as Complete, then Submit Review
        fetch('ajax/update_job_status.php?t=' + Date.now(), {
            method: 'POST',
            body: new URLSearchParams({
                'job_id': formData.get('job_id'),
                'action': 'complete'
            })
        })
        .then(r => r.json())
        .then(statusResult => {
            if (statusResult.success) {
                // Now submit the review
                return fetch('ajax/submit_review.php?t=' + Date.now(), {
                    method: 'POST',
                    body: formData
                });
            }
            throw new Error(statusResult.message);
        })
        .then(r => r.json())
        .then(reviewResult => {
            if (reviewResult.success) {
                alert('Job completed and review posted!');
                location.reload();
            } else {
                alert(reviewResult.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'Error processing completion');
        });
    });
}

function cancelJob(jobId) {
    if (confirm('Are you sure you want to cancel this job?')) {
        const formData = new FormData();
        formData.append('job_id', jobId);
        formData.append('action', 'cancel');

        fetch('ajax/update_job_status.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
            }
        });
    }
}

function closePortfolioModal() {
    document.getElementById('portfolio-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function copyPortfolioLink() {
    const userId = <?php echo $user_id; ?>;
    const url = window.location.href.split('?')[0].replace(/profile\.php$/, '') + 'fundi/portfolio/?id=' + userId;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Portfolio link copied to clipboard!');
        });
    } else {
        alert('Your shareable link: ' + url);
    }
}

function previewProjectImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('project-preview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

const portfolioForm = document.getElementById('portfolio-form');
if (portfolioForm) {
    portfolioForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('ajax/manage_portfolio.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Update failed');
        });
    });
}
</script>

