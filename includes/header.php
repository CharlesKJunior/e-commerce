<?php
// includes/header.php
$activePage = $activePage ?? 'home';
?>
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
                    <a href="account.php" class="nav-icon <?php echo $activePage == 'account' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-icon">
                        <i class="fas fa-user"></i>
                        <span>Login</span>
                    </a>
                <?php endif; ?>
                
                <a href="wishlist.php" class="nav-icon <?php echo $activePage == 'wishlist' ? 'active' : ''; ?>">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                    <span class="badge"><?php echo count($_SESSION['wishlist']); ?></span>
                </a>
                <a href="cart.php" class="nav-icon cart-icon <?php echo $activePage == 'cart' ? 'active' : ''; ?>">
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
                <a href="products.php" class="<?php echo $activePage == 'products' ? 'active' : ''; ?>">Products <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <a href="products.php?category=routers">Routers</a>
                    <a href="products.php?category=switches">Switches</a>
                    <a href="products.php?category=access-points">Access Points</a>
                    <a href="products.php?category=cables">Cables & Connectors</a>
                    <a href="products.php?category=security">Network Security</a>
                </div>
            </li>
            <li><a href="services.php" class="<?php echo $activePage == 'services' ? 'active' : ''; ?>">Services</a></li>
            <li><a href="about.php" class="<?php echo $activePage == 'about' ? 'active' : ''; ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo $activePage == 'contact' ? 'active' : ''; ?>">Contact</a></li>
            <li><a href="tel:<?php echo str_replace(' ', '', SITE_PHONE); ?>" class="btn btn-outline"><i class="fas fa-phone"></i> Order Now</a></li>
        </ul>
    </div>
</header>