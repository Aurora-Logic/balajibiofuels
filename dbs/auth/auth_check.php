<?php
// admin/auth_check.php
// Simple authentication check for API endpoints

session_start();

// Function to check if user is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// If this file is being accessed directly (not included), return JSON response
if (basename($_SERVER['PHP_SELF']) === 'auth_check.php') {
    header('Content-Type: application/json');
    
    if (!isAdminLoggedIn()) {
        echo json_encode([
            'logged_in' => false,
            'message' => 'Not logged in'
        ]);
        exit;
    }

    // Return logged in status
    echo json_encode([
        'logged_in' => true,
        'admin_id' => $_SESSION['admin_id'] ?? null,
        'admin_name' => $_SESSION['admin_name'] ?? null,
        'admin_phone' => $_SESSION['admin_phone'] ?? null
    ]);
    exit;
}

// If included by another file, just check authentication silently
if (!isAdminLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please login first.'
    ]);
    exit;
}

// Helper functions for included files
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

function getCurrentAdminPhone() {
    return $_SESSION['admin_phone'] ?? null;
}

function getCurrentAdminName() {
    return $_SESSION['admin_name'] ?? null;
}
?>
