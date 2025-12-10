<?php
// admin/index.php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

// Restrict access to admin only
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = "Admin Dashboard - Roncom";

// Get dashboard statistics
$db = new Database();
$conn = $db->getConnection();

$stats = [];
$queries = [
    'total_orders' => "SELECT COUNT(*) as count FROM orders",
    'pending_orders' => "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'",
    'total_revenue' => "SELECT SUM(total_amount) as amount FROM orders WHERE payment_status = 'completed'",
    'total_products' => "SELECT COUNT(*) as count FROM products WHERE is_active = 1",
    'total_customers' => "SELECT COUNT(*) as count FROM users WHERE role = 'customer'",
    'pending_services' => "SELECT COUNT(*) as count FROM service_bookings WHERE status = 'pending'"
];

foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $stats[$key] = $row['count'] ?? $row['amount'] ?? 0;
    }
}

// Get recent orders
$recentOrders = [];
$sql = "SELECT o.*, u.first_name, u.last_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC LIMIT 10";
$result = $conn->query($sql);
if ($result) {
    $recentOrders = $result->fetch_all(MYSQLI_ASSOC);
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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Header -->
        <header class="admin-header">
            <div class="header-left">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['user_name']; ?></p>
            </div>
            <div class="header-right">
                <div class="user-dropdown">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['user_name']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="../account.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Stats -->
        <section class="admin-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>UGX <?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <span>Total Revenue</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <span>Total Orders</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon customers">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_customers']; ?></h3>
                        <span>Customers</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <span>Products</span>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Recent Orders -->
        <section class="admin-section">
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="orders.php" class="btn btn-outline">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo $order['order_number']; ?></strong></td>
                                <td>
                                    <?php if ($order['first_name']): ?>
                                        <?php echo $order['first_name'] . ' ' . $order['last_name']; ?>
                                    <?php else: ?>
                                        Guest
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>UGX <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        
        <!-- Quick Actions -->
        <section class="admin-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="products.php?action=add" class="quick-action">
                    <i class="fas fa-plus"></i>
                    <span>Add Product</span>
                </a>
                <a href="orders.php" class="quick-action">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Manage Orders</span>
                </a>
                <a href="services.php" class="quick-action">
                    <i class="fas fa-tools"></i>
                    <span>Service Bookings</span>
                </a>
                <a href="customers.php" class="quick-action">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </div>
        </section>
    </div>

    <script src="../js/script.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>