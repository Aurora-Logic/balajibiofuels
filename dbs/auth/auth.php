<?php
// admin/auth.php
// Authentication functions and session management

session_start();

require_once __DIR__ . '/../../config/db.php';

class AdminAuth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    // Get current admin user
    public function getCurrentAdmin() {
        if ($this->isLoggedIn() && isset($_SESSION['admin_id'])) {
            $stmt = $this->pdo->prepare("SELECT id, name, phone FROM admin_users WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            return $stmt->fetch();
        }
        return null;
    }
    
    // Login with phone and password
    public function login($phone, $password) {
        $stmt = $this->pdo->prepare("SELECT id, name, phone, password_hash FROM admin_users WHERE phone = ?");
        $stmt->execute([$phone]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_phone'] = $admin['phone'];
            $_SESSION['admin_name'] = $admin['name'];
            
            // Update last login
            $updateStmt = $this->pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$admin['id']]);
            
            return true;
        }
        
        return false;
    }
    
    // Logout
    public function logout() {
        $_SESSION = array();
        session_destroy();
    }
    
    // Check if any admin user exists
    public function hasAdminUsers() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM admin_users");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error checking admin users: " . $e->getMessage());
            throw new Exception("Database error while checking admin users");
        }
    }
    
    // Create first admin user
    public function createAdmin($phone, $password, $name) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // First check if phone already exists
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE phone = ?");
            $checkStmt->execute([$phone]);
            $exists = $checkStmt->fetch();
            
            if ($exists['count'] > 0) {
                throw new Exception("Phone number already exists");
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO admin_users (name, phone, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $passwordHash]);
            return true;
            
        } catch (PDOException $e) {
            error_log("Error creating admin user: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    // Require login - redirect if not logged in
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
}

// Initialize auth object
try {
    $auth = new AdminAuth($pdo);
    
    // Auto-redirect to setup if no admin users exist (but not from setup pages)
    if (!$auth->hasAdminUsers() && 
        !in_array(basename($_SERVER['PHP_SELF']), ['setup.php', 'setup_process.php', 'init_admin_table.php', 'debug_auth.php'])) {
        header('Location: setup.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Auth initialization error: " . $e->getMessage());
    // Don't redirect on error, let the calling script handle it
}
?>
