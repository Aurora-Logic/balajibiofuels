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
            // Get dashboard statistics
            $stats = [];
            
            // Total images
            $imageStmt = $pdo->query('SELECT COUNT(*) as count FROM gallery_images');
            $stats['total_images'] = (int)$imageStmt->fetch()['count'];
            
            // Total videos
            $videoStmt = $pdo->query('SELECT COUNT(*) as count FROM videos');
            $stats['total_videos'] = (int)$videoStmt->fetch()['count'];
            
            // Total categories
            $categoryStmt = $pdo->query('SELECT COUNT(*) as count FROM categories');
            $stats['total_categories'] = (int)$categoryStmt->fetch()['count'];
            
            // Recent uploads (last 30 days)
            $recentUploadsStmt = $pdo->query('
                SELECT COUNT(*) as count FROM (
                    SELECT uploaded_at FROM gallery_images WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    UNION ALL
                    SELECT uploaded_at FROM videos WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ) as recent
            ');
            $stats['recent_uploads'] = (int)$recentUploadsStmt->fetch()['count'];
            
            // Recent activity (last 10 activities)
            $activityStmt = $pdo->query('SELECT activity_type, description, created_at FROM activity_log ORDER BY created_at DESC LIMIT 10');
            $stats['recent_activity'] = $activityStmt->fetchAll();
            
            // Category distribution
            $categoryDistStmt = $pdo->query('
                SELECT c.name, COUNT(gi.id) as image_count, COUNT(v.id) as video_count
                FROM categories c 
                LEFT JOIN gallery_images gi ON c.id = gi.category_id 
                LEFT JOIN videos v ON c.id = v.category_id 
                GROUP BY c.id, c.name 
                ORDER BY (COUNT(gi.id) + COUNT(v.id)) DESC
            ');
            $stats['category_distribution'] = $categoryDistStmt->fetchAll();
            
            // Upload trend (last 12 months)
            $trendStmt = $pdo->query('
                SELECT 
                    DATE_FORMAT(month_year, "%Y-%m") as month,
                    COALESCE(SUM(image_count), 0) as images,
                    COALESCE(SUM(video_count), 0) as videos
                FROM (
                    SELECT 
                        DATE_FORMAT(uploaded_at, "%Y-%m-01") as month_year,
                        COUNT(*) as image_count,
                        0 as video_count
                    FROM gallery_images 
                    WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(uploaded_at, "%Y-%m")
                    
                    UNION ALL
                    
                    SELECT 
                        DATE_FORMAT(uploaded_at, "%Y-%m-01") as month_year,
                        0 as image_count,
                        COUNT(*) as video_count
                    FROM videos 
                    WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(uploaded_at, "%Y-%m")
                ) as combined
                GROUP BY month_year
                ORDER BY month_year ASC
            ');
            $stats['upload_trend'] = $trendStmt->fetchAll();
            
            // Storage usage
            $storageStmt = $pdo->query('
                SELECT 
                    "images" as type,
                    COUNT(*) as count,
                    SUM(
                        CASE 
                            WHEN image_path IS NOT NULL THEN 1
                            ELSE 0
                        END
                    ) as file_count
                FROM gallery_images
                UNION ALL
                SELECT 
                    "videos" as type,
                    COUNT(*) as count,
                    SUM(
                        CASE 
                            WHEN video_path IS NOT NULL THEN 1
                            ELSE 0
                        END
                    ) as file_count
                FROM videos
            ');
            $stats['storage_info'] = $storageStmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $stats]);
            
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
