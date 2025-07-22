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

// Helper function to handle file uploads
function handleImageUpload($file, $uploadDir = '../uploads/') {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File size too large. Maximum 5MB allowed.');
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return 'uploads/' . $filename; // Return relative path for database
}

switch ($method) {
    case 'GET':
        try {
            // Handle single image request
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare('SELECT gi.*, c.name AS category_name FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id WHERE gi.id = ?');
                $stmt->execute([$_GET['id']]);
                $image = $stmt->fetch();
                
                if ($image) {
                    echo json_encode(['success' => true, 'data' => $image]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Image not found']);
                }
                break;
            }
            
            // Handle category filter
            $whereClause = '';
            $params = [];
            if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
                $whereClause = 'WHERE gi.category_id = ?';
                $params[] = $_GET['category_id'];
            }
            
            // Handle search
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $searchTerm = '%' . $_GET['search'] . '%';
                if ($whereClause) {
                    $whereClause .= ' AND (gi.title LIKE ? OR gi.description LIKE ?)';
                } else {
                    $whereClause = 'WHERE (gi.title LIKE ? OR gi.description LIKE ?)';
                }
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Handle pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $countQuery = "SELECT COUNT(*) FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id $whereClause";
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalImages = $countStmt->fetchColumn();
            
            // Get images with pagination
            $query = "SELECT gi.id, gi.title, gi.description, gi.image_path, gi.uploaded_at, gi.category_id, c.name AS category_name 
                     FROM gallery_images gi 
                     LEFT JOIN categories c ON gi.category_id = c.id 
                     $whereClause 
                     ORDER BY gi.uploaded_at DESC 
                     LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $images = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'data' => $images,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalImages / $limit),
                    'total_items' => $totalImages,
                    'items_per_page' => $limit
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $action = $_POST['action'] ?? 'create';
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $categoryId = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            
            if ($action === 'update') {
                // Handle update operation
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    throw new Exception('Image ID is required for update');
                }
                
                // Check if image exists
                $checkStmt = $pdo->prepare('SELECT id, title, image_path FROM gallery_images WHERE id = ?');
                $checkStmt->execute([$id]);
                $existingImage = $checkStmt->fetch();
                
                if (!$existingImage) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Image not found']);
                    break;
                }
                
                // Handle optional file replacement
                $imagePath = $existingImage['image_path']; // Keep existing path by default
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    // Delete old file
                    $oldFullPath = '../' . $existingImage['image_path'];
                    if (file_exists($oldFullPath)) {
                        unlink($oldFullPath);
                    }
                    // Upload new file
                    $imagePath = handleImageUpload($_FILES['image']);
                }
                
                // Update database
                $stmt = $pdo->prepare('UPDATE gallery_images SET title = ?, description = ?, category_id = ?, image_path = ? WHERE id = ?');
                $stmt->execute([$title, $description, $categoryId, $imagePath, $id]);
                
                // Log activity
                $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                $activityStmt->execute(['image_update', "Image updated: $title"]);
                
                // Get updated image
                $getStmt = $pdo->prepare('SELECT gi.*, c.name AS category_name FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id WHERE gi.id = ?');
                $getStmt->execute([$id]);
                $updatedImage = $getStmt->fetch();
                
                echo json_encode(['success' => true, 'data' => $updatedImage, 'message' => 'Image updated successfully']);
                
            } else {
                // Handle create operation
                if (!isset($_FILES['image'])) {
                    throw new Exception('No image file provided');
                }
                
                $imagePath = handleImageUpload($_FILES['image']);
                
                $stmt = $pdo->prepare('INSERT INTO gallery_images (title, description, category_id, image_path) VALUES (?, ?, ?, ?)');
                $stmt->execute([$title, $description, $categoryId, $imagePath]);
                
                $imageId = $pdo->lastInsertId();
                
                // Log activity
                $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                $activityStmt->execute(['image_upload', "New image uploaded: $title"]);
                
                // Get the created image
                $getStmt = $pdo->prepare('SELECT gi.*, c.name AS category_name FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id WHERE gi.id = ?');
                $getStmt->execute([$imageId]);
                $newImage = $getStmt->fetch();
                
                echo json_encode(['success' => true, 'data' => $newImage, 'message' => 'Image uploaded successfully']);
            }
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
                throw new Exception('Image ID is required');
            }
            
            $id = $input['id'];
            $title = $input['title'] ?? '';
            $description = $input['description'] ?? '';
            $categoryId = !empty($input['category_id']) ? $input['category_id'] : null;
            
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            
            // Check if image exists
            $checkStmt = $pdo->prepare('SELECT id, title FROM gallery_images WHERE id = ?');
            $checkStmt->execute([$id]);
            $existingImage = $checkStmt->fetch();
            
            if (!$existingImage) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Image not found']);
                break;
            }
            
            $stmt = $pdo->prepare('UPDATE gallery_images SET title = ?, description = ?, category_id = ? WHERE id = ?');
            $stmt->execute([$title, $description, $categoryId, $id]);
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['image_update', "Image updated: $title"]);
            
            // Get updated image
            $getStmt = $pdo->prepare('SELECT gi.*, c.name AS category_name FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id WHERE gi.id = ?');
            $getStmt->execute([$id]);
            $updatedImage = $getStmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $updatedImage, 'message' => 'Image updated successfully']);
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
                throw new Exception('Image ID is required');
            }
            
            // Get image info before deletion
            $getStmt = $pdo->prepare('SELECT title, image_path FROM gallery_images WHERE id = ?');
            $getStmt->execute([$id]);
            $image = $getStmt->fetch();
            
            if (!$image) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Image not found']);
                break;
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM gallery_images WHERE id = ?');
            $stmt->execute([$id]);
            
            // Delete physical file
            $fullPath = '../' . $image['image_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['image_delete', "Image deleted: " . $image['title']]);
            
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
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