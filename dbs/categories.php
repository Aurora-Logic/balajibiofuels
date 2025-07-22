<?php

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        try {
            // Handle single category request
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare('
                    SELECT c.*, 
                           COUNT(DISTINCT gi.id) as image_count,
                           COUNT(DISTINCT v.id) as video_count
                    FROM categories c 
                    LEFT JOIN gallery_images gi ON c.id = gi.category_id 
                    LEFT JOIN videos v ON c.id = v.category_id
                    WHERE c.id = ? 
                    GROUP BY c.id
                ');
                $stmt->execute([$_GET['id']]);
                $category = $stmt->fetch();
                
                if ($category) {
                    echo json_encode(['success' => true, 'data' => $category]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Category not found']);
                }
                break;
            }
            
            // Get all categories with image and video counts
            $stmt = $pdo->query('
                SELECT c.*, 
                       COUNT(DISTINCT gi.id) as image_count,
                       COUNT(DISTINCT v.id) as video_count
                FROM categories c 
                LEFT JOIN gallery_images gi ON c.id = gi.category_id 
                LEFT JOIN videos v ON c.id = v.category_id
                GROUP BY c.id 
                ORDER BY c.name ASC
            ');
            $categories = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $categories]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            // Check if category already exists
            $checkStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ?');
            $checkStmt->execute([$name]);
            if ($checkStmt->fetch()) {
                throw new Exception('Category with this name already exists');
            }
            
            $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            
            $categoryId = $pdo->lastInsertId();
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['category_create', "New category created: $name"]);
            
            // Get the created category
            $getStmt = $pdo->prepare('
                SELECT c.*, 
                       COUNT(DISTINCT gi.id) as image_count,
                       COUNT(DISTINCT v.id) as video_count
                FROM categories c 
                LEFT JOIN gallery_images gi ON c.id = gi.category_id 
                LEFT JOIN videos v ON c.id = v.category_id
                WHERE c.id = ? 
                GROUP BY c.id
            ');
            $getStmt->execute([$categoryId]);
            $newCategory = $getStmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $newCategory, 'message' => 'Category created successfully']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        try {
            if (!isset($input['id'])) {
                throw new Exception('Category ID is required');
            }
            
            $id = $input['id'];
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            // Check if category exists
            $checkStmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Category not found']);
                break;
            }
            
            // Check if another category with the same name exists
            $duplicateStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id != ?');
            $duplicateStmt->execute([$name, $id]);
            if ($duplicateStmt->fetch()) {
                throw new Exception('Another category with this name already exists');
            }
            
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $description, $id]);
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['category_update', "Category updated: $name"]);
            
            // Get updated category
            $getStmt = $pdo->prepare('
                SELECT c.*, 
                       COUNT(DISTINCT gi.id) as image_count,
                       COUNT(DISTINCT v.id) as video_count
                FROM categories c 
                LEFT JOIN gallery_images gi ON c.id = gi.category_id 
                LEFT JOIN videos v ON c.id = v.category_id
                WHERE c.id = ? 
                GROUP BY c.id
            ');
            $getStmt->execute([$id]);
            $updatedCategory = $getStmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $updatedCategory, 'message' => 'Category updated successfully']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        try {
            $id = $input['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('Category ID is required');
            }
            
            // Get category info and check if it has images or videos
            $getStmt = $pdo->prepare('
                SELECT c.name, 
                       COUNT(DISTINCT gi.id) as image_count,
                       COUNT(DISTINCT v.id) as video_count
                FROM categories c 
                LEFT JOIN gallery_images gi ON c.id = gi.category_id 
                LEFT JOIN videos v ON c.id = v.category_id
                WHERE c.id = ? 
                GROUP BY c.id
            ');
            $getStmt->execute([$id]);
            $category = $getStmt->fetch();
            
            if (!$category) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Category not found']);
                break;
            }
            
            if ($category['image_count'] > 0 || $category['video_count'] > 0) {
                // Update images and videos to have no category instead of preventing deletion
                $updateImagesStmt = $pdo->prepare('UPDATE gallery_images SET category_id = NULL WHERE category_id = ?');
                $updateImagesStmt->execute([$id]);
                
                $updateVideosStmt = $pdo->prepare('UPDATE videos SET category_id = NULL WHERE category_id = ?');
                $updateVideosStmt->execute([$id]);
            }
            
            // Delete category
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['category_delete', "Category deleted: " . $category['name']]);
            
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        break;
}
