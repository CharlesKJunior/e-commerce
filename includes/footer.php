<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h4><?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></h4>
                <p>Uganda's leading provider of networking equipment and IT solutions.</p>
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Info</h4>
                <p><i class="fas fa-phone"></i> <?php echo SITE_PHONE; ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></p>
                <p><i class="fas fa-map-marker-alt"></i> Kampala, Uganda</p>
            </div>
            
            <div class="footer-section">
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars(SITE_PHONE, ENT_QUOTES, 'UTF-8'); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars(SITE_EMAIL, ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Saturday: 9:00 AM - 4:00 PM</p>
                <p>Sunday: Closed</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Refund Policy</a></li>
            </ul>
        </div>
    </div>
</footer>