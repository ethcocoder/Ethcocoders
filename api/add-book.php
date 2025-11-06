<?php
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// Get POST data
$title = trim($_POST['bookTitle'] ?? '');
$author = trim($_POST['bookAuthor'] ?? '');
$category_id = filter_var($_POST['bookCategory'] ?? '', FILTER_VALIDATE_INT);
$description = trim($_POST['bookDescription'] ?? '');
$bookSourceType = $_POST['bookSourceType'] ?? 'upload';

$file_path = null;
$cover_image = null; // You can implement cover upload later
$file_size = 0; // Initialize

// Validate required fields
if (empty($title) || empty($category_id)) {
    echo json_encode(['success' => false, 'message' => 'Book title and category are required.']);
    exit;
}

try {
    $pdo = get_db_connection();

    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM grade_categories WHERE id = ?");
    $stmt->execute([$category_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        exit;
    }

    // Handle book source
    if ($bookSourceType === 'upload') {
        if (isset($_FILES['bookFile']) && $_FILES['bookFile']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['bookFile']['tmp_name'];
            $fileName = $_FILES['bookFile']['name'];
            $fileSize = $_FILES['bookFile']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $file_size = $fileSize;
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/system-books/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $destPath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
                exit;
            }

            $file_path = '/assets/uploads/system-books/' . $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
            exit;
        }
    } elseif ($bookSourceType === 'link') {
        $bookLink = trim($_POST['bookLink'] ?? '');
        if (empty($bookLink) || !filter_var($bookLink, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid book link.']);
            exit;
        }
        $file_path = $bookLink;
        $file_size = 0;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid book source type.']);
        exit;
    }

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO system_books (title, author, grade_category_id, description, file_path, file_size, cover_image)
        VALUES (:title, :author, :category_id, :description, :file_path, :file_size, :cover_image)
    ");

    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':category_id' => $category_id,
        ':description' => $description,
        ':file_path' => $file_path,
        ':file_size' => $file_size,
        ':cover_image' => $cover_image
    ]);

    echo json_encode(['success' => true, 'message' => 'Book added successfully.']);

} catch (PDOException $e) {
    error_log("Error adding book: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>
