<?php
/**
 * Dynamic, Direct-Download PDF Resume/CV Generator for MbokaHub Fundis using Dompdf
 */
require_once "includes/db_connect.php";
session_start();

if (!isset($_GET['id'])) {
    die("Error: Fundi ID not specified.");
}

$fundi_id = (int)$_GET['id'];

try {
    // 1. Fetch User & Profile details (including skills)
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.email, u.phone, u.user_name,
               fp.bio, fp.location, fp.tvet_level, fp.rating, fp.review_count, fp.skills,
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

    // 4. Fetch Projects (3 Latest Gigs)
    $stmt = $pdo->prepare("SELECT * FROM gigs WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$fundi_id]);
    $gigs = $stmt->fetchAll();

    // 5. Fetch Education
    $stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$fundi_id]);
    $education = $stmt->fetchAll();

    // 6. Fetch References
    $stmt = $pdo->prepare("SELECT * FROM character_references WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$fundi_id]);
    $references = $stmt->fetchAll();

    // 7. Fetch Achievements
    $stmt = $pdo->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY date_awarded DESC, created_at DESC");
    $stmt->execute([$fundi_id]);
    $achievements = $stmt->fetchAll();

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
$skills = $fundi['skills'] ?: '';

// Load Dompdf Autoloader
require_once "src/dompdf/autoload.inc.php";
use Dompdf\Dompdf;
use Dompdf\Options;

// Configure options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

// Build beautiful, CV-compliant 2-column A4 HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume - <?php echo htmlspecialchars($full_name); ?></title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-title-cell {
            vertical-align: top;
        }
        .header-contact-cell {
            text-align: right;
            vertical-align: top;
            color: #64748b;
            font-size: 10px;
        }
        h1 {
            font-size: 26px;
            color: #0f172a;
            margin: 0 0 5px 0;
            font-weight: bold;
            line-height: 1.1;
        }
        .specialty {
            font-size: 12px;
            color: #059669;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tvet-tag {
            color: #64748b;
            font-size: 10px;
            font-weight: 500;
        }
        .main-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .sidebar-col {
            width: 32%;
            vertical-align: top;
            padding-right: 18px;
            border-right: 1px solid #f1f5f9;
        }
        .content-col {
            width: 68%;
            vertical-align: top;
            padding-left: 20px;
        }
        h2 {
            font-size: 11px;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 22px;
        }
        .skill-tag {
            display: inline-block;
            background-color: #f8fafc;
            color: #334155;
            padding: 4px 7px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            margin-right: 4px;
            margin-bottom: 5px;
            font-size: 9px;
            font-weight: bold;
        }
        .cert-card {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .cert-title {
            font-weight: bold;
            font-size: 10px;
            color: #0f172a;
        }
        .cert-inst {
            color: #059669;
            font-size: 9px;
            font-weight: bold;
            margin: 2px 0;
        }
        .cert-date {
            color: #94a3b8;
            font-size: 8px;
        }
        .ref-card {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .ref-name {
            font-weight: bold;
            font-size: 10px;
            color: #0f172a;
        }
        .ref-org {
            color: #059669;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ref-phone {
            color: #64748b;
            font-size: 9px;
            margin-top: 3px;
        }
        .summary-text {
            color: #475569;
            font-size: 11px;
            line-height: 1.5;
        }
        .timeline-item {
            margin-bottom: 12px;
            padding-left: 8px;
            border-left: 2px solid #e2e8f0;
        }
        .timeline-title {
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
        }
        .timeline-meta {
            font-size: 9px;
            color: #64748b;
            margin: 2px 0;
        }
        .timeline-company {
            color: #059669;
            font-weight: bold;
        }
        .timeline-desc {
            color: #475569;
            font-size: 10px;
            margin-top: 4px;
        }
        .project-table {
            width: 100%;
        }
        .project-card {
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .project-title {
            font-weight: bold;
            font-size: 10px;
            color: #0f172a;
        }
        .project-rate {
            color: #059669;
            font-weight: bold;
            font-size: 10px;
            text-align: right;
        }
        .project-desc {
            color: #64748b;
            font-size: 9px;
            margin-top: 3px;
        }
        .ach-card {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .ach-title {
            font-weight: bold;
            font-size: 10px;
            color: #0f172a;
        }
        .ach-desc {
            color: #475569;
            font-size: 9px;
            margin-top: 2px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="header-title-cell">
                <h1><?php echo htmlspecialchars($full_name); ?></h1>
                <div class="specialty">
                    <?php echo htmlspecialchars($specialty); ?>
                    <span style="color: #cbd5e1; font-weight: normal; margin: 0 5px;">|</span>
                    <span class="tvet-tag"><?php echo htmlspecialchars($tvet_level); ?></span>
                </div>
            </td>
            <td class="header-contact-cell">
                <div style="margin-bottom: 2px;">Email: <?php echo htmlspecialchars($email); ?></div>
                <div style="margin-bottom: 2px;">Phone: <?php echo htmlspecialchars($phone); ?></div>
                <div style="margin-bottom: 2px;">Location: <?php echo htmlspecialchars($location); ?></div>
                <div style="font-style: italic; color: #94a3b8;">mbokahub.pro/<?php echo htmlspecialchars($fundi['user_name']); ?></div>
            </td>
        </tr>
    </table>

    <!-- Main Content Area -->
    <table class="main-grid">
        <tr>
            <!-- Left Sidebar (1/3 Width) -->
            <td class="sidebar-col">
                
                <!-- Skills -->
                <?php if (!empty($skills)): ?>
                <div class="section">
                    <h2>Core Skills</h2>
                    <div style="margin-top: 5px;">
                        <?php 
                        $skills_arr = array_map('trim', explode(',', $skills));
                        foreach ($skills_arr as $skill): 
                            if (!empty($skill)):
                        ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Certifications -->
                <?php if (!empty($certs)): ?>
                <div class="section">
                    <h2>Certifications</h2>
                    <?php foreach ($certs as $cert): ?>
                        <div class="cert-card">
                            <div class="cert-title"><?php echo htmlspecialchars($cert['title']); ?></div>
                            <div class="cert-inst"><?php echo htmlspecialchars($cert['institution']); ?></div>
                            <div class="cert-date"><?php echo date("F Y", strtotime($cert['issue_date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- References -->
                <?php if (!empty($references)): ?>
                <div class="section">
                    <h2>References</h2>
                    <?php foreach ($references as $ref): ?>
                        <div class="ref-card">
                            <div class="ref-name"><?php echo htmlspecialchars($ref['name']); ?></div>
                            <div class="ref-org">
                                <?php echo htmlspecialchars($ref['organization'] ?: 'Independent'); ?>
                                <?php if ($ref['relationship']): ?>
                                    (<?php echo htmlspecialchars($ref['relationship']); ?>)
                                <?php endif; ?>
                            </div>
                            <div class="ref-phone">📞 <?php echo htmlspecialchars($ref['contact_info']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </td>

            <!-- Right Content (2/3 Width) -->
            <td class="content-col">
                
                <!-- Professional Summary -->
                <div class="section">
                    <h2>Professional Summary</h2>
                    <div class="summary-text"><?php echo nl2br(htmlspecialchars($bio)); ?></div>
                </div>

                <!-- Experience -->
                <div class="section">
                    <h2>Work Experience</h2>
                    <?php if (empty($experiences)): ?>
                        <div style="color: #94a3b8; font-style: italic;">No work experience listed yet.</div>
                    <?php else: ?>
                        <?php foreach ($experiences as $exp): ?>
                            <div class="timeline-item">
                                <span class="timeline-date">
                                    <?php echo date("M Y", strtotime($exp['start_date'])); ?> - <?php echo $exp['end_date'] ? date("M Y", strtotime($exp['end_date'])) : "Present"; ?>
                                </span>
                                <div class="timeline-title"><?php echo htmlspecialchars($exp['role']); ?></div>
                                <div class="timeline-meta">
                                    at <span class="timeline-company"><?php echo htmlspecialchars($exp['company']); ?></span>
                                </div>
                                <div class="timeline-desc"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Featured Projects -->
                <div class="section">
                    <h2>Featured Projects & Services</h2>
                    <?php if (empty($gigs)): ?>
                        <div style="color: #94a3b8; font-style: italic;">No featured services listed yet.</div>
                    <?php else: ?>
                        <?php foreach ($gigs as $gig): ?>
                            <table class="project-table" style="border-collapse: collapse; margin-bottom: 8px;">
                                <tr>
                                    <td class="project-card">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td class="project-title"><?php echo htmlspecialchars($gig['title']); ?></td>
                                                <td class="project-rate">KSh <?php echo number_format($gig['price_amount']); ?></td>
                                            </tr>
                                        </table>
                                        <div class="project-desc"><?php echo htmlspecialchars($gig['description']); ?></div>
                                    </td>
                                </tr>
                            </table>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Education -->
                <div class="section">
                    <h2>Education</h2>
                    <?php if (empty($education)): ?>
                        <div style="color: #94a3b8; font-style: italic;">No education details listed yet.</div>
                    <?php else: ?>
                        <?php foreach ($education as $edu): ?>
                            <div class="timeline-item">
                                <span class="timeline-date">
                                    <?php echo date("M Y", strtotime($edu['start_date'])); ?> - <?php echo $edu['end_date'] ? date("M Y", strtotime($edu['end_date'])) : "Present"; ?>
                                </span>
                                <div class="timeline-title"><?php echo htmlspecialchars($edu['credential']); ?></div>
                                <div class="timeline-meta">
                                    at <span class="timeline-company"><?php echo htmlspecialchars($edu['institution']); ?></span>
                                </div>
                                <?php if ($edu['description']): ?>
                                    <div class="timeline-desc"><?php echo nl2br(htmlspecialchars($edu['description'])); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Achievements -->
                <?php if (!empty($achievements)): ?>
                <div class="section">
                    <h2>Key Achievements</h2>
                    <?php foreach ($achievements as $ach): ?>
                        <div class="ach-card">
                            <span class="timeline-date" style="font-size: 8px; font-weight: bold; color: #94a3b8; margin-top: 2px;">
                                <?php echo $ach['date_awarded'] ? date("F Y", strtotime($ach['date_awarded'])) : ''; ?>
                            </span>
                            <div class="ach-title">🏆 <?php echo htmlspecialchars($ach['title']); ?></div>
                            <div class="ach-desc"><?php echo nl2br(htmlspecialchars($ach['description'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Generated dynamically via MbokaHub Pro Profile &copy; <?php echo date("Y"); ?> MbokaHub. All rights reserved.
    </div>

</body>
</html>
<?php
$html = ob_get_clean();

// Load HTML to Dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF file (Force Direct Download attachment)
$dompdf->stream("Resume_" . str_replace(" ", "_", $full_name) . ".pdf", ["Attachment" => true]);
exit;
