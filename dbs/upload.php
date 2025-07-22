<?php

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// Helper function to handle file uploads
function handleFileUpload($file, $type = 'image', $uploadDir = '../uploads/') {
    if ($type === 'image') {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $prefix = 'img_';
    } elseif ($type === 'video') {
        $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'];
        $maxSize = 100 * 1024 * 1024; // 100MB
        $prefix = 'vid_';
    } else {
        throw new Exception('Invalid file type specified');
    }
    
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Get file info
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
    }
    
    if ($file['size'] > $maxSize) {
        $maxSizeMB = $maxSize / (1024 * 1024);
        throw new Exception("File size too large. Maximum {$maxSizeMB}MB allowed.");
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return [
        'filename' => $filename,
        'path' => 'uploads/' . $filename,
        'size' => $file['size'],
        'type' => $mimeType,
        'original_name' => $file['name']
    ];
}

switch ($method) {
    case 'POST':
        try {
            if (!isset($_FILES) || empty($_FILES)) {
                throw new Exception('No files uploaded');
            }
            
            $uploadType = $_POST['upload_type'] ?? 'image';
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $categoryId = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            
            $uploadedFiles = [];
            $errors = [];
            
            // Handle multiple file uploads
            foreach ($_FILES as $fieldName => $file) {
                try {
                    // Handle array of files
                    if (is_array($file['name'])) {
                        for ($i = 0; $i < count($file['name']); $i++) {
                            if ($file['error'][$i] === UPLOAD_ERR_OK) {
                                $singleFile = [
                                    'name' => $file['name'][$i],
                                    'type' => $file['type'][$i],
                                    'tmp_name' => $file['tmp_name'][$i],
                                    'error' => $file['error'][$i],
                                    'size' => $file['size'][$i]
                                ];
                                
                                $fileInfo = handleFileUpload($singleFile, $uploadType);
                                
                                // Save to database based on type
                                if ($uploadType === 'image') {
                                    $stmt = $pdo->prepare('INSERT INTO gallery_images (title, description, category_id, image_path) VALUES (?, ?, ?, ?)');
                                    $stmt->execute([$title . ' ' . ($i + 1), $description, $categoryId, $fileInfo['path']]);
                                    $recordId = $pdo->lastInsertId();
                                    
                                    // Get the created record
                                    $getStmt = $pdo->prepare('SELECT gi.*, c.name AS category_name FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id WHERE gi.id = ?');
                                    $getStmt->execute([$recordId]);
                                    $record = $getStmt->fetch();
                                } else {
                                    $stmt = $pdo->prepare('INSERT INTO videos (title, description, category_id, video_path) VALUES (?, ?, ?, ?)');
                                    $stmt->execute([$title . ' ' . ($i + 1), $description, $categoryId, $fileInfo['path']]);
                                    $recordId = $pdo->lastInsertId();
                                    
                                    // Get the created record
                                    $getStmt = $pdo->prepare('SELECT v.*, c.name AS category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?');
                                    $getStmt->execute([$recordId]);
                                    $record = $getStmt->fetch();
                                }
                                
                                $uploadedFiles[] = array_merge($fileInfo, ['record' => $record]);
                            }
                        }
                    } else {
                        // Handle single file
                        if ($file['error'] === UPLOAD_ERR_OK) {
                            $fileInfo = handleFileUpload($file, $uploadType);
                            
                            // Save to database based on type
                            if ($uploadType === 'image') {
                                $stmt = $pdo->prepare('INSERT INTO gallery_images (title, description, category_id, image_path) VALUES (?, ?, ?, ?)');
                                $stmt->execute([$title, $description, $categoryId, $fileInfo['path']]);
                                $recordId = $pdo->lastInsertId();
                                
                                // Get the created record
                                $getStmt = $pdo->prepare('SELECT gi.*, c.name AS category_name FROM gallery_images gi LEFT JOIN categories c ON gi.category_id = c.id WHERE gi.id = ?');
                                $getStmt->execute([$recordId]);
                                $record = $getStmt->fetch();
                            } else {
                                $stmt = $pdo->prepare('INSERT INTO videos (title, description, category_id, video_path) VALUES (?, ?, ?, ?)');
                                $stmt->execute([$title, $description, $categoryId, $fileInfo['path']]);
                                $recordId = $pdo->lastInsertId();
                                
                                // Get the created record
                                $getStmt = $pdo->prepare('SELECT v.*, c.name AS category_name FROM videos v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?');
                                $getStmt->execute([$recordId]);
                                $record = $getStmt->fetch();
                            }
                            
                            $uploadedFiles[] = array_merge($fileInfo, ['record' => $record]);
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = ['file' => $fieldName, 'error' => $e->getMessage()];
                }
            }
            
            // Log activity
            if (!empty($uploadedFiles)) {
                $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                $activityStmt->execute([
                    $uploadType . '_upload', 
                    count($uploadedFiles) . " {$uploadType}(s) uploaded: $title"
                ]);
            }
            
            $response = [
                'success' => !empty($uploadedFiles),
                'data' => $uploadedFiles,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
            ];
            
            if (!empty($errors)) {
                $response['errors'] = $errors;
                $response['message'] .= ' with ' . count($errors) . ' error(s)';
            }
            
            echo json_encode($response);
            
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
