<?php

require_once __DIR__ . '/auth/auth_check.php'; // Add authentication check
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

// Helper function to get settings from database
function getSettings($pdo) {
    try {
        $stmt = $pdo->query('SELECT key_name, value FROM settings');
        $settings = $stmt->fetchAll();
        
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['key_name']] = $setting['value'];
        }
        
        return $settingsArray;
    } catch (Exception $e) {
        // Return default values if settings table doesn't exist or has issues
        return [
            'max_image_size' => '10',
            'max_video_size' => '100'
        ];
    }
}

// Helper function to handle video uploads
function handleVideoUpload($file, $uploadDir = '../uploads/') {
    global $pdo;
    
    $settings = getSettings($pdo);
    $maxSizeMB = isset($settings['max_video_size']) ? (int)$settings['max_video_size'] : 100;
    
    $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'];
    $maxSize = $maxSizeMB * 1024 * 1024; // Convert MB to bytes
    
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only MP4, AVI, MOV, WMV, and WebM are allowed.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception("File size too large. Maximum {$maxSizeMB}MB allowed.");
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'vid_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return 'uploads/' . $filename; // Return relative path for database
}

// Helper function to get video duration (if ffmpeg is available)
function getVideoDuration($videoPath) {
    // This is a basic implementation. For production, consider using FFmpeg
    return null; // Return null if duration cannot be determined
}

switch ($method) {
    case 'GET':
        try {
            // Handle single video request
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare('SELECT v.*, c.name AS category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?');
                $stmt->execute([$_GET['id']]);
                $video = $stmt->fetch();
                
                if ($video) {
                    echo json_encode(['success' => true, 'data' => $video]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Video not found']);
                }
                break;
            }
            
            // Handle category filter
            $whereClause = '';
            $params = [];
            if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
                $whereClause = 'WHERE v.category_id = ?';
                $params[] = $_GET['category_id'];
            }
            
            // Handle search
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $searchTerm = '%' . $_GET['search'] . '%';
                if ($whereClause) {
                    $whereClause .= ' AND (v.title LIKE ? OR v.description LIKE ?)';
                } else {
                    $whereClause = 'WHERE (v.title LIKE ? OR v.description LIKE ?)';
                }
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Handle pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $countQuery = "SELECT COUNT(*) FROM videos v LEFT JOIN categories c ON v.category_id = c.id $whereClause";
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalVideos = $countStmt->fetchColumn();
            
            // Get videos with pagination
            $query = "SELECT v.id, v.title, v.description, v.video_path, v.duration, v.uploaded_at, v.category_id, c.name AS category_name 
                     FROM videos v 
                     LEFT JOIN categories c ON v.category_id = c.id 
                     $whereClause 
                     ORDER BY v.uploaded_at DESC 
                     LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $videos = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'data' => $videos,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalVideos / $limit),
                    'total_items' => $totalVideos,
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
                    throw new Exception('Video ID is required for update');
                }
                
                // Check if video exists
                $checkStmt = $pdo->prepare('SELECT id, title, video_path, duration FROM videos WHERE id = ?');
                $checkStmt->execute([$id]);
                $existingVideo = $checkStmt->fetch();
                
                if (!$existingVideo) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Video not found']);
                    break;
                }
                
                // Handle optional file replacement and duration
                $videoPath = $existingVideo['video_path']; // Keep existing path by default
                $duration = $existingVideo['duration']; // Keep existing duration by default
                
                if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
                    // Delete old file
                    $oldFullPath = '../' . $existingVideo['video_path'];
                    if (file_exists($oldFullPath)) {
                        unlink($oldFullPath);
                    }
                    // Upload new file
                    $videoPath = handleVideoUpload($_FILES['video']);
                    $duration = getVideoDuration('../' . $videoPath);
                } else {
                    // Use duration from form if provided (from JavaScript automatic detection)
                    $formDuration = $_POST['duration'] ?? null;
                    if ($formDuration !== null && is_numeric($formDuration)) {
                        $duration = (int)$formDuration;
                    }
                }
                
                // Update database
                $stmt = $pdo->prepare('UPDATE videos SET title = ?, description = ?, category_id = ?, video_path = ?, duration = ? WHERE id = ?');
                $stmt->execute([$title, $description, $categoryId, $videoPath, $duration, $id]);
                
                // Log activity
                $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                $activityStmt->execute(['video_update', "Video updated: $title"]);
                
                // Get updated video
                $getStmt = $pdo->prepare('SELECT v.*, c.name AS category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?');
                $getStmt->execute([$id]);
                $updatedVideo = $getStmt->fetch();
                
                echo json_encode(['success' => true, 'data' => $updatedVideo, 'message' => 'Video updated successfully']);
                
            } else {
                // Handle create operation
                if (!isset($_FILES['video'])) {
                    throw new Exception('No video file provided');
                }
                
                $videoPath = handleVideoUpload($_FILES['video']);
                
                // Get duration from form (JavaScript detection) or calculate server-side
                $duration = $_POST['duration'] ?? null;
                if ($duration === null || !is_numeric($duration)) {
                    $duration = getVideoDuration('../' . $videoPath);
                } else {
                    $duration = (int)$duration;
                }
                
                $stmt = $pdo->prepare('INSERT INTO videos (title, description, category_id, video_path, duration) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$title, $description, $categoryId, $videoPath, $duration]);
                
                $videoId = $pdo->lastInsertId();
                
                // Log activity
                $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                $activityStmt->execute(['video_upload', "New video uploaded: $title"]);
                
                // Get the created video
                $getStmt = $pdo->prepare('SELECT v.*, c.name AS category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?');
                $getStmt->execute([$videoId]);
                $newVideo = $getStmt->fetch();
                
                echo json_encode(['success' => true, 'data' => $newVideo, 'message' => 'Video uploaded successfully']);
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
                throw new Exception('Video ID is required');
            }
            
            $id = $input['id'];
            $title = $input['title'] ?? '';
            $description = $input['description'] ?? '';
            $categoryId = !empty($input['category_id']) ? $input['category_id'] : null;
            
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            
            // Check if video exists
            $checkStmt = $pdo->prepare('SELECT id, title FROM videos WHERE id = ?');
            $checkStmt->execute([$id]);
            $existingVideo = $checkStmt->fetch();
            
            if (!$existingVideo) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Video not found']);
                break;
            }
            
            $stmt = $pdo->prepare('UPDATE videos SET title = ?, description = ?, category_id = ? WHERE id = ?');
            $stmt->execute([$title, $description, $categoryId, $id]);
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['video_update', "Video updated: $title"]);
            
            // Get updated video
            $getStmt = $pdo->prepare('SELECT v.*, c.name AS category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?');
            $getStmt->execute([$id]);
            $updatedVideo = $getStmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $updatedVideo, 'message' => 'Video updated successfully']);
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
                throw new Exception('Video ID is required');
            }
            
            // Get video info before deletion
            $getStmt = $pdo->prepare('SELECT title, video_path FROM videos WHERE id = ?');
            $getStmt->execute([$id]);
            $video = $getStmt->fetch();
            
            if (!$video) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Video not found']);
                break;
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM videos WHERE id = ?');
            $stmt->execute([$id]);
            
            // Delete physical file
            $fullPath = '../' . $video['video_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['video_delete', "Video deleted: " . $video['title']]);
            
            echo json_encode(['success' => true, 'message' => 'Video deleted successfully']);
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
