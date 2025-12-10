<?php
// account.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "My Account - Roncom Networking Store";
$activePage = "account";

// Redirect to login if not logged in
if (!$auth->isLoggedIn()) {
    $_SESSION['redirect_to'] = 'account.php';
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user = $auth->getCurrentUser();
$userId = $user['id'];

// Get current tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$validTabs = ['dashboard', 'orders', 'addresses', 'wishlist', 'services', 'settings'];
if (!in_array($tab, $validTabs)) {
    $tab = 'dashboard';
}

// Handle form submissions
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $firstName, $lastName, $phone, $userId);
        
        if ($stmt->execute()) {
            $successMessage = 'Profile updated successfully';
            // Update session
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $user = $auth->getCurrentUser(); // Refresh user data
        } else {
            $errorMessage = 'Failed to update profile';
        }
    }
    
    if (isset($_POST['update_password'])) {
        // Update password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $userData['password'])) {
            if ($newPassword === $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $hashedPassword, $userId);
                    
                    if ($stmt->execute()) {
                        $successMessage = 'Password updated successfully';
                    } else {
                        $errorMessage = 'Failed to update password';
                    }
                } else {
                    $errorMessage = 'New password must be at least 6 characters';
                }
            } else {
                $errorMessage = 'New passwords do not match';
            }
        } else {
            $errorMessage = 'Current password is incorrect';
        }
    }
    
    if (isset($_POST['add_address'])) {
        // Add new address
        $addressType = $_POST['address_type'] ?? 'home';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $street = trim($_POST['street_address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $region = $_POST['region'] ?? 'central';
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        
        // If setting as default, remove default from other addresses
        if ($isDefault) {
            $sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
        
        $sql = "INSERT INTO user_addresses (user_id, address_type, full_name, phone, 
                street_address, city, district, region, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssssi", $userId, $addressType, $fullName, $phone, 
                         $street, $city, $district, $region, $isDefault);
        
        if ($stmt->execute()) {
            $successMessage = 'Address added successfully';
            $tab = 'addresses'; // Switch to addresses tab
        } else {
            $errorMessage = 'Failed to add address';
        }
    }
    
    if (isset($_POST['update_address'])) {
        // Update address
        $addressId = $_POST['address_id'] ?? 0;
        $addressType = $_POST['address_type'] ?? 'home';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $street = trim($_POST['street_address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $region = $_POST['region'] ?? 'central';
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        
        // If setting as default, remove default from other addresses
        if ($isDefault) {
            $sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $addressId);
            $stmt->execute();
        }
        
        $sql = "UPDATE user_addresses SET address_type = ?, full_name = ?, phone = ?, 
                street_address = ?, city = ?, district = ?, region = ?, is_default = ? 
                WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssiii", $addressType, $fullName, $phone, $street, 
                         $city, $district, $region, $isDefault, $addressId, $userId);
        
        if ($stmt->execute()) {
            $successMessage = 'Address updated successfully';
            $tab = 'addresses';
        } else {
            $errorMessage = 'Failed to update address';
        }
    }
}

// Load data based on tab
switch ($tab) {
    case 'dashboard':
        // Get dashboard stats
        $stats = [];
        
        // Total orders
        $sql = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_orders'] = $result->fetch_assoc()['total_orders'];
        
        // Pending orders
        $sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['pending_orders'] = $result->fetch_assoc()['pending_orders'];
        
        // Recent orders
        $recentOrders = [];
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $recentOrders = $result->fetch_all(MYSQLI_ASSOC);
        
        // Wishlist count
        $stats['wishlist_count'] = count($_SESSION['wishlist']);
        
        break;
        
    case 'orders':
        // Get all orders
        $orders = [];
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        
        break;
        
    case 'addresses':
        // Get user addresses
        $addresses = [];
        $sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
        
        break;
        
    case 'services':
        // Get service bookings
        $bookings = [];
        $sql = "SELECT * FROM service_bookings WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        
        break;
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
        .account-page {
            padding: 60px 0;
            min-height: calc(100vh - 300px);
        }
        
        .account-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .account-layout {
                grid-template-columns: 1fr;
            }
        }
        
        .account-sidebar {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            height: fit-content;
        }
        
        .account-profile {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }
        
        .profile-info h3 {
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .profile-info p {
            color: var(--gray);
            font-size: 14px;
        }
        
        .account-menu {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: var(--gray);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .menu-item:hover {
            background: var(--light);
            color: var(--primary);
        }
        
        .menu-item.active {
            background: var(--primary);
            color: white;
        }
        
        .menu-item i {
            width: 20px;
            text-align: center;
        }
        
        .menu-badge {
            margin-left: auto;
            background: var(--secondary);
            color: white;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 10px;
        }
        
        .account-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 40px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: var(--light);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .stat-card span {
            color: var(--gray);
            font-size: 14px;
        }
        
        .recent-orders {
            margin-top: 40px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .orders-table th {
            background: var(--light);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--gray-light);
        }
        
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .orders-table tr:hover {
            background: var(--light);
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .addresses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .address-card {
            border: 1px solid var(--gray-light);
            border-radius: 10px;
            padding: 25px;
            position: relative;
        }
        
        .address-card.default {
            border-color: var(--primary);
            background: #f8faff;
        }
        
        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .address-type {
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .address-default-badge {
            background: var(--success);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .address-details p {
            margin-bottom: 10px;
            color: var(--gray);
        }
        
        .address-details strong {
            color: var(--dark);
        }
        
        .address-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-action {
            padding: 8px 15px;
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }
        
        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-action.delete {
            border-color: var(--danger);
            color: var(--danger);
        }
        
        .btn-action.delete:hover {
            background: var(--danger);
            color: white;
        }
        
        .add-address-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 40px 20px;
            border: 2px dashed var(--gray-light);
            border-radius: 10px;
            background: transparent;
            color: var(--gray);
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .add-address-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .settings-form {
            max-width: 600px;
        }
        
        .settings-section {
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .settings-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .settings-form .form-group {
            margin-bottom: 20px;
        }
        
        .settings-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .settings-form input,
        .settings-form select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            font-size: 16px;
        }
        
        .settings-form input:focus,
        .settings-form select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(10, 77, 162, 0.1);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--gray-light);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--gray);
            margin-bottom: 25px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
        }
        
        .modal-body {
            padding: 30px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Account Section -->
    <section class="section account-page">
        <div class="container">
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="account-profile">
                        <div class="profile-avatar">
                            <?php 
                            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                            echo $initials;
                            ?>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                            <p><?php echo $user['email']; ?></p>
                        </div>
                    </div>
                    
                    <nav class="account-menu">
                        <a href="account.php?tab=dashboard" class="menu-item <?php echo $tab == 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="account.php?tab=orders" class="menu-item <?php echo $tab == 'orders' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                            <?php if (isset($stats['total_orders']) && $stats['total_orders'] > 0): ?>
                                <span class="menu-badge"><?php echo $stats['total_orders']; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="account.php?tab=addresses" class="menu-item <?php echo $tab == 'addresses' ? 'active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Addresses</span>
                        </a>
                        <a href="wishlist.php" class="menu-item">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                            <span class="menu-badge"><?php echo count($_SESSION['wishlist']); ?></span>
                        </a>
                        <a href="account.php?tab=services" class="menu-item <?php echo $tab == 'services' ? 'active' : ''; ?>">
                            <i class="fas fa-tools"></i>
                            <span>My Services</span>
                        </a>
                        <a href="account.php?tab=settings" class="menu-item <?php echo $tab == 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Account Settings</span>
                        </a>
                        <a href="logout.php" class="menu-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </aside>
                
                <!-- Main Content -->
                <main class="account-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-content <?php echo $tab == 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                        <div class="tab-header">
                            <h2>Dashboard</h2>
                            <p>Welcome back, <?php echo $user['first_name']; ?>!</p>
                        </div>
                        
                        <div class="dashboard-stats">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                                <span>Total Orders</span>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                                <span>Pending Orders</span>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h3><?php echo $stats['wishlist_count'] ?? 0; ?></h3>
                                <span>Wishlist Items</span>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h3>0</h3>
                                <span>Active Services</span>
                            </div>
                        </div>
                        
                        <div class="recent-orders">
                            <h3>Recent Orders</h3>
                            <?php if (!empty($recentOrders)): ?>
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo $order['order_number']; ?></strong></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td>UGX <?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-action">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <h3>No Orders Yet</h3>
                                    <p>You haven't placed any orders yet.</p>
                                    <a href="products.php" class="btn btn-primary                                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Orders Tab -->
                    <div class="tab-content <?php echo $tab == 'orders' ? 'active' : ''; ?>" id="orders">
                        <div class="tab-header">
                            <h2>My Orders</h2>
                            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                        
                        <?php if (!empty($orders)): ?>
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): 
                                        // Get order items count
                                        $sql = "SELECT COUNT(*) as item_count FROM order_items WHERE order_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $order['id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $itemCount = $result->fetch_assoc()['item_count'];
                                    ?>
                                        <tr>
                                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $itemCount; ?> item(s)</td>
                                            <td>UGX <?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="order-status status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-action">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h3>No Orders Yet</h3>
                                <p>You haven't placed any orders yet.</p>
                                <a href="products.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Addresses Tab -->
                    <div class="tab-content <?php echo $tab == 'addresses' ? 'active' : ''; ?>" id="addresses">
                        <div class="tab-header">
                            <h2>My Addresses</h2>
                            <button type="button" class="btn btn-primary" onclick="openAddressModal()">
                                <i class="fas fa-plus"></i> Add New Address
                            </button>
                        </div>
                        
                        <div class="addresses-grid">
                            <?php foreach ($addresses as $address): ?>
                                <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                    <div class="address-header">
                                        <span class="address-type">
                                            <?php echo ucfirst($address['address_type']); ?>
                                        </span>
                                        <?php if ($address['is_default']): ?>
                                            <span class="address-default-badge">Default</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="address-details">
                                        <p><strong><?php echo $address['full_name']; ?></strong></p>
                                        <p><?php echo $address['phone']; ?></p>
                                        <p><?php echo $address['street_address']; ?></p>
                                        <p><?php echo $address['city']; ?></p>
                                        <p><?php echo $address['district']; ?>, <?php echo ucfirst($address['region']); ?> Region</p>
                                    </div>
                                    
                                    <div class="address-actions">
                                        <button type="button" class="btn-action edit-address" 
                                                data-id="<?php echo $address['id']; ?>"
                                                data-type="<?php echo $address['address_type']; ?>"
                                                data-name="<?php echo $address['full_name']; ?>"
                                                data-phone="<?php echo $address['phone']; ?>"
                                                data-street="<?php echo $address['street_address']; ?>"
                                                data-city="<?php echo $address['city']; ?>"
                                                data-district="<?php echo $address['district']; ?>"
                                                data-region="<?php echo $address['region']; ?>"
                                                data-default="<?php echo $address['is_default']; ?>">
                                            Edit
                                        </button>
                                        <button type="button" class="btn-action delete delete-address" 
                                                data-id="<?php echo $address['id']; ?>">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="button" class="add-address-btn" onclick="openAddressModal()">
                                <i class="fas fa-plus"></i>
                                <span>Add New Address</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Services Tab -->
                    <div class="tab-content <?php echo $tab == 'services' ? 'active' : ''; ?>" id="services">
                        <div class="tab-header">
                            <h2>My Services</h2>
                            <a href="services.php" class="btn btn-primary">Book New Service</a>
                        </div>
                        
                        <?php if (!empty($bookings)): ?>
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><strong>#<?php echo $booking['booking_code']; ?></strong></td>
                                            <td><?php echo $booking['service_type']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['service_date'])); ?></td>
                                            <td><?php echo $booking['service_time']; ?></td>
                                            <td>
                                                <span class="order-status status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="service-details.php?id=<?php echo $booking['id']; ?>" class="btn-action">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h3>No Service Bookings</h3>
                                <p>You haven't booked any services yet.</p>
                                <a href="services.php" class="btn btn-primary">Book a Service</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div class="tab-content <?php echo $tab == 'settings' ? 'active' : ''; ?>" id="settings">
                        <div class="tab-header">
                            <h2>Account Settings</h2>
                        </div>
                        
                        <form method="POST" class="settings-form">
                            <!-- Profile Section -->
                            <div class="settings-section">
                                <h3>Personal Information</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" 
                                               value="<?php echo $user['first_name']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" 
                                               value="<?php echo $user['last_name']; ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" value="<?php echo $user['email']; ?>" disabled>
                                    <small style="color: var(--gray); margin-top: 5px; display: block;">
                                        Email cannot be changed. Contact support for email updates.
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo $user['phone'] ?? ''; ?>">
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </div>
                            
                            <!-- Password Section -->
                            <div class="settings-section">
                                <h3>Change Password</h3>
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password">
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password">
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </section>

    <!-- Address Modal -->
    <div class="modal" id="addressModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Address</h3>
                <button type="button" class="close-modal" onclick="closeAddressModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="addressForm">
                    <input type="hidden" id="address_id" name="address_id" value="">
                    
                    <div class="form-group">
                        <label for="address_type">Address Type</label>
                        <select id="address_type" name="address_type" required>
                            <option value="home">Home</option>
                            <option value="work">Work</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="street_address">Street Address</label>
                        <input type="text" id="street_address" name="street_address" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="city">City/Town</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="district">District</label>
                            <input type="text" id="district" name="district" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" required>
                            <option value="central">Central</option>
                            <option value="eastern">Eastern</option>
                            <option value="northern">Northern</option>
                            <option value="western">Western</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="is_default" name="is_default" value="1">
                            <span>Set as default address</span>
                        </label>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 30px;">
                        <button type="submit" name="add_address" id="submitBtn" class="btn btn-primary">
                            Save Address
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeAddressModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Address Modal Functions
        function openAddressModal(addressId = null) {
            const modal = document.getElementById('addressModal');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('addressForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (addressId) {
                // Editing existing address
                modalTitle.textContent = 'Edit Address';
                submitBtn.name = 'update_address';
                submitBtn.innerHTML = 'Update Address';
            } else {
                // Adding new address
                modalTitle.textContent = 'Add New Address';
                submitBtn.name = 'add_address';
                submitBtn.innerHTML = 'Save Address';
                form.reset();
                document.getElementById('address_id').value = '';
            }
            
            modal.classList.add('active');
        }
        
        function closeAddressModal() {
            document.getElementById('addressModal').classList.remove('active');
        }
        
        // Edit Address
        document.querySelectorAll('.edit-address').forEach(button => {
            button.addEventListener('click', function() {
                const addressId = this.getAttribute('data-id');
                const addressType = this.getAttribute('data-type');
                const fullName = this.getAttribute('data-name');
                const phone = this.getAttribute('data-phone');
                const street = this.getAttribute('data-street');
                const city = this.getAttribute('data-city');
                const district = this.getAttribute('data-district');
                const region = this.getAttribute('data-region');
                const isDefault = this.getAttribute('data-default');
                
                document.getElementById('address_id').value = addressId;
                document.getElementById('address_type').value = addressType;
                document.getElementById('full_name').value = fullName;
                document.getElementById('phone').value = phone;
                document.getElementById('street_address').value = street;
                document.getElementById('city').value = city;
                document.getElementById('district').value = district;
                document.getElementById('region').value = region;
                document.getElementById('is_default').checked = isDefault === '1';
                
                openAddressModal(addressId);
            });
        });
        
        // Delete Address
        document.querySelectorAll('.delete-address').forEach(button => {
            button.addEventListener('click', function() {
                const addressId = this.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this address?')) {
                    window.location.href = `delete-address.php?id=${addressId}`;
                }
            });
        });
        
        // Close modal when clicking outside
        document.getElementById('addressModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddressModal();
            }
        });
        
        // Form validation
        document.getElementById('addressForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            if (phone && !/^\+?[\d\s\-\(\)]+$/.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return false;
            }
        });
    </script>
</body>
</html>