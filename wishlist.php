<?php
// wishlist.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "My Wishlist - Roncom Networking Store";
$activePage = "wishlist";

$db = new Database();
$conn = $db->getConnection();

// Handle wishlist actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

switch ($action) {
    case 'add':
        if ($productId > 0) {
            addToWishlist($productId);
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'wishlist.php'));
        exit;
        
    case 'remove':
        $itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        if ($itemId > 0) {
            removeFromWishlist($itemId);
        }
        header('Location: wishlist.php');
        exit;
        
    case 'clear':
        clearWishlist();
        header('Location: wishlist.php');
        exit;
        
    case 'move-to-cart':
        $itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        if ($itemId > 0) {
            moveToCart($itemId);
        }
        header('Location: wishlist.php');
        exit;
}

// Get wishlist items with product details
$wishlistItems = [];
if (!empty($_SESSION['wishlist'])) {
    $ids = array_keys($_SESSION['wishlist']);
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id IN ($placeholders) AND p.is_active = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product['id']] = $product;
        }
        
        foreach ($_SESSION['wishlist'] as $id => $item) {
            if (isset($productMap[$id])) {
                $wishlistItems[] = [
                    'id' => $id,
                    'product' => $productMap[$id],
                    'added_at' => $item['added_at']
                ];
            }
        }
    }
}

// Functions
function addToWishlist($productId) {
    if (!isset($_SESSION['wishlist'][$productId])) {
        $_SESSION['wishlist'][$productId] = [
            'added_at' => date('Y-m-d H:i:s')
        ];
    }
}

function removeFromWishlist($productId) {
    if (isset($_SESSION['wishlist'][$productId])) {
        unset($_SESSION['wishlist'][$productId]);
    }
}

function clearWishlist() {
    $_SESSION['wishlist'] = [];
}

