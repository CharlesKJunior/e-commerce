<?php
// checkout.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/payment.php';

$pageTitle = "Checkout - Roncom Networking Store";
$activePage = "checkout";

$db = new Database();
$conn = $db->getConnection();
$payment = new Payment();

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get cart items (similar to cart.php)
$cartItems = [];
$cartTotal = 0;
$cartSubtotal = 0;
$taxAmount = 0;
$installationTotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT p.* FROM products p WHERE p.id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product['id']] = $product;
        }
        
        foreach ($_SESSION['cart'] as $id => $item) {
            if (isset($productMap[$id])) {
                $product = $productMap[$id];
                $itemTotal = $product['price'] * $item['quantity'];
                $installationCost = isset($item['installation']) && $item['installation'] ? 50000 : 0;
                
                $cartItems[] = [
                    'id' => $id,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'installation' => isset($item['installation']) ? $item['installation'] : false,
                    'price' => $product['price'],
                    'item_total' => $itemTotal,
                    'installation_cost' => $installationCost,
                    'total' => $itemTotal + $installationCost
                ];
                
                $cartSubtotal += $itemTotal;
                $installationTotal += $installationCost;
            }
        }
        
        $taxAmount = $cartSubtotal * TAX_RATE;
        $cartTotal = $cartSubtotal + $installationTotal + $taxAmount;
    }
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate form data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $shippingMethod = $_POST['shipping_method'] ?? 'standard';
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $instructions = trim($_POST['instructions'] ?? '');
    
    // Payment method specific fields
    $mobileProvider = $_POST['mobile_provider'] ?? '';
    $mobileNumber = $_POST['mobile_number'] ?? '';
    $cardNumber = $_POST['card_number'] ?? '';
    
    // Validate required fields
    if (empty($firstName)) $errors['first_name'] = 'First name is required';
    if (empty($lastName)) $errors['last_name'] = 'Last name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($phone)) $errors['phone'] = 'Phone number is required';
    if (empty($address)) $errors['address'] = 'Address is required';
    if (empty($city)) $errors['city'] = 'City is required';
    if (empty($district)) $errors['district'] = 'District is required';
    if (empty($region)) $errors['region'] = 'Region is required';
    
    // Validate payment method specific fields
    if ($paymentMethod == 'mtn_mobile' || $paymentMethod == 'airtel_money') {
        if (empty($mobileNumber)) $errors['mobile_number'] = 'Mobile number is required';
    }
    
    if (empty($errors)) {
        // Generate order number
        $orderNumber = 'RON-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $userId = $auth->isLoggedIn() ? $_SESSION['user_id'] : null;
            
            $sql = "INSERT INTO orders (
                order_number, user_id, guest_email, guest_phone, subtotal, tax_amount, 
                shipping_amount, service_amount, total_amount, payment_method, 
                shipping_method, status, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'];
            $status = 'pending';
            $shippingAmount = $shippingMethod == 'express' ? 50000 : ($shippingMethod == 'same_day' ? 100000 : 0);
            
            $stmt->bind_param(
                "sissdddddsssss",
                $orderNumber,
                $userId,
                $email,
                $phone,
                $cartSubtotal,
                $taxAmount,
                $shippingAmount,
                $installationTotal,
                $cartTotal + $shippingAmount,
                $paymentMethod,
                $shippingMethod,
                $status,
                $ip,
                $agent
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order: " . $stmt->error);
            }
            
            $orderId = $conn->insert_id;
            
            // Save order items
            foreach ($cartItems as $item) {
                $sql = "INSERT INTO order_items (
                    order_id, product_id, item_type, name, description, price, 
                    quantity, total, variation_details, include_installation, installation_cost
                ) VALUES (?, ?, 'product', ?, ?, ?, ?, ?, NULL, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $itemName = $item['product']['name'];
                $itemDesc = $item['product']['short_description'];
                $includeInstallation = $item['installation'] ? 1 : 0;
                
                $stmt->bind_param(
                    "iissddidd",
                    $orderId,
                    $item['id'],
                    $itemName,
                    $itemDesc,
                    $item['price'],
                    $item['quantity'],
                    $item['item_total'],
                    $includeInstallation,
                    $item['installation_cost']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to save order item: " . $stmt->error);
                }
            }
            
            // Save shipping address
            $sql = "INSERT INTO order_addresses (
                order_id, address_type, full_name, email, phone, street_address, 
                city, district, region, instructions
            ) VALUES (?, 'shipping', ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $fullName = "$firstName $lastName";
            $stmt->bind_param(
                "issssssss",
                $orderId,
                $fullName,
                $email,
                $phone,
                $address,
                $city,
                $district,
                $region,
                $instructions
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save address: " . $stmt->error);
            }
            
            // Process payment based on method
            if ($paymentMethod == 'mtn_mobile' || $paymentMethod == 'airtel_money') {
                // Process mobile money payment
                $paymentResult = $payment->processMobileMoney(
                    $orderId,
                    $paymentMethod == 'mtn_mobile' ? 'mtn' : 'airtel',
                    $mobileNumber,
                    $cartTotal + $shippingAmount,
                    $orderNumber
                );
                
                if (!$paymentResult['success']) {
                    throw new Exception("Payment failed: " . $paymentResult['message']);
                }
                
                // Update order with payment reference
                $sql = "UPDATE orders SET payment_reference = ?, payment_status = 'processing' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $paymentResult['reference'], $orderId);
                $stmt->execute();
                
            } elseif ($paymentMethod == 'cash') {
                // Cash on delivery - no immediate payment processing
                $sql = "UPDATE orders SET payment_status = 'pending' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order confirmation
            header("Location: order-confirmation.php?order_id=$orderId");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors['general'] = "Checkout failed: " . $e->getMessage();
        }
    }
}

