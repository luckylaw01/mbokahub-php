<?php
/**
 * Publicly Sharable Portfolio Page for Fundis
 * URL structure: mbokahub/fundi/portfolio/?id=1
 */
require_once "../../includes/db_connect.php";
require_once "../../includes/translations.php";
session_start();

// 1. Identification
if (!isset($_GET['id'])) {
    die("Profile not specified.");
}
$view_user_id = (int)$_GET['id'];

// 2. Fetch Comprehensive Profile Data
try {
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.created_at as user_created,
               f.*, c.name_en as cat_name, c.icon_class
        FROM users u 
        JOIN fundi_profiles f ON u.id = f.user_id 
        LEFT JOIN categories c ON f.category_id = c.id
        WHERE u.id = ? AND u.role = 'fundi'
    ");
    $stmt->execute([$view_user_id]);
    $fundi = $stmt->fetch();

    if (!$fundi) {
        die("Fundi profile not found.");
    }

    // Fetch Gallery
    $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE user_id = ? ORDER BY completion_date DESC");
    $stmt->execute([$view_user_id]);
    $gallery = $stmt->fetchAll();

    // Fetch Experience
    $stmt = $pdo->prepare("SELECT * FROM experiences WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$view_user_id]);
    $experiences = $stmt->fetchAll();

    // Fetch Certs
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE user_id = ? ORDER BY issue_date DESC");
    $stmt->execute([$view_user_id]);
    $certs = $stmt->fetchAll();

    // Fetch Gigs (Active & Completed)
    $stmt = $pdo->prepare("SELECT * FROM gigs WHERE user_id = ? ORDER BY is_active DESC, created_at DESC");
    $stmt->execute([$view_user_id]);
    $gigs = $stmt->fetchAll();

} catch (PDOException $e) {
    die("System error: " . $e->getMessage());
}

$full_name = $fundi['first_name'] . ' ' . $fundi['last_name'];
$profile_pic = $fundi['avatar_url'] ? "../../" . $fundi['avatar_url'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($full_name); ?> | Professional Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.3); }
        .vibrant-gradient { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); }
        
        /* Hero Background with Parallax Effect */
        .hero-banner {
            background-image: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('https://images.unsplash.com/photo-1581094794329-c8112a89af12?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .floating { animation: floating 3s ease-in-out infinite; }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        /* Smooth section transition */
        .section-curve {
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            transform: rotate(180deg);
        }

        .section-curve svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 80px;
        }

        .section-curve .shape-fill {
            fill: #f8fafc;
        }
    </style>
</head>
<body class="text-slate-900 leading-tight">

    <!-- Sticky Navigation -->
    <nav class="fixed top-6 left-0 right-0 z-[100] px-4">
        <div class="max-w-6xl mx-auto glass rounded-3xl p-3 flex items-center justify-between shadow-xl shadow-slate-200/50">
            <div class="flex items-center gap-3 ml-2">
                <div class="w-10 h-10 vibrant-gradient rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-emerald-200">M</div>
                <span class="font-black text-sm tracking-tighter">MBOKAHUB<span class="text-emerald-500">PRO</span></span>
            </div>
            <div class="hidden md:flex gap-8">
                <a href="#projects" class="py-2 text-xs font-bold text-slate-600 hover:text-emerald-500 transition-colors">Portfolio</a>
                <a href="#journey" class="py-2 text-xs font-bold text-slate-600 hover:text-emerald-500 transition-colors">Career Journey</a>
                <a href="#gigs" class="py-2 text-xs font-bold text-slate-600 hover:text-emerald-500 transition-colors">Services</a>
            </div>
            <div class="flex gap-4">
                <a href="#hire" class="px-6 py-2.5 bg-slate-900 text-white rounded-2xl text-xs font-bold hover:scale-105 transition-all shadow-lg active:scale-95">Hire Me</a>
            </div>
        </div>
    </nav>

    <main class="overflow-x-hidden">
        
        <!-- Immersive Hero Section -->
        <section class="hero-banner min-h-[95vh] flex items-center justify-center relative px-4 pt-24 pb-32">
            <!-- Smooth Design Curve Transition -->
            <div class="section-curve">
                <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
                </svg>
            </div>
            
            <div class="max-w-6xl mx-auto w-full relative z-10">
                <div class="flex flex-col md:flex-row items-center gap-12 lg:gap-20 text-center md:text-left">
                    <!-- Left Side: Profile Picture -->
                    <div class="relative group shrink-0">
                        <div class="absolute inset-0 vibrant-gradient blur-3xl opacity-40 scale-125 group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="w-48 h-48 md:w-64 md:h-64 rounded-[4.5rem] bg-white/10 backdrop-blur-xl p-3 shadow-2xl relative z-10 floating">
                            <div class="w-full h-full rounded-[4rem] bg-slate-100 flex items-center justify-center overflow-hidden border-4 border-white shadow-inner">
                                <?php if ($profile_pic): ?>
                                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-7xl font-black text-slate-300"><?php echo substr($fundi['first_name'], 0, 1); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($fundi['is_verified']): ?>
                        <div class="absolute bottom-6 right-6 bg-emerald-500 text-white w-14 h-14 rounded-3xl flex items-center justify-center border-4 border-white shadow-2xl z-20">
                            <i class="fas fa-check text-base"></i>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Side: Content -->
                    <div class="flex-1">
                        <div class="inline-flex items-center gap-3 px-6 py-2 bg-emerald-500/20 backdrop-blur-md text-emerald-400 rounded-full text-[12px] font-black uppercase tracking-[0.2em] mb-8 border border-emerald-500/30 mx-auto md:mx-0">
                            <i class="fas <?php echo $fundi['icon_class'] ?: 'fa-hammer'; ?> text-xs"></i>
                            <?php echo htmlspecialchars($fundi['cat_name'] ?: 'PRO ARTISAN'); ?>
                        </div>
                        
                        <h1 class="text-5xl md:text-6xl lg:text-8xl font-black text-white tracking-tightest mb-8 leading-[0.9]">
                            I build with <span class="text-emerald-400 italic">Precision</span>,<br>
                            here in <span class="underline decoration-blue-500 underline-offset-8"><?php echo htmlspecialchars($fundi['location'] ?: 'Kenya'); ?></span>.
                        </h1>
                        
                        <p class="text-slate-300 text-xl md:text-2xl font-light leading-relaxed mb-12 max-w-2xl">
                            <?php echo htmlspecialchars($fundi['bio'] ?: "Professional artisan specialized in high-quality craftsmanship and dedicated service delivery."); ?>
                        </p>
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-4">
                            <a href="#hire" class="px-10 py-5 bg-emerald-500 text-white rounded-3xl font-black text-sm uppercase tracking-widest hover:bg-emerald-400 hover:scale-105 transition-all shadow-2xl shadow-emerald-500/40">
                                Send Request
                            </a>
                            <a href="#projects" class="px-10 py-5 bg-white/10 backdrop-blur-md text-white border border-white/20 rounded-3xl font-black text-sm uppercase tracking-widest hover:bg-white/20 transition-all">
                                Gallery
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Area -->
        <section class="max-w-7xl mx-auto px-4 pt-12 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Project Gallery -->
                <div class="lg:col-span-8" id="projects">
                    <div class="mb-12">
                        <h2 class="text-3xl font-black mb-2 px-2 italic">Featured Works</h2>
                        <div class="w-20 h-2 vibrant-gradient rounded-full ml-2"></div>
                    </div>

                    <?php if (empty($gallery)): ?>
                        <div class="p-20 bg-slate-50 rounded-[3rem] border-4 border-dashed border-slate-100 flex flex-col items-center justify-center text-slate-300">
                            <i class="fas fa-image text-5xl mb-4"></i>
                            <p class="font-bold uppercase text-[10px] tracking-widest">No projects showcased yet</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($gallery as $item): ?>
                            <div class="group relative bg-white p-4 rounded-[3.5rem] shadow-sm hover:shadow-2xl transition-all duration-500 border border-slate-50">
                                <div class="aspect-[4/3] rounded-[2.5rem] overflow-hidden mb-6">
                                    <img src="../../<?php echo htmlspecialchars($item['image_url']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                </div>
                                <div class="px-4 pb-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-black text-xl text-slate-900"><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase"><?php echo date("Y", strtotime($item['completion_date'])); ?></span>
                                    </div>
                                    <p class="text-slate-500 text-sm line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Experience Timeline -->
                    <div class="mt-20" id="journey">
                        <div class="mb-12">
                            <h2 class="text-3xl font-black mb-2 px-2 italic">Career Journey</h2>
                            <div class="w-16 h-2 bg-blue-500 rounded-full ml-2"></div>
                        </div>

                        <div class="space-y-6">
                            <?php foreach ($experiences as $exp): ?>
                            <div class="bg-white p-8 rounded-[3rem] border border-slate-50 shadow-sm flex flex-col md:flex-row gap-6 relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 -mr-16 -mt-16 rounded-full group-hover:scale-150 transition-all"></div>
                                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-2xl shrink-0">💼</div>
                                <div class="relative z-10 flex-1">
                                    <h4 class="text-2xl font-black text-slate-900 mb-1"><?php echo htmlspecialchars($exp['role']); ?></h4>
                                    <div class="flex items-center gap-3 text-xs font-bold text-emerald-600 uppercase tracking-widest mb-4">
                                        <span><?php echo htmlspecialchars($exp['company']); ?></span>
                                        <span class="text-slate-200">|</span>
                                        <span class="text-slate-400"><?php echo date("M Y", strtotime($exp['start_date'])); ?> - <?php echo $exp['end_date'] ? date("M Y", strtotime($exp['end_date'])) : "Present"; ?></span>
                                    </div>
                                    <p class="text-slate-500 leading-relaxed text-sm"><?php echo htmlspecialchars($exp['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="lg:col-span-4 space-y-12">
                    
                    <!-- Quick Hiring Card -->
                    <div id="hire" class="vibrant-gradient p-8 rounded-[3.5rem] text-white shadow-2xl shadow-emerald-200 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-3xl -mr-10 -mt-10"></div>
                        <h3 class="text-2xl font-black mb-4 relative z-10 italic">Ready to start?</h3>
                        <p class="text-white/80 text-sm font-medium mb-8 relative z-10 leading-relaxed">
                            I am currently available for new projects in <?php echo htmlspecialchars($fundi['location'] ?: 'your area'); ?>. 
                        </p>
                        <a href="../../login.php" class="block w-full py-5 bg-white text-emerald-600 text-center rounded-3xl font-black shadow-xl hover:scale-105 transition-all relative z-10">
                            SEND A DIRECT REQUEST
                        </a>
                    </div>

                    <!-- Verified Certs -->
                    <div>
                        <h3 class="text-xl font-black mb-6 px-2">Certifications</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <?php foreach ($certs as $cert): ?>
                            <div class="bg-amber-50/50 p-6 rounded-[2.5rem] border border-amber-100 flex items-center gap-4">
                                <div class="text-3xl opacity-50">🎓</div>
                                <div>
                                    <h4 class="font-black text-slate-900 text-sm"><?php echo htmlspecialchars($cert['title']); ?></h4>
                                    <p class="text-[10px] font-bold text-amber-700 uppercase tracking-widest"><?php echo htmlspecialchars($cert['institution']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Marketplace Gigs -->
                    <div>
                        <h3 class="text-xl font-black mb-6 px-2">Productized Gigs</h3>
                        <div class="space-y-4">
                            <?php foreach ($gigs as $gig): ?>
                            <div class="bg-white p-5 rounded-[2.5rem] border border-slate-100 flex items-center gap-4 group cursor-pointer hover:border-emerald-500/20 shadow-sm hover:shadow-xl transition-all">
                                <div class="w-16 h-16 bg-slate-50 rounded-[1.5rem] flex items-center justify-center text-2xl group-hover:scale-110 transition-all overflow-hidden">
                                     <?php if ($gig['image_url']): ?>
                                        <img src="../../<?php echo htmlspecialchars($gig['image_url']); ?>" class="w-full h-full object-cover">
                                     <?php else: ?>
                                        ✨
                                     <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($gig['title']); ?></h4>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-emerald-600 uppercase">KSh <?php echo number_format($gig['price_amount']); ?></span>
                                        <?php if (!$gig['is_active']): ?>
                                            <span class="text-[8px] px-2 py-0.5 bg-slate-100 text-slate-400 rounded-full font-black uppercase">Verified</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <footer class="py-20 border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-4 text-center">
             <div class="w-16 h-16 vibrant-gradient rounded-[2rem] flex items-center justify-center text-2xl text-white font-black mx-auto mb-8 shadow-2xl">M</div>
             <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">Built with passion on MbokaHub</p>
             <p class="text-slate-300 text-[10px] mt-4">&copy; 2026 MbokaHub Technologies. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
