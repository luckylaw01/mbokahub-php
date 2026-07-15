<?php
/**
 * AJAX Handler for Portfolio, Experience, and Certifications
 */
require_once '../includes/db_connect.php';
require_once '../includes/image_helper.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_portfolio':
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $completion_date = filter_input(INPUT_POST, 'completion_date', FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (!isset($_FILES['image'])) {
                echo json_encode(['success' => false, 'message' => 'Project image required.']);
                exit;
            }

            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $upload_dir = '../assets/images/portfolio/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $filename_webp = 'project_' . $user_id . '_' . time() . '.webp';
            $target_webp = $upload_dir . $filename_webp;
            $db_path_webp = 'assets/images/portfolio/' . $filename_webp;

            if (compressAndConvertToWebp($file['tmp_name'], $target_webp, 80)) {
                $db_path = $db_path_webp;
                $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_url, completion_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $description, $db_path, $completion_date]);
                echo json_encode(['success' => true]);
            } else {
                // Fallback to original format
                $filename = 'project_' . $user_id . '_' . time() . '.' . $ext;
                $target = $upload_dir . $filename;
                $db_path = 'assets/images/portfolio/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $stmt = $pdo->prepare("INSERT INTO portfolio_items (user_id, title, description, image_url, completion_date) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $description, $db_path, $completion_date]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Upload failed.']);
                }
            }
            break;

        case 'add_experience':
            $role = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS);
            $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS);
            $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);

            $stmt = $pdo->prepare("INSERT INTO experiences (user_id, role, company, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $role, $company, $start_date, $end_date, $description]);
            echo json_encode(['success' => true]);
            break;

        case 'add_cert':
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $institution = filter_input(INPUT_POST, 'institution', FILTER_SANITIZE_SPECIAL_CHARS);
            $issue_date = filter_input(INPUT_POST, 'issue_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

            $stmt = $pdo->prepare("INSERT INTO certifications (user_id, title, institution, issue_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $institution, $issue_date]);
            echo json_encode(['success' => true]);
            break;

        case 'add_gig':
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            
            $db_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $upload_dir = '../assets/images/gigs/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $filename_webp = 'gig_' . $user_id . '_' . time() . '.webp';
                $target_webp = $upload_dir . $filename_webp;
                $db_path_webp = 'assets/images/gigs/' . $filename_webp;

                if (compressAndConvertToWebp($file['tmp_name'], $target_webp, 80)) {
                    $db_path = $db_path_webp;
                } else {
                    // Fallback to original format
                    $filename = 'gig_' . $user_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                        $db_path = 'assets/images/gigs/' . $filename;
                    }
                }
            }

            // Manually created gigs are ALWAYS active (is_active = 1)
            $stmt = $pdo->prepare("INSERT INTO gigs (user_id, title, price_amount, description, image_url, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$user_id, $title, $price, $description, $db_path]);
            echo json_encode(['success' => true]);
            break;

        case 'add_education':
            $institution = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $credential = filter_input(INPUT_POST, 'credential', FILTER_SANITIZE_SPECIAL_CHARS);
            $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS);
            $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);

            $stmt = $pdo->prepare("INSERT INTO education (user_id, institution, credential, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $institution, $credential, $start_date, $end_date, $description]);
            echo json_encode(['success' => true]);
            break;

        case 'add_reference':
            $name = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $organization = filter_input(INPUT_POST, 'organization', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $relationship = filter_input(INPUT_POST, 'relationship', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $contact_info = filter_input(INPUT_POST, 'contact_info', FILTER_SANITIZE_SPECIAL_CHARS);

            $stmt = $pdo->prepare("INSERT INTO character_references (user_id, name, organization, relationship, contact_info) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $organization, $relationship, $contact_info]);
            echo json_encode(['success' => true]);
            break;

        case 'add_achievement':
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $date_awarded = filter_input(INPUT_POST, 'date_awarded', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

            $stmt = $pdo->prepare("INSERT INTO achievements (user_id, title, description, date_awarded) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $date_awarded]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_portfolio':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $completion_date = filter_input(INPUT_POST, 'completion_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

            // Verify ownership
            $check = $pdo->prepare("SELECT id, image_url FROM portfolio_items WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            $existing = $check->fetch();

            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Project not found or unauthorized.']);
                exit;
            }

            $new_image_path = $existing['image_url']; // Keep existing image by default

            // Handle optional new image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $upload_dir = '../assets/images/portfolio/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $filename_webp = 'project_' . $user_id . '_' . time() . '.webp';
                $target_webp = $upload_dir . $filename_webp;

                if (compressAndConvertToWebp($file['tmp_name'], $target_webp, 80)) {
                    $new_image_path = 'assets/images/portfolio/' . $filename_webp;
                } else {
                    $filename = 'project_' . $user_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                        $new_image_path = 'assets/images/portfolio/' . $filename;
                    }
                }
            }

            $stmt = $pdo->prepare("UPDATE portfolio_items SET title = ?, description = ?, image_url = ?, completion_date = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $new_image_path, $completion_date, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_portfolio':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);

            // Verify ownership before delete
            $check = $pdo->prepare("SELECT id FROM portfolio_items WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);

            if (!$check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Project not found or unauthorized.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM portfolio_items WHERE id = ? AND user_id = ?");
            $stmt->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_experience':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $role    = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
            $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS);
            $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS);
            $end_date   = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $check = $pdo->prepare("SELECT id FROM experiences WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $stmt = $pdo->prepare("UPDATE experiences SET role=?, company=?, start_date=?, end_date=?, description=? WHERE id=? AND user_id=?");
            $stmt->execute([$role, $company, $start_date, $end_date, $description, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_experience':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $check = $pdo->prepare("SELECT id FROM experiences WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $pdo->prepare("DELETE FROM experiences WHERE id = ? AND user_id = ?")->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_education':
            $item_id     = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $institution = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $credential  = filter_input(INPUT_POST, 'credential', FILTER_SANITIZE_SPECIAL_CHARS);
            $start_date  = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_SPECIAL_CHARS);
            $end_date    = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $check = $pdo->prepare("SELECT id FROM education WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $stmt = $pdo->prepare("UPDATE education SET institution=?, credential=?, start_date=?, end_date=?, description=? WHERE id=? AND user_id=?");
            $stmt->execute([$institution, $credential, $start_date, $end_date, $description, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_education':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $check = $pdo->prepare("SELECT id FROM education WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $pdo->prepare("DELETE FROM education WHERE id = ? AND user_id = ?")->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_achievement':
            $item_id      = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $title        = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $description  = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $date_awarded = filter_input(INPUT_POST, 'date_awarded', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $check = $pdo->prepare("SELECT id FROM achievements WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $stmt = $pdo->prepare("UPDATE achievements SET title=?, description=?, date_awarded=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $description, $date_awarded, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_achievement':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $check = $pdo->prepare("SELECT id FROM achievements WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $pdo->prepare("DELETE FROM achievements WHERE id = ? AND user_id = ?")->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_cert':
            $item_id     = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $title       = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $institution = filter_input(INPUT_POST, 'institution', FILTER_SANITIZE_SPECIAL_CHARS);
            $issue_date  = filter_input(INPUT_POST, 'issue_date', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $check = $pdo->prepare("SELECT id FROM certifications WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $stmt = $pdo->prepare("UPDATE certifications SET title=?, institution=?, issue_date=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $institution, $issue_date, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_cert':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $check = $pdo->prepare("SELECT id FROM certifications WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $pdo->prepare("DELETE FROM certifications WHERE id = ? AND user_id = ?")->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_gig':
            $item_id     = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $title       = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $check = $pdo->prepare("SELECT id FROM gigs WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $stmt = $pdo->prepare("UPDATE gigs SET title=?, price_amount=?, description=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $price, $description, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_gig':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $check = $pdo->prepare("SELECT id FROM gigs WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $pdo->prepare("DELETE FROM gigs WHERE id = ? AND user_id = ?")->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'edit_reference':
            $item_id      = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $name         = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
            $organization = filter_input(INPUT_POST, 'organization', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $relationship = filter_input(INPUT_POST, 'relationship', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
            $contact_info = filter_input(INPUT_POST, 'contact_info', FILTER_SANITIZE_SPECIAL_CHARS);
            $check = $pdo->prepare("SELECT id FROM character_references WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $stmt = $pdo->prepare("UPDATE character_references SET name=?, organization=?, relationship=?, contact_info=? WHERE id=? AND user_id=?");
            $stmt->execute([$name, $organization, $relationship, $contact_info, $item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_reference':
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $check = $pdo->prepare("SELECT id FROM character_references WHERE id = ? AND user_id = ?");
            $check->execute([$item_id, $user_id]);
            if (!$check->fetch()) { echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit; }
            $pdo->prepare("DELETE FROM character_references WHERE id = ? AND user_id = ?")->execute([$item_id, $user_id]);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