// Get user data if logged in
$userData = null;
if ($auth->isLoggedIn()) {
    $userData = $auth->getCurrentUser();
    
    // Get user addresses
    $addresses = [];
    $sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $addresses = $result->fetch_all(MYSQLI_ASSOC);
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
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Checkout</h1>
            <p>Complete your purchase in just a few steps</p>
        </div>
    </section>

    <!-- Checkout Steps -->
    <section class="checkout-steps">
        <div class="container">
            <div class="steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-info">
                        <span class="step-title">Cart Review</span>
                        <span class="step-subtitle">Review your items</span>
                    </div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-info">
                        <span class="step-title">Delivery</span>
                        <span class="step-subtitle">Shipping & Address</span>
                    </div>
                </div>
                <div class="step active">
                    <div class="step-number">3</div>
                    <div class="step-info">
                        <span class="step-title">Payment</span>
                        <span class="step-subtitle">Payment method</span>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-info">
                        <span class="step-title">Confirmation</span>
                        <span class="step-subtitle">Order complete</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Content -->
    <section class="section checkout-page">
        <div class="container">
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="checkout.php" id="checkoutForm">
                <div class="checkout-layout">
                    <!-- Delivery Information -->
                    <div class="checkout-form-section">
                        <div class="checkout-section">
                            <h2><i class="fas fa-shipping-fast"></i> Delivery Information</h2>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="firstName">First Name *</label>
                                    <input type="text" id="firstName" name="first_name" 
                                           value="<?php echo $userData['first_name'] ?? ''; ?>" required>
                                    <?php if (isset($errors['first_name'])): ?>
                                        <small class="error"><?php echo $errors['first_name']; ?></small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName">Last Name *</label>
                                    <input type="text" id="lastName" name="last_name" 
                                           value="<?php echo $userData['last_name'] ?? ''; ?>" required>
                                    <?php if (isset($errors['last_name'])): ?>
                                        <small class="error"><?php echo $errors['last_name']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo $userData['email'] ?? ''; ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <small class="error"><?php echo $errors['email']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo $userData['phone'] ?? ''; ?>" 
                                       placeholder="+256 XXX XXX XXX" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <small class="error"><?php echo $errors['phone']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Street Address *</label>
                                <input type="text" id="address" name="address" required>
                                <?php if (isset($errors['address'])): ?>
                                    <small class="error"><?php echo $errors['address']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="city">City/Town *</label>
                                    <select id="city" name="city" required>
                                        <option value="">Select City/Town</option>
                                        <option value="kampala">Kampala</option>
                                        <option value="mbarara">Mbarara</option>
                                        <option value="rukungiri">Rukungiri</option>
                                        <option value="jinja">Jinja</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <?php if (isset($errors['city'])): ?>
                                        <small class="error"><?php echo $errors['city']; ?></small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="district">District *</label>
                                    <input type="text" id="district" name="district" required>
                                    <?php if (isset($errors['district'])): ?>
                                        <small class="error"><?php echo $errors['district']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="region">Region *</label>
                                <select id="region" name="region" required>
                                    <option value="">Select Region</option>
                                    <option value="central">Central</option>
                                    <option value="western">Western</option>
                                    <option value="eastern">Eastern</option>
                                    <option value="northern">Northern</option>
                                </select>
                                <?php if (isset($errors['region'])): ?>
                                    <small class="error"><?php echo $errors['region']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="instructions">Delivery Instructions (Optional)</label>
                                <textarea id="instructions" name="instructions" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <!-- Shipping Method -->
                        <div class="checkout-section">
                            <h2><i class="fas fa-truck"></i> Shipping Method</h2>
                            
                            <div class="shipping-methods">
                                <div class="shipping-method">
                                    <label class="radio">
                                        <input type="radio" name="shipping_method" value="standard" checked>
                                        <div class="shipping-info">
                                            <span class="shipping-name">Standard Shipping</span>
                                            <span class="shipping-time">3-5 business days</span>
                                            <span class="shipping-price">FREE</span>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="shipping-method">
                                    <label class="radio">
                                        <input type="radio" name="shipping_method" value="express">
                                        <div class="shipping-info">
                                            <span class="shipping-name">Express Shipping</span>
                                            <span class="shipping-time">1-2 business days</span>
                                            <span class="shipping-price">UGX 50,000</span>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="shipping-method">
                                    <label class="radio">
                                        <input type="radio" name="shipping_method" value="same_day">
                                        <div class="shipping-info">
                                            <span class="shipping-name">Same Day Delivery</span>
                                            <span class="shipping-time">Same day (Kampala only)</span>
                                            <span class="shipping-price">UGX 100,000</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="checkout-section">
                            <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                            
                            <div class="payment-tabs">
                                <div class="tab-buttons">
                                    <button type="button" class="tab-btn active" data-tab="mobile-money">Mobile Money</button>
                                    <button type="button" class="tab-btn" data-tab="cash">Cash on Delivery</button>
                                </div>
                                
                                <div class="tab-content active" id="mobile-money">
                                    <div class="form-group">
                                        <label for="mobileProvider">Mobile Network *</label>
                                        <select id="mobileProvider" name="mobile_provider" required>
                                            <option value="">Select Network</option>
                                            <option value="mtn">MTN Mobile Money</option>
                                            <option value="airtel">Airtel Money</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mobileNumber">Mobile Number *</label>
                                        <input type="text" id="mobileNumber" name="mobile_number" 
                                               placeholder="e.g., 0772 XXX XXX">
                                        <?php if (isset($errors['mobile_number'])): ?>
                                            <small class="error"><?php echo $errors['mobile_number']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="payment-note">
                                        <i class="fas fa-info-circle"></i>
                                        <span>You'll receive a payment request on your phone.</span>
                                    </div>
                                </div>
                                
                                <div class="tab-content" id="cash">
                                    <div class="payment-note success">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div>
                                            <strong>Pay when your order arrives</strong>
                                            <span>Our delivery agent will collect payment when they deliver your order.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Terms & Conditions -->
                        <div class="checkout-section">
                            <div class="terms">
                                <label class="checkbox">
                                    <input type="checkbox" id="terms" name="terms" required>
                                    <span>I agree to the Terms & Conditions and Privacy Policy *</span>
                                </label>
                            </div>
                            
                            <div class="checkout-actions">
                                <a href="cart.php" class="btn btn-outline">
                                    <i class="fas fa-arrow-left"></i> Back to Cart
                                </a>
                                <button type="submit" class="btn btn-primary" id="placeOrder">
                                    <i class="fas fa-lock"></i> Place Order
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="checkout-summary">
                        <div class="summary-card">
                            <h2>Order Summary</h2>
                            
                            <div class="order-items">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="order-item">
                                        <div class="item-info">
                                            <span class="item-name"><?php echo $item['product']['name']; ?></span>
                                            <span class="item-qty">×<?php echo $item['quantity']; ?></span>
                                        </div>
                                        <span class="item-price"><?php echo CURRENCY; ?> <?php echo number_format($item['item_total'], 2); ?></span>
                                    </div>
                                    <?php if ($item['installation']): ?>
                                        <div class="order-item service">
                                            <div class="item-info">
                                                <span class="item-name">Installation Service</span>
                                                <span class="item-qty">For <?php echo $item['product']['name']; ?></span>
                                            </div>
                                            <span class="item-price"><?php echo CURRENCY; ?> <?php echo number_format($item['installation_cost'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="summary-totals">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span><?php echo CURRENCY; ?> <?php echo number_format($cartSubtotal, 2); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Installation</span>
                                    <span><?php echo CURRENCY; ?> <?php echo number_format($installationTotal, 2); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping</span>
                                    <span class="free shipping-cost">FREE</span>
                                </div>
                                <div class="summary-row">
                                    <span>Tax (<?php echo (TAX_RATE * 100); ?>%)</span>
                                    <span><?php echo CURRENCY; ?> <?php echo number_format($taxAmount, 2); ?></span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total</span>
                                    <span class="total-amount"><?php echo CURRENCY; ?> <?php echo number_format($cartTotal, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="summary-help">
                                <h3><i class="fas fa-headset"></i> Need Help?</h3>
                                <p>Call: <strong><?php echo SITE_PHONE; ?></strong></p>
                                <p>Email: <strong><?php echo SITE_EMAIL; ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    // Payment tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Update payment method hidden field
            document.querySelector('input[name="payment_method"]').value = 
                tabId === 'mobile-money' ? 'mtn_mobile' : 'cash';
        });
    });
    
    // Shipping method price update
    document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const shippingCost = document.querySelector('.shipping-cost');
            const totalAmount = document.querySelector('.total-amount');
            
            let shippingPrice = 0;
            if (this.value === 'express') shippingPrice = 50000;
            if (this.value === 'same_day') shippingPrice = 100000;
            
            if (shippingPrice > 0) {
                shippingCost.textContent = 'UGX ' + shippingPrice.toLocaleString();
                shippingCost.className = '';
            } else {
                shippingCost.textContent = 'FREE';
                shippingCost.className = 'free';
            }
            
            // Update total (simplified - in real app, recalculate from server)
            const baseTotal = <?php echo $cartTotal; ?>;
            const newTotal = baseTotal + shippingPrice;
            totalAmount.textContent = 'UGX ' + newTotal.toLocaleString();
        });
    });
    
    // City select updates district
    document.getElementById('city').addEventListener('change', function() {
        const districtMap = {
            'kampala': 'Kampala',
            'mbarara': 'Mbarara',
            'rukungiri': 'Rukungiri',
            'jinja': 'Jinja'
        };
        
        if (districtMap[this.value]) {
            document.getElementById('district').value = districtMap[this.value];
        } else if (this.value === 'other') {
            document.getElementById('district').value = '';
            document.getElementById('district').focus();
        }
    });
    
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        const mobileProvider = document.getElementById('mobileProvider');
        const mobileNumber = document.getElementById('mobileNumber');
        
        if (paymentMethod && paymentMethod.value.includes('mobile')) {
            if (!mobileProvider.value) {
                e.preventDefault();
                alert('Please select a mobile network');
                mobileProvider.focus();
                return;
            }
            
            if (!mobileNumber.value) {
                e.preventDefault();
                alert('Please enter your mobile number');
                mobileNumber.focus();
                return;
            }
        }
    });
    </script>
</body>
</html>