<?php
// admin/setup_process.php  
// Process admin setup form

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in response, log them instead
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    // Test database connection first
    require_once __DIR__ . '/../../config/db.php';
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Admin users table does not exist.'
        ]);
        exit;
    }
    
    // Initialize auth class
    require_once 'auth.php';
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'System error: ' . $e->getMessage()
    ]);
    exit;
}

// Check if already has admin users
try {
    if ($auth->hasAdminUsers()) {
        echo json_encode(['success' => false, 'message' => 'Admin user already exists']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error checking existing users: ' . $e->getMessage()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validation
if (empty($name) || empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Name, phone, and password are required']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

// Validate phone number format (basic validation)
if (strlen($phone) < 10) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
    exit;
}

// Clean phone number (remove non-digits except +)
$phone = preg_replace('/[^\d+]/', '', $phone);

// Create admin user
try {
    if ($auth->createAdmin($phone, $password, $name)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Admin account created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create admin account. Phone number may already exist or database error occurred.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error creating admin account: ' . $e->getMessage()
    ]);
}
?>
