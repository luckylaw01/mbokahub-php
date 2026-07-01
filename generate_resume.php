<?php
/**
 * Dynamic, Print-Friendly Resume/CV Generator for MbokaHub Fundis
 */
require_once "includes/db_connect.php";
session_start();

if (!isset($_GET['id'])) {
    die("Error: Fundi ID not specified.");
}

$fundi_id = (int)$_GET['id'];

try {
    // 1. Fetch User & Profile details
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.email, u.phone, u.user_name,
               fp.bio, fp.location, fp.tvet_level, fp.rating, fp.review_count,
               c.name_en as category_name
        FROM users u
        JOIN fundi_profiles fp ON u.id = fp.user_id
        LEFT JOIN categories c ON fp.category_id = c.id
        WHERE u.id = ? AND u.role = 'fundi'
    ");
    $stmt->execute([$fundi_id]);
    $fundi = $stmt->fetch();

    if (!$fundi) {
        die("Error: Artisan profile not found.");
    }

    // 2. Fetch Experience
    $stmt = $pdo->prepare("SELECT * FROM experiences WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$fundi_id]);
    $experiences = $stmt->fetchAll();

    // 3. Fetch Certifications
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE user_id = ? ORDER BY issue_date DESC");
    $stmt->execute([$fundi_id]);
    $certs = $stmt->fetchAll();

    // 4. Fetch Gigs (Skills / Services)
    $stmt = $pdo->prepare("SELECT * FROM gigs WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC");
    $stmt->execute([$fundi_id]);
    $gigs = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$full_name = $fundi['first_name'] . ' ' . $fundi['last_name'];
$specialty = $fundi['category_name'] ?: 'Professional Artisan';
$location = $fundi['location'] ?: 'Kenya';
$email = $fundi['email'];
$phone = $fundi['phone'] ?: 'N/A';
$bio = $fundi['bio'] ?: 'Dedicated professional artisan focused on delivering exceptional quality workmanship and precision services.';
$tvet_level = $fundi['tvet_level'] && $fundi['tvet_level'] !== 'student' ? $fundi['tvet_level'] : 'Vocational Artisan';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume - <?php echo htmlspecialchars($full_name); ?></title>
    <!-- Premium Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
        }
        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
        }
        /* Custom styling for standard printing settings */
        @media print {
            body {
                background-color: #ffffff;
                color: #0f172a;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border: none !important;
            }
            .page-break-inside-avoid {
                page-break-inside: avoid;
            }
            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>
<body class="text-slate-800 antialiased p-4 md:p-12">

    <!-- Action Navigation Banners (Hidden during print) -->
    <div class="max-w-4xl mx-auto mb-8 flex items-center justify-between no-print bg-white p-4 rounded-3xl shadow-xl shadow-slate-100 border border-slate-100 animate-in slide-in-from-top-4 duration-300">
        <a href="javascript:window.history.back();" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition-all flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print();" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-2xl transition-all flex items-center gap-2 shadow-lg shadow-emerald-100">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Main Resume Canvas -->
    <main class="max-w-4xl mx-auto bg-white p-8 md:p-16 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100 print-shadow-none print:p-0">
        
        <!-- CV Header Profile Card -->
        <header class="border-b-2 border-slate-100 pb-8 mb-8 flex flex-col md:flex-row justify-between items-start gap-6">
            <div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-2"><?php echo htmlspecialchars($full_name); ?></h1>
                <p class="text-emerald-600 font-black text-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100"><?php echo htmlspecialchars($specialty); ?></span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-400 font-semibold text-xs"><?php echo htmlspecialchars($tvet_level); ?></span>
                </p>
            </div>
            
            <div class="space-y-2 text-slate-500 text-sm font-medium">
                <p class="flex items-center gap-2">
                    <i class="fas fa-envelope text-slate-400 w-4"></i>
                    <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="hover:text-emerald-500 transition-colors"><?php echo htmlspecialchars($email); ?></a>
                </p>
                <p class="flex items-center gap-2">
                    <i class="fas fa-phone text-slate-400 w-4"></i>
                    <?php echo htmlspecialchars($phone); ?>
                </p>
                <p class="flex items-center gap-2">
                    <i class="fas fa-location-dot text-slate-400 w-4"></i>
                    <?php echo htmlspecialchars($location); ?>
                </p>
                <p class="flex items-center gap-2 text-xs text-slate-400 italic mt-2">
                    <i class="fas fa-globe text-slate-300 w-4"></i>
                    mbokahub.pro/<?php echo htmlspecialchars($fundi['user_name']); ?>
                </p>
            </div>
        </header>

        <!-- Professional Summary -->
        <section class="mb-10 page-break-inside-avoid">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-wider border-l-4 border-emerald-500 pl-3 mb-4">Professional Summary</h2>
            <p class="text-slate-600 leading-relaxed text-sm md:text-base font-light">
                <?php echo nl2br(htmlspecialchars($bio)); ?>
            </p>
        </section>

        <!-- Work History & Career Journey -->
        <section class="mb-10">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-wider border-l-4 border-emerald-500 pl-3 mb-6">Work Experience</h2>
            <?php if (empty($experiences)): ?>
                <p class="text-slate-400 italic text-sm">No work experience listed yet.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($experiences as $exp): ?>
                        <div class="page-break-inside-avoid border-l-2 border-slate-100 pl-5 ml-2 relative">
                            <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-slate-300"></div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 mb-2">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($exp['role']); ?></h3>
                                    <span class="text-sm font-semibold text-emerald-600"><?php echo htmlspecialchars($exp['company']); ?></span>
                                </div>
                                <span class="text-xs font-bold text-slate-400 bg-slate-50 border border-slate-100 px-3 py-1 rounded-full shrink-0">
                                    <?php echo date("M Y", strtotime($exp['start_date'])); ?> - <?php echo $exp['end_date'] ? date("M Y", strtotime($exp['end_date'])) : "Present"; ?>
                                </span>
                            </div>
                            <p class="text-slate-500 text-sm leading-relaxed font-light"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Education & Certifications -->
        <section class="mb-10 page-break-inside-avoid">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-wider border-l-4 border-emerald-500 pl-3 mb-6">Certifications & Credentials</h2>
            <?php if (empty($certs)): ?>
                <p class="text-slate-400 italic text-sm">No credentials/certifications registered.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($certs as $cert): ?>
                        <div class="bg-slate-50 border border-slate-100/60 p-5 rounded-2xl flex items-start gap-4">
                            <div class="text-2xl mt-1 opacity-70">🎓</div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($cert['title']); ?></h3>
                                <p class="text-emerald-600 font-bold text-xs uppercase tracking-wide"><?php echo htmlspecialchars($cert['institution']); ?></p>
                                <span class="text-[10px] text-slate-400 font-semibold"><?php echo date("F Y", strtotime($cert['issue_date'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Services & Capabilities (Gigs) -->
        <section class="mb-10 page-break-inside-avoid">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-wider border-l-4 border-emerald-500 pl-3 mb-6">Specialized Services</h2>
            <?php if (empty($gigs)): ?>
                <p class="text-slate-400 italic text-sm">No services listed yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($gigs as $gig): ?>
                        <div class="border border-slate-100 p-5 rounded-2xl hover:border-slate-200 transition-colors">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($gig['title']); ?></h3>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">KSh <?php echo number_format($gig['price_amount']); ?></span>
                            </div>
                            <p class="text-slate-500 text-xs leading-relaxed font-light line-clamp-3"><?php echo htmlspecialchars($gig['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Document Footer -->
        <footer class="mt-16 pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center text-[10px] text-slate-400 font-medium gap-4">
            <p>Generated dynamically via MbokaHub Pro Profile</p>
            <p>&copy; <?php echo date("Y"); ?> MbokaHub. All rights reserved.</p>
        </footer>

    </main>

</body>
</html>
