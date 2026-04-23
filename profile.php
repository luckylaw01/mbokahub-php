<?php
require_once "includes/translations.php";
require_once "includes/db_connect.php";
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
        
        $profile = [
            "rating" => $full_profile["rating"] ?? 0.0,
            "reviews" => $full_profile["review_count"] ?? 0,
            "completed" => 0, // Need jobs table query for actual count
            "joined" => date("M Y", strtotime($full_profile["created_at"])),
            "specialty" => $full_profile["cat_name"] ?? ($role === "fundi" ? "Artisan" : "Client"),
            "location" => $full_profile["location"] ?? "Kenya",
            "bio" => $full_profile["bio"] ?? "",
            "avatar" => $full_profile["avatar_url"] ?? null,
            "tvet_level" => $full_profile["tvet_level"] ?? "student"
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
            "avatar" => null
        ];
    }
} catch (PDOException $e) {
    die("Error fetching profile: " . $e->getMessage());
}

// Fetch portfolio items
$portfolio_items = [];
$experiences = [];
$certifications = [];
$gigs = [];

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

        $stmt = $pdo->prepare("SELECT * FROM gigs WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $gigs = $stmt->fetchAll();
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
                    <?php endif; ?>

                    <!-- Quick Upload Overlay (Shown on Hover) -->
                    <label for="avatar-input" class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-all cursor-pointer flex items-center justify-center rounded-[2.5rem] text-white overflow-hidden">
                        <i class="fas fa-camera text-2xl"></i>
                        <input type="file" id="avatar-input" class="hidden" accept="image/*" onchange="uploadAvatar(this)">
                    </label>
                </div>

                <div class="text-center md:text-left flex-1">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-2"><?php echo htmlspecialchars($user_name); ?></h2>
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
                            <span class="text-xl font-black text-slate-900"><?php echo $profile["rating"]; ?> <i class="fas fa-star text-amber-400 text-sm"></i></span>
                        </div>
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">Joined</span>
                            <span class="text-xl font-black text-slate-900"><?php echo $profile["joined"]; ?></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 w-full md:w-auto">
                    <button onclick="openEditProfile()" class="w-full md:w-48 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl hover:scale-105 transition-all">Edit Profile</button>
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

function uploadAvatar(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('avatar', input.files[0]);

        // Feedback
        const container = input.closest('.group').querySelector('div');
        const originalContent = container.innerHTML;
        container.innerHTML = '<i class="fas fa-circle-notch fa-spin text-emerald-500"></i>';

        fetch('ajax/update_profile.php', {
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

document.getElementById('edit-profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('ajax/update_profile.php', {
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
    }
}

function closePortfolioModal() {
    document.getElementById('portfolio-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openAddPortfolio() { openPortfolioModal('add_portfolio', 'Add Project'); }
function openAddExperience() { openPortfolioModal('add_experience', 'Add Experience'); }
function openAddCert() { openPortfolioModal('add_cert', 'Add Certification'); }
function openAddGig() { openPortfolioModal('add_gig', 'Create Quick Gig'); }

function previewProjectImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('project-preview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('portfolio-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('ajax/manage_portfolio.php', {
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
</script>

