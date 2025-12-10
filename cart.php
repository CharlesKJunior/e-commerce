<?php
// cart.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "Shopping Cart - Roncom Networking Store";
$activePage = "cart";

$db = new Database();
$conn = $db->getConnection();

// Handle cart actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

switch ($action) {
    case 'add':
        if ($productId > 0 && $quantity > 0) {
            addToCart($productId, $quantity);
        }
        header('Location: cart.php');
        exit;
        
    case 'update':
        if (isset($_POST['cart'])) {
            updateCart($_POST['cart']);
        }
        header('Location: cart.php');
        exit;
        
    case 'remove':
        $itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        if ($itemId > 0) {
            removeFromCart($itemId);
        }
        header('Location: cart.php');
        exit;
        
    case 'clear':
        clearCart();
        header('Location: cart.php');
        exit;
}

// Get cart items with product details
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
        
        // Organize products by ID for easy access
        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product['id']] = $product;
        }
        
        // Build cart items array
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

// Functions
function addToCart($productId, $quantity = 1, $includeInstallation = false) {
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = [
            'quantity' => $quantity,
            'installation' => $includeInstallation
        ];
    } else {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
        if ($includeInstallation) {
            $_SESSION['cart'][$productId]['installation'] = true;
        }
    }
}

function updateCart($cartData) {
    foreach ($cartData as $productId => $item) {
        if (isset($_SESSION['cart'][$productId])) {
            $quantity = intval($item['quantity']);
            $installation = isset($item['installation']) ? true : false;
            
            if ($quantity > 0) {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
                $_SESSION['cart'][$productId]['installation'] = $installation;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }
    }
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
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
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="section cart-page">
        <div class="container">
            <?php if (!empty($cartItems)): ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items-section">
                        <div class="cart-header">
                            <h2>Your Items (<?php echo count($cartItems); ?>)</h2>
                            <a href="products.php" class="continue-shopping">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                        
                        <form method="POST" action="cart.php?action=update">
                            <div class="cart-items-list">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="cart-item">
                                        <div class="cart-item-image">
                                            <?php if ($item['product']['image_url']): ?>
                                                <img src="<?php echo $item['product']['image_url']; ?>" alt="<?php echo $item['product']['name']; ?>">
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <i class="fas fa-network-wired"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cart-item-details">
                                            <h3><?php echo $item['product']['name']; ?></h3>
                                            <p class="item-description"><?php echo $item['product']['short_description']; ?></p>
                                            <div class="item-options">
                                                <label class="checkbox">
                                                    <input type="checkbox" name="cart[<?php echo $item['id']; ?>][installation]" 
                                                           value="1" <?php echo $item['installation'] ? 'checked' : ''; ?>>
                                                    <span>Include Professional Installation (+UGX 50,000)</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="cart-item-price">
                                            <span class="price"><?php echo CURRENCY; ?> <?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                        <div class="cart-item-quantity">
                                            <button type="button" class="qty-btn minus" data-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="cart[<?php echo $item['id']; ?>][quantity]" 
                                                   value="<?php echo $item['quantity']; ?>" min="1" class="qty-input">
                                            <button type="button" class="qty-btn plus" data-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="cart-item-total">
                                            <span><?php echo CURRENCY; ?> <?php echo number_format($item['total'], 2); ?></span>
                                        </div>
                                        <a href="cart.php?action=remove&item_id=<?php echo $item['id']; ?>" class="remove-item">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="cart-actions">
                                <button type="submit" class="btn btn-outline">
                                    <i class="fas fa-sync-alt"></i> Update Cart
                                </button>
                                <a href="cart.php?action=clear" class="btn btn-outline" onclick="return confirm('Clear your entire cart?')">
                                    <i class="fas fa-trash"></i> Clear Cart
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span><?php echo CURRENCY; ?> <?php echo number_format($cartSubtotal, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Installation Service</span>
                                <span><?php echo CURRENCY; ?> <?php echo number_format($installationTotal, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span class="free">FREE</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (<?php echo (TAX_RATE * 100); ?>%)</span>
                                <span><?php echo CURRENCY; ?> <?php echo number_format($taxAmount, 2); ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span><?php echo CURRENCY; ?> <?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="checkout-actions">
                            <a href="products.php" class="btn btn-outline">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                            <a href="checkout.php" class="btn btn-primary">
                                <i class="fas fa-lock"></i> Proceed to Checkout
                            </a>
                        </div>
                        
                        <div class="secure-checkout">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure checkout • SSL encrypted</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart fa-3x"></i>
                        <h3>Your cart is empty</h3>
                        <p>Add some networking equipment to get started!</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    // Quantity buttons
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            let value = parseInt(input.value);
            
            if (this.classList.contains('minus')) {
                if (value > 1) {
                    input.value = value - 1;
                }
            } else if (this.classList.contains('plus')) {
                input.value = value + 1;
            }
        });
    });
    </script>
</body>
</html>