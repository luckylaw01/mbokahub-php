<?php
/**
 * MbokaHub - Main Dashboard (Hirer View Placeholder)
 * Blueprint: 3.3. Main Content Area
 */
require_once 'includes/db_connect.php';
require_once 'includes/translations.php';
require_once 'includes/rating_helper.php';
session_start();

// Handle Language Switching
if (isset($_GET['lang'])) {
    $_SESSION['current_lang'] = $_GET['lang'] === 'sw' ? 'sw' : 'en';
}
$current_lang = $_SESSION['current_lang'] ?? 'en';
$t = $lang[$current_lang];

// Check for Remember Me cookie if not logged in
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // Re-establish session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
        }
    } catch (PDOException $e) {
        // Silently fail cookie login
    }
}

// Current User State
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['name'] : '';
$user_role = $is_logged_in ? $_SESSION['role'] : 'hirer';

// Initial Dashboard View logic
// If fundi or contractor, default to 'work' (find projects/gigs), otherwise default to 'hire'
$initial_view = ($user_role === 'fundi' || $user_role === 'contractor') ? 'work' : 'hire';

// Redirect Admin to their specialized dashboard
if ($user_role === 'admin') {
    header("Location: admin/index.php");
    exit();
}

// Fetch initial categories for the grid from DB
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Fetch Active Stories (Last 24 hours, last 10)
try {
    $stmt = $pdo->query("
        SELECT s.*, u.first_name, u.role, u.user_name 
        FROM stories s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.expires_at > CURRENT_TIMESTAMP 
        ORDER BY s.created_at DESC 
        LIMIT 10
    ");
    $stories = $stmt->fetchAll();
} catch (PDOException $e) {
    $stories = [];
}

// Fetch Main Feed Data mapping to current view
$feed_items = [];
try {
    if ($initial_view === 'hire') {
        // Search & Filter Logic
        $search = $_GET['search'] ?? '';
        $category_filter = $_GET['category'] ?? '';
        
        $query = "
            SELECT u.id as user_id, u.first_name, u.last_name, u.user_name, f.location, f.rating, c.icon_class, IFNULL(c.name_en, 'Artisan') as specialization
            FROM users u
            JOIN fundi_profiles f ON u.id = f.user_id
            LEFT JOIN categories c ON f.category_id = c.id
            WHERE u.role = 'fundi'
        ";
        
        $params = [];
        if (!empty($search)) {
            $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR f.bio LIKE ? OR f.location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($category_filter)) {
            $query .= " AND f.category_id = ?";
            $params[] = $category_filter;
        }
        
        $query .= " ORDER BY f.rating DESC LIMIT 10";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $feed_items = $stmt->fetchAll();
    } else {
        // Fetch open jobs for the fundi, including information on whether they have already bid
        $fundi_id = $_SESSION['user_id'] ?? 0;
        $stmt = $pdo->prepare("
            SELECT j.*, c.name_en as category_name, c.icon_class, u.first_name, u.last_name,
                   (SELECT id FROM job_bids WHERE job_id = j.id AND fundi_id = ?) as user_bid_id
            FROM jobs j
            JOIN categories c ON j.category_id = c.id
            JOIN users u ON j.user_id = u.id
            WHERE j.status = 'open'
            ORDER BY j.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$fundi_id]);
        $feed_items = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $feed_items = [];
}

$page_title = "Find Your Expert Fundi";
include 'includes/header.php';
?>

    <main class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-10">
        
        <?php if ($is_logged_in): ?>
        <!-- Personalized Greeting for Kenyan Audience -->
        <div class="mb-8 md:mb-12 bg-gradient-to-br from-emerald-600 via-emerald-700 to-slate-900 rounded-[2.5rem] p-6 md:p-10 text-white relative overflow-hidden shadow-2xl shadow-emerald-900/20">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 blur-3xl -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-emerald-400 opacity-20 blur-2xl -ml-20 -mb-20"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 md:gap-6">
                <div>
                    <h2 class="text-3xl md:text-5xl font-black mb-1 md:mb-2 tracking-tight">Habari, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>! 👋</h2>
                    <p class="text-emerald-100 font-medium text-xs md:text-base">Karibu! Here's what's happening on your MbokaHub dashboard today.</p>
                </div>
                <div class="text-left md:text-right bg-black/20 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/10 shadow-inner">
                    <p class="text-[9px] uppercase tracking-widest text-emerald-200 font-black mb-0.5"><?php echo date("l, d M Y"); ?></p>
                    <p class="text-sm md:text-lg font-black text-white"><?php echo $user_role === 'fundi' ? 'Expert Dashboard' : 'Client Dashboard'; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3.4. Stories / Spotlight Bar -->
        <section class="mb-10 lg:mb-16">
            <div class="flex items-center justify-between mb-5 px-1 md:px-2">
                <h3 class="text-lg md:text-xl font-bold flex items-center gap-2">
                    <span class="vibrant-gradient w-2 h-6 md:h-7 rounded-full"></span>
                    Spotlights & Updates
                </h3>
            </div>
            
            <div class="flex gap-4 md:gap-6 overflow-x-auto no-scrollbar pb-4 -mx-4 px-4">
                <!-- Create Story Button (If Logged In) -->
                <?php if ($is_logged_in): ?>
                <div class="flex-shrink-0 flex flex-col items-center gap-2 cursor-pointer group">
                    <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-200 rounded-full flex items-center justify-center border-2 border-dashed border-slate-400 group-hover:border-emerald-500 transition-colors">
                        <i class="fas fa-plus text-slate-500 group-hover:text-emerald-500"></i>
                    </div>
                    <span class="text-[10px] md:text-xs font-bold text-slate-600">Add Story</span>
                </div>
                <?php endif; ?>

                <?php if (empty($stories)): ?>
                    <!-- Mock Story for empty state UI display -->
                    <div class="flex-shrink-0 flex flex-col items-center gap-2 cursor-pointer group">
                        <div class="w-16 h-16 md:w-20 md:h-20 p-1 rounded-full bg-gradient-to-tr from-amber-400 to-rose-500">
                            <div class="w-full h-full bg-white rounded-full p-0.5">
                                <img src="https://ui-avatars.com/api/?name=Mboka+Hub&background=10b981&color=fff" class="w-full h-full rounded-full object-cover">
                            </div>
                        </div>
                        <span class="text-[10px] md:text-xs font-bold text-emerald-600">Welcome!</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($stories as $story): ?>
                    <div class="flex-shrink-0 flex flex-col items-center gap-2 cursor-pointer group animate-in fade-in zoom-in duration-500">
                        <div class="w-16 h-16 md:w-20 md:h-20 p-1 rounded-full <?php echo $story['type'] === 'fundi_work' ? 'bg-gradient-to-tr from-emerald-400 to-blue-500' : 'bg-gradient-to-tr from-purple-500 to-pink-500'; ?>">
                            <div class="w-full h-full bg-white rounded-full p-0.5">
                                <img src="<?php echo htmlspecialchars($story['media_url']); ?>" 
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($story['first_name']); ?>&background=random'"
                                     class="w-full h-full rounded-full object-cover group-hover:scale-110 transition-transform">
                            </div>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-[10px] md:text-xs font-bold text-slate-800 truncate max-w-[80px]">
                                <?php echo htmlspecialchars($story['first_name']); ?>
                            </span>
                            <?php if ($story['is_verified']): ?>
                            <i class="fas fa-check-circle text-blue-500 text-[8px] md:text-[9px]"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 3.2. Role Switcher -->
        <div class="flex justify-center mb-8 md:mb-12">
            <div class="bg-white/50 backdrop-blur-md shadow-2xl rounded-full md:rounded-[2.5rem] p-1.5 md:p-2 flex gap-1 md:gap-2 border border-white/40 max-w-full overflow-x-auto no-scrollbar">
                <button id="btn-hire" onclick="switchView('hire')" 
                        class="px-5 md:px-8 py-2.5 md:py-3 rounded-full md:rounded-[2rem] font-bold text-xs md:text-sm shadow-lg whitespace-nowrap transition-all <?php echo $initial_view === 'hire' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100'; ?>">
                    <?php echo $t['btn_hire']; ?>
                </button>
                <button id="btn-work" onclick="switchView('work')" 
                        class="px-5 md:px-8 py-2.5 md:py-3 rounded-full md:rounded-[2rem] font-bold text-xs md:text-sm whitespace-nowrap transition-all <?php echo $initial_view === 'work' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100'; ?>">
                    <?php echo $t['btn_work']; ?>
                </button>
            </div>
        </div>

        <!-- Dashboard Containers -->
        <div id="hirer-view" class="<?php echo $initial_view === 'work' ? 'hidden' : ''; ?>">
            <!-- 3.3. Hero/Search Banner -->
            <section class="relative mb-6 md:mb-20">
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-emerald-300/20 rounded-full blur-3xl animate-pulse"></div>
                
                <div class="relative bg-white p-4 md:p-20 rounded-2xl md:rounded-[3rem] shadow-2xl shadow-slate-200 overflow-hidden text-center border border-slate-50">
                    <h2 class="text-xl md:text-6xl font-extrabold mb-4 md:mb-8 tracking-tight text-slate-900 leading-tight">
                        <?php echo $t['hero_title']; ?>
                    </h2>
                    <form action="index.php" method="GET" class="relative max-w-3xl mx-auto">
                        <i class="fas fa-search absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-slate-400 text-xs md:text-lg"></i>
                        <input type="text" name="search" placeholder="<?php echo $t['search_placeholder']; ?>" 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                               class="w-full bg-slate-50 rounded-xl md:rounded-[2rem] pl-10 md:pl-16 pr-4 md:px-16 py-3 md:py-7 text-xs md:text-lg border-2 border-transparent focus:border-emerald-500/30 focus:outline-none shadow-inner transition-all">
                        <button type="submit" class="hidden md:block absolute right-4 top-1/2 -translate-y-1/2 bg-slate-900 text-white px-10 py-4 rounded-[1.5rem] font-bold hover:scale-105 active:scale-95 transition-all shadow-lg">
                            <?php echo $t['search_btn']; ?>
                        </button>
                    </form>
                    <?php if ($is_logged_in && $_SESSION['role'] === 'hirer'): ?>
                    <button onclick="openPostJobWizard()" class="mt-4 md:mt-10 inline-flex items-center gap-2 md:gap-3 bg-emerald-500 text-white px-4 py-2 md:px-8 md:py-4 rounded-xl md:rounded-2xl font-bold text-xs md:text-base shadow-xl shadow-emerald-200 hover:bg-emerald-600 hover:scale-105 active:scale-95 transition-all">
                        <i class="fas fa-plus-circle"></i>
                        <?php echo $t['post_job']; ?>
                    </button>
                    <?php endif; ?>
                    <button class="md:hidden w-full mt-3 bg-slate-900 text-white py-3 rounded-xl text-xs font-bold shadow-lg">
                        <?php echo $t['search_btn']; ?> Expert
                    </button>
                </div>
            </section>

            <!-- 3.3. Categories Grid -->
            <section class="mb-12 md:mb-20">
                <div class="flex items-center justify-between mb-6 md:mb-10 px-2 md:px-4">
                    <h3 class="text-xl md:text-2xl font-bold"><?php echo $t['categories']; ?></h3>
                    <a href="#" class="text-xs md:text-sm font-bold text-emerald-600 hover:underline tracking-tight"><?php echo $t['view_all']; ?></a>
                </div>
                
                <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 gap-2 md:gap-6">
                    <?php foreach ($categories as $cat): ?>
                    <a href="index.php?category=<?php echo $cat['id']; ?>" class="bg-white p-3 md:p-8 rounded-2xl md:rounded-[2.5rem] border border-slate-100 <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'border-emerald-500 bg-emerald-50/30' : 'hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/5'; ?> transition-all group cursor-pointer text-center">
                        <div class="w-8 h-8 md:w-16 md:h-16 <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'bg-emerald-500 text-white' : 'bg-slate-50'; ?> rounded-lg md:rounded-2xl flex items-center justify-center mx-auto mb-2 md:mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <i class="fas <?php echo htmlspecialchars($cat['icon_class']); ?> text-sm md:text-2xl"></i>
                        </div>
                        <span class="font-bold text-[9px] md:text-sm text-slate-800 transition-colors group-hover:text-emerald-600 leading-tight">
                            <?php echo htmlspecialchars($cat['name_'.$current_lang]); ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- 3.5. Discover Fundis (Active Feed) -->
            <section class="mb-12 md:mb-20">
                <div class="flex items-center justify-between mb-8 px-2">
                    <h3 class="text-xl md:text-2xl font-bold"><?php echo $t['top_experts']; ?></h3>
                    <button class="p-2.5 bg-white rounded-xl border border-slate-100 shadow-sm text-slate-500 hover:text-emerald-500 transition-all">
                        <i class="fas fa-sliders text-sm"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6" id="fundi-feed">
                    <?php if (empty($feed_items) && $initial_view === 'hire'): ?>
                        <div class="col-span-full py-10 md:py-20 text-center bg-white rounded-2xl md:rounded-[2rem] border-2 border-dashed border-slate-200">
                            <i class="fas fa-user-tie text-2xl md:text-4xl text-slate-300 mb-4"></i>
                            <p class="text-slate-500 text-xs md:text-base font-bold">Looking for experts...</p>
                        </div>
                    <?php elseif ($initial_view === 'hire'): ?>
                        <?php foreach ($feed_items as $fundi): ?>
                        <div class="bg-white p-3 md:p-6 rounded-2xl md:rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 hover:scale-[1.02] transition-all cursor-pointer group animate-in slide-in-from-bottom-4 flex flex-col h-full">
                            <div class="flex items-start justify-between mb-3 md:mb-6">
                                <div class="flex items-center gap-2 md:gap-4">
                                    <div class="w-8 h-8 md:w-14 md:h-14 vibrant-gradient rounded-lg md:rounded-2xl flex items-center justify-center text-white text-sm md:text-xl font-bold shadow-lg">
                                        <?php echo substr($fundi['first_name'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-[11px] md:text-base text-slate-900 group-hover:text-emerald-600 transition-colors"><?php echo htmlspecialchars($fundi['first_name'] . ' ' . $fundi['last_name']); ?></h4>
                                        <div class="text-[8px] md:text-[10px] font-black uppercase text-emerald-500 tracking-wider">
                                            <?php echo htmlspecialchars($fundi['specialization']); ?>
                                        </div>
                                        <div class="mt-0.5 md:mt-1 scale-[0.8] origin-left md:scale-100">
                                            <?php echo renderRating($fundi['rating']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 text-slate-500 text-[8px] md:text-xs mb-3 md:mb-6 font-medium mt-auto">
                                <span class="bg-slate-50 px-2 py-1 md:px-3 md:py-1.5 rounded-md md:rounded-lg flex items-center gap-1.5 w-fit">
                                    <i class="fas fa-location-dot text-emerald-500"></i>
                                    <?php echo htmlspecialchars($fundi['location']); ?>
                                </span>
                                <span class="bg-slate-50 px-2 py-1 md:px-3 md:py-1.5 rounded-md md:rounded-lg flex items-center gap-1.5 w-fit">
                                    <i class="fas fa-certificate text-blue-500"></i>
                                    <?php echo $t['verified']; ?>
                                </span>
                            </div>

                            <button onclick="openFundiModal(<?php echo (int)$fundi['user_id']; ?>)" 
                                    class="w-full py-2 md:py-4 bg-slate-900 text-white rounded-lg md:rounded-xl font-bold text-[9px] md:text-sm shadow-md md:shadow-xl hover:bg-emerald-600 transition-all mt-auto">
                                <?php echo $t['view_portfolio']; ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- 3.2. Fundi View (Work Dashboard) -->
        <div id="fundi-view" class="<?php echo $initial_view === 'hire' ? 'hidden' : ''; ?>">
            <!-- Fundi Stats Row (Vibrant Redesign) -->
            <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-12">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-emerald-600/30 text-white relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-32 h-32 bg-white opacity-10 rounded-full blur-xl"></div>
                    <div class="flex items-center gap-4 mb-2 md:mb-4 relative z-10">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 backdrop-blur-sm rounded-xl md:rounded-2xl flex items-center justify-center border border-white/20">
                            <i class="fas fa-briefcase text-base md:text-xl"></i>
                        </div>
                        <h4 class="font-black uppercase tracking-widest text-[10px] md:text-xs text-emerald-100"><?php echo $t['active_jobs']; ?></h4>
                    </div>
                    <p class="text-3xl md:text-4xl font-black relative z-10">12</p>
                    <p class="text-xs md:text-sm text-emerald-200 font-bold mt-1 md:mt-2 relative z-10">+2 since today</p>
                </div>
                
                <div class="bg-gradient-to-br from-slate-800 to-slate-950 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-slate-900/30 text-white relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-500 opacity-20 rounded-full blur-xl"></div>
                    <div class="flex items-center gap-4 mb-2 md:mb-4 relative z-10">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/10 backdrop-blur-sm rounded-xl md:rounded-2xl flex items-center justify-center border border-white/10">
                            <i class="fas fa-wallet text-base md:text-xl text-emerald-400"></i>
                        </div>
                        <h4 class="font-black uppercase tracking-widest text-[10px] md:text-xs text-slate-400"><?php echo $t['earnings']; ?></h4>
                    </div>
                    <p class="text-3xl md:text-4xl font-black relative z-10 text-emerald-400">KES 4.2k</p>
                    <p class="text-[10px] md:text-sm text-slate-400 font-bold mt-1 md:mt-2 relative z-10">Available for withdrawal</p>
                </div>

                <div class="sm:col-span-2 md:col-span-1 bg-gradient-to-br from-amber-400 to-orange-500 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-amber-500/30 text-white relative overflow-hidden">
                    <div class="absolute -left-4 -bottom-4 w-32 h-32 bg-white opacity-20 rounded-full blur-xl"></div>
                    <div class="flex items-center gap-4 mb-2 md:mb-4 relative z-10">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 backdrop-blur-sm rounded-xl md:rounded-2xl flex items-center justify-center border border-white/20">
                            <i class="fas fa-star text-base md:text-xl"></i>
                        </div>
                        <h4 class="font-black uppercase tracking-widest text-[10px] md:text-xs text-amber-100"><?php echo $t['trust_index']; ?></h4>
                    </div>
                    <p class="text-3xl md:text-4xl font-black relative z-10">4.9</p>
                    <p class="text-[10px] md:text-sm text-amber-100 font-bold mt-1 md:mt-2 relative z-10">Based on 84 reviews</p>
                </div>
            </section>

            <!-- Job Pipeline / Requests -->
            <section class="bg-white rounded-[2rem] md:rounded-[3rem] p-6 md:p-10 border border-slate-100 shadow-2xl shadow-slate-200/50">
                <div class="flex items-center justify-between mb-6 md:mb-10">
                    <h3 class="text-xl md:text-2xl font-bold"><?php echo $t['new_requests']; ?></h3>
                    <button class="bg-slate-100 text-slate-600 px-4 md:px-6 py-2 rounded-full font-bold text-[10px] md:text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Refresh
                    </button>
                </div>

                <div class="space-y-3 md:space-y-6">
                    <?php if (empty($feed_items) && $initial_view === 'work'): ?>
                        <div class="py-10 md:py-20 text-center bg-slate-50 rounded-2xl md:rounded-[2rem] border-2 border-dashed border-slate-200">
                            <i class="fas fa-hammer text-2xl md:text-4xl text-slate-300 mb-3 md:mb-5"></i>
                            <p class="text-slate-500 text-xs md:text-base font-bold">No jobs available right now.</p>
                        </div>
                    <?php elseif ($initial_view === 'work'): ?>
                        <?php foreach ($feed_items as $job): ?>
                        <div class="group flex flex-col items-stretch p-3 md:p-8 rounded-2xl md:rounded-[2rem] bg-slate-50 border-2 border-transparent hover:border-emerald-500/20 transition-all animate-in slide-in-from-bottom-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-6 mb-3 md:mb-6">
                                <div class="flex items-center gap-3 md:gap-6">
                                    <div class="w-10 h-10 md:w-20 md:h-20 bg-white rounded-xl md:rounded-[2.5rem] shadow-md md:shadow-xl shadow-slate-200/50 flex items-center justify-center shrink-0">
                                        <i class="fas <?php echo htmlspecialchars($job['icon_class']); ?> text-lg md:text-3xl text-emerald-500"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 md:gap-3 mb-1 md:mb-1.5">
                                            <h5 class="font-bold text-[11px] md:text-xl text-slate-800 truncate"><?php echo htmlspecialchars($job['title']); ?></h5>
                                            <?php if ($job['urgency'] === 'emergency'): ?>
                                                <span class="bg-rose-100 text-rose-600 px-2 py-0.5 md:px-3 md:py-1 rounded-full text-[7px] md:text-[10px] font-black uppercase tracking-tighter animate-pulse"><?php echo $t['emergency']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-slate-500 text-[9px] md:text-sm font-medium flex items-center gap-1 md:gap-2">
                                            <i class="fas fa-location-dot text-slate-400"></i>
                                            <?php echo htmlspecialchars($job['location']); ?> 
                                            <span class="text-slate-200">•</span>
                                            <i class="fas fa-wallet text-slate-400"></i>
                                            KES <?php echo htmlspecialchars($job['budget_range']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 md:gap-3">
                                    <button onclick="openJobModal(<?php echo (int)$job['id']; ?>)"
                                            class="flex-1 md:flex-none bg-white text-slate-900 px-3 py-2 md:px-6 md:py-4 rounded-lg md:rounded-xl font-bold text-[9px] md:text-sm shadow-sm hover:bg-slate-900 hover:text-white transition-all text-center">
                                        <?php echo $t['view_details']; ?>
                                    </button>
                                    <?php if (isset($job['user_bid_id']) && $job['user_bid_id']): ?>
                                        <button disabled 
                                                class="flex-1 md:flex-none bg-slate-100 text-slate-400 px-4 py-2 md:px-8 md:py-4 rounded-lg md:rounded-xl font-bold text-[9px] md:text-sm cursor-not-allowed text-center">
                                            <i class="fas fa-check-circle mr-1 md:mr-2"></i>Applied
                                        </button>
                                    <?php else: ?>
                                        <button onclick="openJobModal(<?php echo (int)$job['id']; ?>)" 
                                                class="flex-1 md:flex-none bg-emerald-500 text-white px-4 py-2 md:px-8 md:py-4 rounded-lg md:rounded-xl font-bold text-[9px] md:text-sm shadow-md md:shadow-xl shadow-emerald-200 hover:bg-emerald-600 hover:scale-105 active:scale-95 transition-all text-center">
                                            <?php echo $t['place_bid']; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="bg-white/50 p-3 md:p-5 rounded-xl md:rounded-2xl">
                                <p class="text-[9px] md:text-xs text-slate-600 font-medium italic leading-relaxed line-clamp-2 md:line-clamp-none">
                                    "<?php echo htmlspecialchars(substr($job['description'], 0, 120)) . '...'; ?>"
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

    </main>

    <!-- Global Detailed Modal (Fundi/Job) -->
    <div id="info-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute bottom-0 md:bottom-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 w-full md:max-w-2xl bg-white rounded-t-[3rem] md:rounded-[3rem] shadow-2xl overflow-hidden animate-in slide-in-from-bottom duration-300">
            <div id="modal-content" class="p-8 md:p-12 max-h-[90vh] overflow-y-auto no-scrollbar">
                <!-- Content injected via JS -->
            </div>
            <button onclick="closeModal()" class="absolute top-6 right-6 w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-rose-500 hover:text-white transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Job Post Wizard Template (Hidden) -->
    <template id="job-wizard-template">
        <div class="space-y-8" id="post-job-form">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl md:text-3xl font-black text-slate-900">Post a Job</h2>
                <div class="flex gap-1" id="wizard-steps-indicator">
                    <div class="w-8 h-1.5 rounded-full bg-emerald-500"></div>
                    <div class="w-8 h-1.5 rounded-full bg-slate-200"></div>
                    <div class="w-8 h-1.5 rounded-full bg-slate-200"></div>
                </div>
            </div>

            <!-- Step 1: Basic Info -->
            <div id="step-1" class="wizard-step">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2">Step 1 of 3</p>
                <h3 class="text-xl font-bold mb-6">What do you need help with?</h3>
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Job Title</label>
                        <input type="text" id="job-title" placeholder="e.g. Fix leaking sink in kitchen" 
                               class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-5 text-sm font-bold outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Category</label>
                        <select id="job-category" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-5 text-sm font-bold outline-none transition-all appearance-none cursor-pointer">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_en']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 2: Details & Urgency -->
            <div id="step-2" class="wizard-step hidden">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2">Step 2 of 3</p>
                <h3 class="text-xl font-bold mb-6">How urgent is it?</h3>
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer group">
                            <input type="radio" name="urgency" value="standard" checked class="hidden peer">
                            <div class="p-6 rounded-2xl border-2 border-slate-100 bg-white peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 transition-all text-center">
                                <i class="fas fa-clock text-xl text-slate-300 group-hover:text-emerald-500 mb-3 transition-colors peer-checked:text-emerald-600"></i>
                                <p class="text-sm font-bold">Standard</p>
                                <p class="text-[10px] text-slate-400 font-medium">Within a week</p>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="urgency" value="emergency" class="hidden peer">
                            <div class="p-6 rounded-2xl border-2 border-slate-100 bg-white peer-checked:border-rose-500 peer-checked:bg-rose-50/30 transition-all text-center">
                                <i class="fas fa-bolt text-xl text-slate-300 group-hover:text-rose-500 mb-3 transition-colors peer-checked:text-rose-600"></i>
                                <p class="text-sm font-bold">Emergency</p>
                                <p class="text-[10px] text-slate-400 font-medium">Asap/24hrs</p>
                            </div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Describe the issue</label>
                        <textarea id="job-description" rows="4" placeholder="Be descriptive to get more accurate bids..." 
                                  class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-5 text-sm font-bold outline-none transition-all resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 3: Location & Budget -->
            <div id="step-3" class="wizard-step hidden">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2">Step 3 of 3</p>
                <h3 class="text-xl font-bold mb-6">Where and how much?</h3>
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Location</label>
                        <input type="text" id="job-location" placeholder="e.g. Rongai, Nairobi" 
                               class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-5 text-sm font-bold outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Budget Range (KES)</label>
                        <input type="text" id="job-budget" placeholder="e.g. 500 - 1500" 
                               class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-5 text-sm font-bold outline-none transition-all font-mono">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-10">
                <button id="wizard-prev" onclick="handleWizardPrev()" class="hidden flex-1 bg-slate-100 text-slate-600 py-4 rounded-2xl font-bold hover:bg-slate-200 transition-all">
                    Back
                </button>
                <button id="wizard-next" onclick="handleWizardNext()" class="flex-[2] bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-emerald-600 shadow-xl shadow-slate-200 transition-all">
                    Continue
                </button>
                <button id="wizard-submit" onclick="submitJobRequest()" class="hidden flex-[2] bg-emerald-500 text-white py-4 rounded-2xl font-bold hover:bg-emerald-600 shadow-xl shadow-emerald-200 transition-all">
                    Post My Job
                </button>
            </div>
        </div>
    </template>



    </main>

<?php include 'includes/footer.php'; ?>
