<?php
// admin/login_process.php
// Process admin login form

require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Phone and password are required']);
    exit;
}

// Clean phone number (remove non-digits except +)
$phone = preg_replace('/[^\d+]/', '', $phone);

// Attempt login
if ($auth->login($phone, $password)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid phone number or password'
    ]);
}
?>
