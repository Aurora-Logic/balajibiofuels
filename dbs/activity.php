<?php

require_once __DIR__ . '/auth/auth_check.php'; // Add authentication check
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
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
            // Handle pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;
            
            // Handle filtering by activity type
            $whereClause = '';
            $params = [];
            if (isset($_GET['type']) && !empty($_GET['type'])) {
                $whereClause = 'WHERE activity_type = ?';
                $params[] = $_GET['type'];
            }
            
            // Handle date filtering
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                if ($whereClause) {
                    $whereClause .= ' AND created_at >= ?';
                } else {
                    $whereClause = 'WHERE created_at >= ?';
                }
                $params[] = $_GET['date_from'];
            }
            
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                if ($whereClause) {
                    $whereClause .= ' AND created_at <= ?';
                } else {
                    $whereClause = 'WHERE created_at <= ?';
                }
                $params[] = $_GET['date_to'] . ' 23:59:59';
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) FROM activity_log $whereClause";
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalActivities = $countStmt->fetchColumn();
            
            // Get activities with pagination
            $query = "SELECT * FROM activity_log $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $activities = $stmt->fetchAll();
            
            // Get activity type summary
            $summaryStmt = $pdo->query('SELECT activity_type, COUNT(*) as count FROM activity_log GROUP BY activity_type ORDER BY count DESC');
            $activitySummary = $summaryStmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'data' => $activities,
                'summary' => $activitySummary,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalActivities / $limit),
                    'total_items' => $totalActivities,
                    'items_per_page' => $limit
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        try {
            // Handle bulk delete (clear old logs)
            if (isset($_GET['clear_old'])) {
                $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
                
                $stmt = $pdo->prepare('DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
                $stmt->execute([$days]);
                
                $deletedCount = $stmt->rowCount();
                
                // Log this action
                $logStmt = $pdo->prepare('INSERT INTO activity_log (activity_type, description) VALUES (?, ?)');
                $logStmt->execute(['system_cleanup', "Cleared $deletedCount old activity logs (older than $days days)"]);
                
                echo json_encode(['success' => true, 'message' => "Deleted $deletedCount old activity logs"]);
                break;
            }
            
            // Handle single activity deletion
            $id = $input['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('Activity ID is required');
            }
            
            $stmt = $pdo->prepare('DELETE FROM activity_log WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Activity not found']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Activity log deleted successfully']);
            }
            
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
