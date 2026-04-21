<?php
if (!isset($current_lang)) {
    $current_lang = $_SESSION['current_lang'] ?? 'en';
}
if (!isset($t)) {
    require_once 'translations.php';
    $t = $lang[$current_lang];
}
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MbokaHub - <?php echo $page_title ?? 'Artisan Marketplace'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .vibrant-gradient { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); }
        .glassmorphism { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        @media (max-width: 768px) {
            .mobile-bottom-nav {
                position: fixed;
                bottom: 1.5rem;
                left: 1.5rem;
                right: 1.5rem;
                z-index: 100;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen pb-32 md:pb-0">

    <!-- Global Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-20 flex items-center justify-between">
            <!-- Left: Branding -->
            <div class="flex items-center gap-2 md:gap-3">
                <a href="index.php" class="flex items-center gap-2 md:gap-3 group">
                    <div class="vibrant-gradient text-white p-2 md:p-2.5 rounded-xl md:rounded-2xl shadow-lg group-hover:rotate-12 transition-transform">
                        <i class="fas fa-tools text-lg md:text-xl"></i>
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight">Mboka<span class="text-emerald-500">Hub</span></h1>
                </a>
            </div>

            <!-- Center: Nav Pills -->
            <nav class="hidden md:flex bg-slate-200/50 p-1.5 rounded-[1.5rem] items-center gap-1">
                <a href="index.php" class="px-6 py-2 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-500 hover:text-slate-900 font-medium'; ?> rounded-full transition-all"><?php echo $t['nav_home'] ?? 'Home'; ?></a>
                <a href="#" class="px-6 py-2 text-slate-500 hover:text-slate-900 font-medium transition-colors"><?php echo $t['nav_search'] ?? 'Search'; ?></a>
                <a href="#" class="px-6 py-2 text-slate-500 hover:text-slate-900 font-medium transition-colors">Jobs</a>
                <a href="profile.php" class="px-6 py-2 <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'bg-white shadow-sm text-emerald-600 font-semibold' : 'text-slate-500 hover:text-slate-900 font-medium'; ?> rounded-full transition-all"><?php echo $t['nav_profile'] ?? 'Profile'; ?></a>
            </nav>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2 md:gap-4">
                <a href="?lang=<?php echo $current_lang === 'en' ? 'sw' : 'en'; ?>" 
                   class="bg-slate-900 text-white px-4 md:px-5 py-2 md:py-2.5 rounded-full text-[10px] md:text-xs font-bold uppercase tracking-widest shadow-xl flex items-center justify-center min-w-[3.5rem] hover:bg-emerald-600 transition-all">
                    <?php echo strtoupper($current_lang); ?>
                </a>
                <?php if ($is_logged_in): ?>
                    <div class="flex items-center gap-2 md:gap-3 bg-slate-100 pl-3 md:pl-4 pr-1 md:pr-1.5 py-1 md:py-1.5 rounded-full border border-slate-200">
                        <span class="hidden sm:inline text-xs md:text-sm font-bold text-slate-700"><?php echo htmlspecialchars($user_name); ?></span>
                        <div class="w-7 h-7 md:w-8 md:h-8 vibrant-gradient rounded-full flex items-center justify-center text-white text-[10px] md:text-xs font-bold">
                            <?php echo substr($user_name, 0, 1); ?>
                        </div>
                        <a href="logout.php" class="text-slate-400 hover:text-rose-500 transition-colors ml-1 mr-1 md:mr-2" title="Logout">
                            <i class="fas fa-sign-out-alt text-xs md:text-sm"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-xs md:text-sm font-bold text-slate-600 hover:text-emerald-600 transition-colors">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
