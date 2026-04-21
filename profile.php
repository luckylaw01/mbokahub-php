<?php
require_once "includes/translations.php";
session_start();

// Auth Check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$current_lang = $_SESSION["current_lang"] ?? "en";
$t = $lang[$current_lang];
$user_name = $_SESSION["name"];
$role = $_SESSION["role"];

// Mock Profile Data
$profile = [
    "rating" => 4.9,
    "reviews" => 84,
    "completed" => 126,
    "joined" => "Jan 2024",
    "specialty" => ($role === "fundi") ? "Master Plumber" : "Property Manager",
    "location" => "Nairobi, Westlands"
];

$page_title = $user_name . " - MbokaHub Profile";
include "includes/header.php";
?>

    <main class="max-w-5xl mx-auto p-4 md:p-8">
        <!-- Profile Header Card -->
        <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-slate-200 border border-slate-50 relative overflow-hidden mb-8">
            <div class="absolute top-0 right-0 w-64 h-64 vibrant-gradient opacity-10 blur-3xl -mr-32 -mt-32"></div>
            
            <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                <div class="relative">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-[2.5rem] bg-slate-100 flex items-center justify-center text-4xl md:text-5xl font-black text-slate-300 border-4 border-white shadow-xl">
                        <?php echo substr($user_name, 0, 1); ?>
                    </div>
                    <?php if ($role === "fundi"): ?>
                    <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white w-10 h-10 rounded-2xl flex items-center justify-center border-4 border-white shadow-lg">
                        <i class="fas fa-check text-xs"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="text-center md:text-left flex-1">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-2"><?php echo htmlspecialchars($user_name); ?></h2>
                    <p class="text-slate-500 font-bold mb-6 flex items-center justify-center md:justify-start gap-2 uppercase tracking-widest text-xs">
                        <i class="fas fa-hammer text-emerald-500"></i>
                        <?php echo $profile["specialty"]; ?>
                        <span class="text-slate-200">|</span>
                        <i class="fas fa-location-dot text-blue-500"></i>
                        <?php echo $profile["location"]; ?>
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1"><?php echo $t["trust_index"] ?? "Trust Index"; ?></span>
                            <span class="text-xl font-black text-slate-900"><?php echo $profile["rating"]; ?> <i class="fas fa-star text-amber-400 text-sm"></i></span>
                        </div>
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">Completed</span>
                            <span class="text-xl font-black text-slate-900"><?php echo $profile["completed"]; ?>+</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 w-full md:w-auto">
                    <button class="w-full md:w-48 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl hover:scale-105 transition-all">Edit Profile</button>
                    <a href="logout.php" class="w-full md:w-48 py-4 bg-rose-50 text-rose-600 text-center rounded-2xl font-bold hover:bg-rose-100 transition-all">Sign Out</a>
                </div>
            </div>
        </div>

        <!-- Portfolio Scaffolding (If Fundi) -->
        <?php if ($role === "fundi"): ?>
        <section class="mb-12">
            <h3 class="text-2xl font-black mb-6 px-2"><?php echo $t["view_portfolio"] ?? "Portfolio"; ?></h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="aspect-square bg-slate-100 rounded-[2rem] border-4 border-dashed border-slate-200 flex items-center justify-center group cursor-pointer hover:bg-white transition-all">
                    <i class="fas fa-plus text-slate-300 group-hover:text-emerald-500 text-2xl"></i>
                </div>
                <!-- Mock Items -->
                <div class="aspect-square bg-slate-200 rounded-[2rem] overflow-hidden relative group">
                   <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                       <span class="text-white font-bold text-xs uppercase tracking-widest">Kitchen Repair</span>
                   </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

<?php include "includes/footer.php"; ?>
