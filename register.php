<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "Register - Roncom Networking Store";
$activePage = "account";

// If user is already logged in, redirect to account
if ($auth->isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$errors = [];
$success = false;
$formData = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'agree_terms' => isset($_POST['agree_terms']) ? true : false
    ];
    
    // Validate inputs
    if (empty($formData['first_name'])) {
        $errors['first_name'] = 'First name is required';
    }
    
    if (empty($formData['last_name'])) {
        $errors['last_name'] = 'Last name is required';
    }
    
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($formData['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^(\+256|0)[0-9]{9}$/', $formData['phone'])) {
        $errors['phone'] = 'Invalid Uganda phone number';
    }
    
    if (empty($formData['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($formData['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($formData['password'] !== $formData['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (!$formData['agree_terms']) {
        $errors['agree_terms'] = 'You must agree to the terms and conditions';
    }
    
    // Check if email already exists
    if (empty($errors['email'])) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors['email'] = 'Email already registered';
        }
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $result = $auth->register($formData);
        
        if ($result['success']) {
            $success = true;
            $formData = []; // Clear form data
        } else {
            $errors = array_merge($errors, $result['errors']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        .auth-page {
            min-height: calc(100vh - 300px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
        }
        
        .auth-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: var(--gray);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        
        .auth-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .auth-form input, .auth-form select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .auth-form input:focus, .auth-form select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 77, 162, 0.1);
            outline: none;
        }
        
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .form-check {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .form-check input {
            margin-right: 10px;
            margin-top: 5px;
        }
        
        .form-check label {
            font-size: 14px;
            color: var(--gray);
        }
        
        .form-check a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .auth-button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 20px;
        }
        
        .auth-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            color: var(--gray);
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .success-message {
            text-align: center;
            padding: 30px;
        }
        
        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .success-message h3 {
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .success-message p {
            color: var(--gray);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Registration Section -->
    <section class="section auth-page">
        <div class="container">
            <div class="auth-container">
                <?php if ($success): ?>
                    <div class="success-message">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Registration Successful!</h3>
                        <p>Your account has been created. Please check your email to verify your account.</p>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Go to Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="auth-header">
                        <h1>Create Account</h1>
                        <p>Join Roncom for the best networking equipment and services</p>
                    </div>
                    
                    <form method="POST" action="register.php" class="auth-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required
                                       value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>">
                                <?php if (isset($errors['first_name'])): ?>
                                    <span class="error"><?php echo $errors['first_name']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required
                                       value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>">
                                <?php if (isset($errors['last_name'])): ?>
                                    <span class="error"><?php echo $errors['last_name']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <span class="error"><?php echo $errors['email']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required
                                   placeholder="+256 XXX XXX XXX or 07XX XXX XXX"
                                   value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                            <?php if (isset($errors['phone'])): ?>
                                <span class="error"><?php echo $errors['phone']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required>
                                <?php if (isset($errors['password'])): ?>
                                    <span class="error"><?php echo $errors['password']; ?></span>
                                <?php endif; ?>
                                <small style="display: block; margin-top: 5px; color: var(--gray);">
                                    Must be at least 6 characters
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <span class="error"><?php echo $errors['confirm_password']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="agree_terms" name="agree_terms" 
                                   <?php echo isset($formData['agree_terms']) && $formData['agree_terms'] ? 'checked' : ''; ?>>
                            <label for="agree_terms">
                                I agree to the <a href="terms.php">Terms & Conditions</a> and 
                                <a href="privacy.php">Privacy Policy</a> *
                            </label>
                        </div>
                        <?php if (isset($errors['agree_terms'])): ?>
                            <span class="error"><?php echo $errors['agree_terms']; ?></span>
                        <?php endif; ?>
                        
                        <button type="submit" class="auth-button">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Sign in here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    // Format phone number
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.startsWith('256')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+256' + value.substring(1);
        }
        
        e.target.value = value;
    });
    </script>
</body>
</html>