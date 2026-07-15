<?php
/**
 * Jobs Exploration Page - MbokaHub
 * Multi-role view: Fundis find work, Hirers track projects.
 */
require_once 'includes/db_connect.php';
require_once 'includes/translations.php';
session_start();

$page_title = "Explore Jobs";
include 'includes/header.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Filters
$category_id = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_NUMBER_INT);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
$status_filter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'open';

// Fetch Categories for Filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name_en ASC")->fetchAll();

// Main Query Build
if ($user_role === 'fundi' || $user_role === 'contractor') {
    // Fundis see available jobs to bid on
    $sql = "SELECT j.*, u.first_name, u.last_name, c.name_en as cat_name, c.icon_class 
            FROM jobs j 
            JOIN users u ON j.user_id = u.id 
            LEFT JOIN categories c ON j.category_id = c.id 
            WHERE j.status = 'open'";
    
    $params = [];
    if ($category_id) {
        $sql .= " AND j.category_id = ?";
        $params[] = $category_id;
    }
    if ($search) {
        $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.location LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    $sql .= " ORDER BY j.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();
} else {
    // Hirers see their own posted jobs
    $sql = "SELECT j.*, c.name_en as cat_name, c.icon_class, 
            (SELECT COUNT(*) FROM job_bids WHERE job_id = j.id) as bid_count
            FROM jobs j 
            LEFT JOIN categories c ON j.category_id = c.id 
            WHERE j.user_id = ?";
    
    $params = [$user_id];
    if ($status_filter && $status_filter !== 'all') {
        $sql .= " AND j.status = ?";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY j.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();
}
?>

<main class="max-w-7xl mx-auto px-2 md:px-6 py-4 md:py-8 mb-24">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-6 mb-4 md:mb-10">
        <div>
            <h2 class="text-xl md:text-3xl font-black text-slate-900 tracking-tight">
                <?php echo ($user_role === 'fundi') ? 'Available Projects' : 'My Project Suite'; ?>
            </h2>
            <p class="text-slate-500 font-semibold flex items-center gap-2 mt-1 text-xs md:text-base">
                <?php if ($user_role === 'fundi'): ?>
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    Browse jobs that match your expertise
                <?php else: ?>
                    Track your active requests and hirings
                <?php endif; ?>
            </p>
        </div>

        <?php if ($user_role === 'hirer'): ?>
        <a href="index.php" class="flex items-center gap-3 px-4 py-2 md:px-6 md:py-4 bg-slate-900 text-white rounded-xl md:rounded-2xl font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 hover:-translate-y-1 transition-all active:scale-95 group text-sm md:text-base">
            <span class="w-6 h-6 md:w-8 md:h-8 bg-white/10 rounded-lg md:rounded-xl flex items-center justify-center group-hover:bg-white/20 transition-colors">
                <i class="fas fa-plus text-xs md:text-base"></i>
            </span>
            Post New Job
        </a>
        <?php endif; ?>
    </div>

    <!-- Discovery Bar -->
    <div class="bg-white p-2 md:p-5 rounded-2xl md:rounded-[2.5rem] shadow-sm border border-slate-100 mb-4 md:mb-10 flex flex-col md:flex-row gap-2 md:gap-4">
        <form class="flex-1 flex flex-col md:flex-row gap-2 md:gap-4" method="GET">
            <div class="flex-1 relative group">
                <i class="fas fa-search absolute left-3 md:left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors text-xs md:text-base"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search projects..." class="w-full pl-8 md:pl-14 pr-4 md:pr-6 py-2 md:py-4 bg-slate-50 border-none rounded-xl md:rounded-2xl text-slate-900 font-bold placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-500/20 transition-all text-xs md:text-base">
            </div>
            
            <div class="md:w-64 relative">
                <select name="category" class="w-full pl-3 md:pl-6 pr-8 md:pr-12 py-2 md:py-4 bg-slate-50 border-none rounded-xl md:rounded-2xl text-slate-900 font-bold appearance-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer text-xs md:text-base">
                    <option value="">All Disciplines</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name_en']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-3 md:right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[10px] md:text-base"></i>
            </div>

            <button type="submit" class="px-4 py-2 md:px-8 md:py-4 bg-emerald-500 text-white rounded-xl md:rounded-2xl font-black text-xs md:text-sm shadow-lg shadow-emerald-100 hover:bg-emerald-600 hover:-translate-y-0.5 transition-all">
                Filter
            </button>
        </form>
    </div>

    <!-- Jobs Grid -->
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-6">
        <?php if (empty($jobs)): ?>
            <div class="col-span-full py-10 md:py-20 text-center">
                <div class="w-12 h-12 md:w-20 md:h-20 bg-slate-100 text-slate-400 rounded-2xl md:rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-folder-open text-xl md:text-3xl"></i>
                </div>
                <h3 class="text-sm md:text-xl font-bold text-slate-900">No projects found</h3>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium">Try adjusting your filters or search terms</p>
            </div>
        <?php else: ?>
            <?php foreach($jobs as $job): ?>
                <div class="bg-white rounded-[1rem] md:rounded-[2.5rem] border border-slate-100 p-3 md:p-8 hover:shadow-2xl hover:shadow-slate-200/50 transition-all group flex flex-col h-full">
                    <!-- Category Badge -->
                    <div class="flex items-center justify-between mb-2 md:mb-6">
                        <div class="flex items-center gap-1 md:gap-3">
                            <div class="w-6 h-6 md:w-10 md:h-10 bg-emerald-50 text-emerald-500 rounded-md md:rounded-xl flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                <i class="fas <?php echo $job['icon_class'] ?: 'fa-briefcase'; ?> text-[8px] md:text-base"></i>
                            </div>
                            <span class="text-[8px] md:text-[10px] font-black uppercase tracking-widest text-slate-400 truncate w-16 md:w-auto"><?php echo htmlspecialchars($job['cat_name'] ?: 'General'); ?></span>
                        </div>
                        <?php if($job['urgency'] === 'emergency'): ?>
                            <span class="bg-rose-50 text-rose-500 px-1.5 py-0.5 md:px-3 md:py-1 rounded-full text-[7px] md:text-[9px] font-black uppercase tracking-tighter border border-rose-100 animate-pulse">Emergency</span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-[11px] md:text-xl font-black text-slate-900 mb-1 md:mb-3 leading-tight group-hover:text-emerald-600 transition-colors line-clamp-2 md:line-clamp-none"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p class="text-slate-500 text-[9px] md:text-sm font-medium line-clamp-2 md:line-clamp-3 mb-2 md:mb-6"><?php echo htmlspecialchars($job['description']); ?></p>

                    <div class="mt-auto space-y-2 md:space-y-4">
                        <div class="flex items-center justify-between pt-2 md:pt-6 border-t border-slate-50">
                            <div>
                                <p class="text-[8px] md:text-[10px] font-black uppercase text-slate-400 tracking-tighter">Budget</p>
                                <p class="text-[11px] md:text-lg font-black text-slate-900">Ksh <?php echo number_format($job['budget_range']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[8px] md:text-[10px] font-black uppercase text-slate-400 tracking-tighter">Location</p>
                                <p class="text-[9px] md:text-sm font-bold text-slate-900 truncate w-12 md:w-auto"><?php echo htmlspecialchars($job['location']); ?></p>
                            </div>
                        </div>

                        <?php if ($user_role === 'fundi'): ?>
                            <a href="get_job_details.php?id=<?php echo $job['id']; ?>" class="block w-full text-center py-2 md:py-4 bg-slate-900 text-white rounded-lg md:rounded-2xl font-black text-[9px] md:text-sm hover:bg-emerald-600 shadow-md md:shadow-xl shadow-slate-100 transition-all">
                                View <span class="hidden md:inline">& Bid</span>
                            </a>
                        <?php else: ?>
                            <div class="flex flex-col md:flex-row items-center justify-between gap-1 md:gap-3">
                                <span class="px-2 py-1 md:px-4 md:py-2 bg-slate-50 text-slate-600 rounded-md md:rounded-xl text-[8px] md:text-xs font-bold border border-slate-100 capitalize w-full text-center md:w-auto">
                                    <?php echo str_replace('_', ' ', $job['status']); ?>
                                </span>
                                <a href="get_job_details.php?id=<?php echo $job['id']; ?>" class="w-full text-center px-2 py-1.5 md:px-5 md:py-2 bg-indigo-50 text-indigo-600 rounded-md md:rounded-xl text-[9px] md:text-xs font-black hover:bg-indigo-600 hover:text-white transition-all">
                                    Manage <span class="hidden md:inline">(<?php echo $job['bid_count']; ?>)</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>