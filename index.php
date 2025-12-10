<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "Home - Roncom Networking Store";
$activePage = "home";

// Get featured products
$db = new Database();
$conn = $db->getConnection();

// Featured products
$featuredProducts = [];
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.is_featured = 1 AND p.is_active = 1 
        LIMIT 4";
$result = $conn->query($sql);
if ($result) {
    $featuredProducts = $result->fetch_all(MYSQLI_ASSOC);
}

// Categories
$categories = [];
$sql = "SELECT * FROM categories WHERE is_active = 1 LIMIT 4";
$result = $conn->query($sql);
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Statistics
$stats = [
    'products' => 500,
    'delivery' => 'Same Day',
    'installation' => 'Included',
    'coverage' => 'Nationwide'
];
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
    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <div class="header-top">
                <div class="header-contact">
                    <span><i class="fas fa-phone"></i> <?php echo SITE_PHONE; ?></span>
                    <span><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></span>
                </div>
                <div class="header-actions">
                    <a href="#"><i class="fas fa-truck"></i> Free Shipping Over UGX 500,000</a>
                </div>
            </div>
            
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-network-wired"></i>
                    <span><?php echo SITE_NAME; ?></span>
                </a>
                
                <form action="products.php" method="GET" class="search-bar">
                    <input type="text" name="search" placeholder="Search networking equipment...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="nav-icons">
                    <?php if ($auth->isLoggedIn()): ?>
                        <a href="account.php" class="nav-icon">
                            <i class="fas fa-user"></i>
                            <span>Account</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="nav-icon">
                            <i class="fas fa-user"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="wishlist.php" class="nav-icon">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                        <span class="badge"><?php echo count($_SESSION['wishlist']); ?></span>
                    </a>
                    <a href="cart.php" class="nav-icon cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <span class="badge" id="cartCount"><?php echo count($_SESSION['cart']); ?></span>
                    </a>
                </div>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="<?php echo $activePage == 'home' ? 'active' : ''; ?>">Home</a></li>
                <li class="dropdown">
                    <a href="products.php">Products <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-menu">
                        <?php foreach ($categories as $category): ?>
                            <a href="products.php?category=<?php echo $category['slug']; ?>"><?php echo $category['name']; ?></a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li><a href="services.php">Services</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="tel:<?php echo str_replace(' ', '', SITE_PHONE); ?>" class="btn btn-outline"><i class="fas fa-phone"></i> Order Now</a></li>
            </ul>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Professional Networking Equipment & Services</h1>
                <p class="hero-subtitle">Shop top-quality routers, switches, access points, and get expert installation services across Uganda</p>
                <div class="hero-btns">
                    <a href="products.php" class="btn btn-primary">Shop Now <i class="fas fa-arrow-right"></i></a>
                    <a href="services.php" class="btn btn-secondary">Book Installation</a>
                </div>
            </div>
            <div class="hero-stats">
                <?php foreach ($stats as $key => $value): ?>
                    <div class="stat-item">
                        <h3><?php echo $value; ?></h3>
                        <p><?php echo ucfirst($key); ?> <?php echo $key == 'products' ? 'Available' : 'Coverage'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="section categories">
        <div class="container">
            <h2 class="text-center">Shop By Category</h2>
            <p class="section-subtitle text-center">Find the perfect networking equipment for your needs</p>
            
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="products.php?category=<?php echo $category['slug']; ?>" class="category-card">
                        <div class="category-icon">
                            <i class="<?php echo $category['icon']; ?>"></i>
                        </div>
                        <h3><?php echo $category['name']; ?></h3>
                        <p><?php echo $category['description']; ?></p>
                        <span class="category-link">Shop Now <i class="fas fa-arrow-right"></i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section featured-products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <a href="products.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="products-grid">
                <?php if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <?php if ($product['badge']): ?>
                                <div class="product-badge"><?php echo strtoupper($product['badge']); ?></div>
                            <?php endif; ?>
                            <div class="product-image">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="<?php echo $product['category_icon'] ?? 'fas fa-network-wired'; ?>"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo $product['name']; ?></h3>
                                <p class="product-description"><?php echo $product['short_description']; ?></p>
                                <div class="product-price">
                                    <span class="price"><?php echo CURRENCY; ?> <?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['compare_price']): ?>
                                        <span class="old-price"><?php echo CURRENCY; ?> <?php echo number_format($product['compare_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($product['rating'])): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i == ceil($product['rating']) && $product['rating'] % 1 != 0): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span>(<?php echo $product['review_count']; ?>)</span>
                                </div>
                            </div>
                            <form method="POST" action="cart.php?action=add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-outline btn-block add-to-cart">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No featured products available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section testimonials">
        <div class="container">
            <h2 class="text-center">What Our Customers Say</h2>
            <p class="section-subtitle text-center">Trusted by businesses across Uganda</p>
            
            <div class="testimonials-grid">
                <!-- Testimonials would be loaded from database -->
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>"Roncom provided excellent routers and professional installation for our office in Kampala. The network has been flawless for 6 months!"</p>
                    <div class="testimonial-author">
                        <h4>David Mugisha</h4>
                        <span>Kampala Business Owner</span>
                    </div>
                </div>
                <!-- Add more testimonials -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="index.php" class="logo">
                        <i class="fas fa-network-wired"></i>
                        <span><?php echo SITE_NAME; ?></span>
                    </a>
                    <p>Uganda's leading networking equipment store with professional installation services nationwide.</p>
                    <div class="payment-methods">
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-visa"></i>
                        <i class="fas fa-money-bill-wave"></i>
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="services.php">Our Services</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="services.php">Network Installation</a></li>
                        <li><a href="services.php">Equipment Configuration</a></li>
                        <li><a href="services.php">Troubleshooting</a></li>
                        <li><a href="services.php">Cable Clipping</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Contact Info</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-phone"></i> <?php echo SITE_PHONE; ?></li>
                        <li><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></li>
                        <li><i class="fas fa-map-marker-alt"></i> Serving all Uganda</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>