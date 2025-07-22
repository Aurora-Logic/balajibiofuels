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
            // Handle single setting request
            if (isset($_GET['key'])) {
                $stmt = $pdo->prepare('SELECT * FROM settings WHERE key_name = ?');
                $stmt->execute([$_GET['key']]);
                $setting = $stmt->fetch();
                
                if ($setting) {
                    echo json_encode(['success' => true, 'data' => $setting]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Setting not found']);
                }
                break;
            }
            
            // Get all settings
            $stmt = $pdo->query('SELECT * FROM settings ORDER BY key_name ASC');
            $settings = $stmt->fetchAll();
            
            // Convert to key-value pairs for easier use
            $settingsObj = [];
            foreach ($settings as $setting) {
                $settingsObj[$setting['key_name']] = $setting['value'];
            }
            
            echo json_encode(['success' => true, 'data' => $settingsObj, 'raw' => $settings]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $key = trim($input['key'] ?? '');
            $value = $input['value'] ?? '';
            
            if (empty($key)) {
                throw new Exception('Setting key is required');
            }
            
            // Check if setting already exists
            $checkStmt = $pdo->prepare('SELECT id FROM settings WHERE key_name = ?');
            $checkStmt->execute([$key]);
            if ($checkStmt->fetch()) {
                throw new Exception('Setting with this key already exists. Use PUT to update.');
            }
            
            $stmt = $pdo->prepare('INSERT INTO settings (key_name, value) VALUES (?, ?)');
            $stmt->execute([$key, $value]);
            
            $settingId = $pdo->lastInsertId();
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['setting_create', "New setting created: $key"]);
            
            // Get the created setting
            $getStmt = $pdo->prepare('SELECT * FROM settings WHERE id = ?');
            $getStmt->execute([$settingId]);
            $newSetting = $getStmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $newSetting, 'message' => 'Setting created successfully']);
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
            // Handle bulk update
            if (isset($input['settings']) && is_array($input['settings'])) {
                $pdo->beginTransaction();
                
                try {
                    foreach ($input['settings'] as $key => $value) {
                        // Check if setting exists
                        $checkStmt = $pdo->prepare('SELECT id FROM settings WHERE key_name = ?');
                        $checkStmt->execute([$key]);
                        $existingSetting = $checkStmt->fetch();
                        
                        if ($existingSetting) {
                            // Update existing setting
                            $stmt = $pdo->prepare('UPDATE settings SET value = ? WHERE key_name = ?');
                            $stmt->execute([$value, $key]);
                        } else {
                            // Insert new setting
                            $stmt = $pdo->prepare('INSERT INTO settings (key_name, value) VALUES (?, ?)');
                            $stmt->execute([$key, $value]);
                        }
                    }
                    
                    $pdo->commit();
                    
                    // Log activity
                    $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                    $activityStmt->execute(['settings_update', "Multiple settings updated"]);
                    
                    echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                break;
            }
            
            // Handle single setting update
            $key = trim($input['key'] ?? '');
            $value = $input['value'] ?? '';
            
            if (empty($key)) {
                throw new Exception('Setting key is required');
            }
            
            // Check if setting exists
            $checkStmt = $pdo->prepare('SELECT id FROM settings WHERE key_name = ?');
            $checkStmt->execute([$key]);
            $existingSetting = $checkStmt->fetch();
            
            if ($existingSetting) {
                // Update existing setting
                $stmt = $pdo->prepare('UPDATE settings SET value = ? WHERE key_name = ?');
                $stmt->execute([$value, $key]);
            } else {
                // Insert new setting
                $stmt = $pdo->prepare('INSERT INTO settings (key_name, value) VALUES (?, ?)');
                $stmt->execute([$key, $value]);
            }
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['setting_update', "Setting updated: $key"]);
            
            // Get the updated setting
            $getStmt = $pdo->prepare('SELECT * FROM settings WHERE key_name = ?');
            $getStmt->execute([$key]);
            $updatedSetting = $getStmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $updatedSetting, 'message' => 'Setting updated successfully']);
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
            $key = $input['key'] ?? $_GET['key'] ?? null;
            
            if (!$key) {
                throw new Exception('Setting key is required');
            }
            
            // Check if setting exists
            $checkStmt = $pdo->prepare('SELECT key_name FROM settings WHERE key_name = ?');
            $checkStmt->execute([$key]);
            $setting = $checkStmt->fetch();
            
            if (!$setting) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Setting not found']);
                break;
            }
            
            // Delete setting
            $stmt = $pdo->prepare('DELETE FROM settings WHERE key_name = ?');
            $stmt->execute([$key]);
            
            // Log activity
            $activityStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
            $activityStmt->execute(['setting_delete', "Setting deleted: $key"]);
            
            echo json_encode(['success' => true, 'message' => 'Setting deleted successfully']);
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
