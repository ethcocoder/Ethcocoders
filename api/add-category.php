<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Match frontend keys
    $categoryName = trim($input['grade_name'] ?? '');
    $categoryDescription = trim($input['description'] ?? '');

    if (empty($categoryName)) {
        $response['message'] = 'Category name cannot be empty.';
    } else {
        $conn = get_db_connection();

        // Check if category already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM grade_categories WHERE grade_name = ?");
        $stmt->execute([$categoryName]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'Category name already exists.';
        } else {
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO grade_categories (grade_name, description) VALUES (?, ?)");
            if ($stmt->execute([$categoryName, $categoryDescription])) {
                $response['success'] = true;
                $response['message'] = 'Category added successfully.';
            } else {
                $response['message'] = 'Failed to add category to database.';
            }
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