function moveToCart($productId) {
    if (isset($_SESSION['wishlist'][$productId])) {
        // Add to cart
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = [
                'quantity' => 1,
                'installation' => false
            ];
        } else {
            $_SESSION['cart'][$productId]['quantity'] += 1;
        }
        
        // Remove from wishlist
        unset($_SESSION['wishlist'][$productId]);
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
        .wishlist-page {
            min-height: calc(100vh - 300px);
            padding: 60px 0;
        }
        
        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .wishlist-actions {
            display: flex;
            gap: 15px;
        }
        
        .wishlist-items {
            display: grid;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .wishlist-item {
            display: grid;
            grid-template-columns: 150px 2fr 1fr 1fr;
            gap: 25px;
            align-items: center;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            border: 1px solid var(--gray-light);
        }
        
        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 992px) {
            .wishlist-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
        
        .item-image {
            position: relative;
        }
        
        .item-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-image .image-placeholder {
            width: 150px;
            height: 150px;
            background: var(--gray-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .item-image .image-placeholder i {
            font-size: 3rem;
            color: var(--primary);
        }
        
        .remove-wishlist {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 30px;
            height: 30px;
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--danger);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .remove-wishlist:hover {
            background: var(--danger);
            color: white;
        }
        
        .item-details h3 {
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .item-description {
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .item-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .item-category {
            background: var(--light);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--primary);
        }
        
        .item-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--secondary);
        }
        
        .item-stock {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .item-stock.in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .item-stock.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .item-price {
            text-align: center;
        }
        
        .price {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .old-price {
            display: block;
            color: var(--gray);
            text-decoration: line-through;
            font-size: 1rem;
        }
        
        .discount {
            display: inline-block;
            background: var(--secondary);
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .empty-wishlist {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--gray-light);
            margin-bottom: 20px;
        }
        
        .empty-wishlist h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .empty-wishlist p {
            color: var(--gray);
            margin-bottom: 25px;
        }
        
        .share-wishlist {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .share-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .share-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .share-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .share-btn.whatsapp:hover {
            background: #25D366;
            border-color: #25D366;
            color: white;
        }
        
        .share-btn.facebook:hover {
            background: #1877F2;
            border-color: #1877F2;
            color: white;
        }
        
        .share-btn.email:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>My Wishlist</h1>
            <p>Save your favorite products for later</p>
        </div>
    </section>

    <!-- Wishlist Section -->
    <section class="section wishlist-page">
        <div class="container">
            <?php if (!empty($wishlistItems)): ?>
                <div class="wishlist-header">
                    <h2>Saved Items (<?php echo count($wishlistItems); ?>)</h2>
                    <div class="wishlist-actions">
                        <a href="wishlist.php?action=clear" class="btn btn-outline" 
                           onclick="return confirm('Clear your entire wishlist?')">
                            <i class="fas fa-trash"></i> Clear All
                        </a>
                    </div>
                </div>
                
                <div class="wishlist-items">
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="wishlist-item">
                            <div class="item-image">
                                <?php if ($item['product']['image_url']): ?>
                                    <img src="<?php echo $item['product']['image_url']; ?>" 
                                         alt="<?php echo $item['product']['name']; ?>">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="fas fa-network-wired"></i>
                                    </div>
                                <?php endif; ?>
                                <a href="wishlist.php?action=remove&item_id=<?php echo $item['id']; ?>" 
                                   class="remove-wishlist" title="Remove from wishlist">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            
                            <div class="item-details">
                                <h3><?php echo $item['product']['name']; ?></h3>
                                <p class="item-description"><?php echo $item['product']['short_description']; ?></p>
                                
                                <div class="item-meta">
                                    <span class="item-category"><?php echo $item['product']['category_name']; ?></span>
                                    <div class="item-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= floor($item['product']['rating'])): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif ($i == ceil($item['product']['rating']) && $item['product']['rating'] % 1 != 0): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span>(<?php echo $item['product']['review_count']; ?>)</span>
                                    </div>
                                </div>
                                
                                <div class="item-stock <?php echo $item['product']['quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <i class="fas fa-<?php echo $item['product']['quantity'] > 0 ? 'check-circle' : 'clock'; ?>"></i>
                                    <span><?php echo $item['product']['quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?></span>
                                </div>
                            </div>
                            
                            <div class="item-price">
                                <span class="price"><?php echo CURRENCY; ?> <?php echo number_format($item['product']['price'], 2); ?></span>
                                <?php if ($item['product']['compare_price']): ?>
                                    <span class="old-price"><?php echo CURRENCY; ?> <?php echo number_format($item['product']['compare_price'], 2); ?></span>
                                    <span class="discount">
                                        Save <?php echo round((1 - $item['product']['price'] / $item['product']['compare_price']) * 100); ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-actions">
                                <?php if ($item['product']['quantity'] > 0): ?>
                                    <form method="POST" action="cart.php?action=add">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                    
                                    <a href="wishlist.php?action=move-to-cart&item_id=<?php echo $item['id']; ?>" 
                                       class="btn btn-outline">
                                        <i class="fas fa-shopping-cart"></i> Move to Cart
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-outline notify-btn" data-product-id="<?php echo $item['id']; ?>">
                                        <i class="fas fa-bell"></i> Notify When Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Share Wishlist -->
                <?php if ($auth->isLoggedIn()): ?>
                    <div class="share-wishlist">
                        <h3>Share Your Wishlist</h3>
                        <p>Share your wishlist with friends and family</p>
                        <div class="share-options">
                            <button class="share-btn whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                            <button class="share-btn facebook">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                            <button class="share-btn email">
                                <i class="fas fa-envelope"></i> Email
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-wishlist">
                    <div class="empty-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Your Wishlist is Empty</h3>
                    <p>Save your favorite networking equipment here to purchase later.</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Recently Viewed -->
            <?php if (!empty($wishlistItems)): ?>
                <div class="section" style="margin-top: 60px;">
                    <h2 class="text-center">You May Also Like</h2>
                    <div class="products-grid" style="margin-top: 30px;">
                        <!-- This would show related products based on wishlist items -->
                        <!-- In a real app, you would query for related products -->
                        <div class="product-card">
                            <div class="product-badge">NEW</div>
                            <div class="product-image">
                                <div class="image-placeholder">
                                    <i class="fas fa-ethernet"></i>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3>Cat6 Ethernet Cable 100m</h3>
                                <p class="product-description">High-speed network cable with connectors</p>
                                <div class="product-price">
                                    <span class="price">UGX 120,000</span>
                                    <span class="old-price">UGX 150,000</span>
                                </div>
                            </div>
                            <form method="POST" action="wishlist.php?action=add">
                                <input type="hidden" name="product_id" value="999">
                                <button type="submit" class="btn btn-outline btn-block">
                                    <i class="far fa-heart"></i> Add to Wishlist
                                </button>
                            </form>
                        </div>
                        
                        <!-- Add more related products -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    // Share buttons
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.classList[1]; // whatsapp, facebook, email
            const wishlistItems = <?php echo json_encode(array_column($wishlistItems, 'product')); ?>;
            const siteUrl = '<?php echo SITE_URL; ?>';
            let shareUrl = '';
            let message = 'Check out my Roncom wishlist:\n\n';
            
            // Build message with product names
            wishlistItems.slice(0, 5).forEach((item, index) => {
                message += `${index + 1}. ${item.name} - UGX ${item.price.toLocaleString()}\n`;
            });
            
            message += `\nView on: ${siteUrl}/wishlist.php`;
            
            switch(platform) {
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(siteUrl + '/wishlist.php')}`;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=My Roncom Wishlist&body=${encodeURIComponent(message)}`;
                    break;
            }
            
            window.open(shareUrl, '_blank');
        });
    });
    
    // Notify when available
    document.querySelectorAll('.notify-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const email = prompt('Enter your email to get notified when this product is back in stock:');
            
            if (email && validateEmail(email)) {
                // In a real app, you would send this to the server
                fetch('api/notify.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('You will be notified when this product is back in stock!');
                    } else {
                        alert('Failed to set notification. Please try again.');
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    </script>
</body>
</html>