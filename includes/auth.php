<?php
// includes/auth.php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($data) {
        $conn = $this->db->getConnection();
        
        // Validate data
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if email exists
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'errors' => ['email' => 'Email already exists']];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));
        
        // Insert user
        $sql = "INSERT INTO users (first_name, last_name, email, password, phone, verification_token) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", 
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashedPassword,
            $data['phone'],
            $verificationToken
        );
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            
            // Send verification email
            $this->sendVerificationEmail($data['email'], $verificationToken);
            
            // Auto login after registration
            $this->login($data['email'], $data['password']);
            
            return ['success' => true, 'user_id' => $userId];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Registration failed. Please try again.']];
        }
    }
    
    public function login($email, $password, $remember = false) {
        $conn = $this->db->getConnection();
        
        // Get user by email
        $sql = "SELECT id, first_name, last_name, email, password, role, status, email_verified 
                FROM users WHERE email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        $user = $result->fetch_assoc();
        
        // Check password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account has been suspended'];
        }
        
        // Check if email is verified
        if (!$user['email_verified']) {
            return ['success' => false, 'message' => 'Please verify your email first'];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Set remember me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            setcookie('remember_token', $token, $expiry, '/');
            
            // Store token in database
            $this->storeRememberToken($user['id'], $token);
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        return ['success' => true, 'user' => $user];
    }
    
    public function logout() {
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $conn = $this->db->getConnection();
        $userId = $_SESSION['user_id'];
        
        $sql = "SELECT id, first_name, last_name, email, phone, role, avatar, created_at 
                FROM users WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    private function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        }
        
        return $errors;
    }
    
    private function emailExists($email) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    private function sendVerificationEmail($email, $token) {
        // Implementation for sending verification email
        // You'll need to configure your email settings
    }
    
    private function storeRememberToken($userId, $token) {
        $conn = $this->db->getConnection();
        
        $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
        
        $sql = "INSERT INTO user_tokens (user_id, token, expiry) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE token = ?, expiry = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $userId, $token, $expiry, $token, $expiry);
        $stmt->execute();
    }
    
    private function updateLastLogin($userId) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
}

// Create global auth instance
$auth = new Auth();
?>