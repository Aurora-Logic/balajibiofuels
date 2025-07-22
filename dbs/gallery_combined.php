<?php

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Handle category filter
            $whereClause = '';
            $params = [];
            if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
                $whereClause = 'WHERE category_id = ?';
                $params[] = $_GET['category_id'];
            }
            
            // Handle search
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $searchTerm = '%' . $_GET['search'] . '%';
                if ($whereClause) {
                    $whereClause .= ' AND (title LIKE ? OR description LIKE ?)';
                } else {
                    $whereClause = 'WHERE (title LIKE ? OR description LIKE ?)';
                }
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Handle pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;
            
            // Get images
            $imageQuery = "SELECT 
                             id, 
                             title, 
                             description, 
                             image_path AS media_path, 
                             uploaded_at, 
                             category_id, 
                             'image' AS media_type 
                          FROM gallery_images";
            
            // Get videos
            $videoQuery = "SELECT 
                             id, 
                             title, 
                             description, 
                             video_path AS media_path, 
                             uploaded_at, 
                             category_id, 
                             'video' AS media_type 
                          FROM videos";
            
            // Combine queries with UNION and apply filters
            $combinedQuery = "
                SELECT * FROM (
                    ($imageQuery) 
                    UNION ALL 
                    ($videoQuery)
                ) AS combined_media";
            
            if ($whereClause) {
                $combinedQuery .= " $whereClause";
            }
            
            $combinedQuery .= " ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($combinedQuery);
            $stmt->execute($params);
            $media = $stmt->fetchAll();
            
            // Get category names
            foreach ($media as &$item) {
                if ($item['category_id']) {
                    $catStmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
                    $catStmt->execute([$item['category_id']]);
                    $category = $catStmt->fetch();
                    $item['category_name'] = $category ? $category['name'] : null;
                } else {
                    $item['category_name'] = null;
                }
            }
            
            // Get total count for pagination
            $countQuery = "
                SELECT COUNT(*) FROM (
                    (SELECT id, category_id, title, description FROM gallery_images) 
                    UNION ALL 
                    (SELECT id, category_id, title, description FROM videos)
                ) AS combined_count";
            
            $countParams = [];
            if ($whereClause) {
                $countQuery .= " $whereClause";
                // Remove LIMIT and OFFSET params for count query
                $countParams = array_slice($params, 0, -2);
            }
            
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($countParams);
            $totalItems = $countStmt->fetchColumn();
            
            echo json_encode([
                'success' => true, 
                'data' => $media,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalItems / $limit),
                    'total_items' => $totalItems,
                    'items_per_page' => $limit
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

?>
