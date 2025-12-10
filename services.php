<?php
// services.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "Professional Services - Roncom Networking Store";
$activePage = "services";

$db = new Database();
$conn = $db->getConnection();

// Handle service booking
$bookingSuccess = false;
$bookingError = '';
$bookingData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service'])) {
    $bookingData = [
        'service_type' => trim($_POST['service_type'] ?? ''),
        'service_category' => trim($_POST['service_category'] ?? ''),
        'service_package' => trim($_POST['service_package'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'preferred_date' => trim($_POST['preferred_date'] ?? ''),
        'preferred_time' => trim($_POST['preferred_time'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'customer_email' => trim($_POST['customer_email'] ?? ''),
        'customer_phone' => trim($_POST['customer_phone'] ?? '')
    ];
    
    // Validate booking data
    $errors = [];
    
    if (empty($bookingData['service_type'])) {
        $errors['service_type'] = 'Please select a service type';
    }
    
    if (empty($bookingData['location'])) {
        $errors['location'] = 'Location is required';
    }
    
    if (empty($bookingData['preferred_date'])) {
        $errors['preferred_date'] = 'Preferred date is required';
    } else {
        $selectedDate = new DateTime($bookingData['preferred_date']);
        $today = new DateTime();
        if ($selectedDate < $today) {
            $errors['preferred_date'] = 'Please select a future date';
        }
    }
    
    if (empty($bookingData['preferred_time'])) {
        $errors['preferred_time'] = 'Preferred time is required';
    }
    
    if (empty($bookingData['customer_name'])) {
        $errors['customer_name'] = 'Your name is required';
    }
    
    if (empty($bookingData['customer_email']) || !filter_var($bookingData['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['customer_email'] = 'Valid email is required';
    }
    
    if (empty($bookingData['customer_phone'])) {
        $errors['customer_phone'] = 'Phone number is required';
    }
    
    if (empty($errors)) {
        // Generate booking number
        $bookingNumber = 'SVC-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        // Prepare data for database
        $userId = $auth->isLoggedIn() ? $_SESSION['user_id'] : null;
        $status = 'pending';
        
        // Get service details for cost estimation
        $estimatedCost = 0;
        if ($bookingData['service_package']) {
            $sql = "SELECT price FROM service_packages WHERE slug = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $bookingData['service_package']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $estimatedCost = $row['price'];
            }
        } elseif ($bookingData['service_category']) {
            $sql = "SELECT base_price FROM service_categories WHERE slug = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $bookingData['service_category']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $estimatedCost = $row['base_price'];
            }
        }
        
        // Save booking to database
        $sql = "INSERT INTO service_bookings (
            booking_number, user_id, service_type, location, 
            preferred_date, preferred_time, description, estimated_cost, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $serviceType = $bookingData['service_type'];
        if ($bookingData['service_package']) {
            $serviceType .= " - " . ucfirst(str_replace('-', ' ', $bookingData['service_package']));
        }
        
        $stmt->bind_param(
            "sississds",
            $bookingNumber,
            $userId,
            $serviceType,
            $bookingData['location'],
            $bookingData['preferred_date'],
            $bookingData['preferred_time'],
            $bookingData['description'],
            $estimatedCost,
            $status
        );
        
        if ($stmt->execute()) {
            $bookingId = $conn->insert_id;
            $bookingSuccess = true;
            $bookingData = []; // Clear form
            
            // Send confirmation email
            sendBookingConfirmation($bookingData['customer_email'], $bookingNumber, $serviceType, $bookingData['preferred_date']);
            
            // Send notification to admin
            sendAdminNotification($bookingNumber, $serviceType, $bookingData['location']);
        } else {
            $bookingError = 'Failed to book service. Please try again.';
        }
    } else {
        $bookingError = 'Please correct the errors in the form.';
    }
}

// Get service packages
$servicePackages = [];
$sql = "SELECT * FROM service_packages WHERE is_active = 1 ORDER BY price";
$result = $conn->query($sql);
if ($result) {
    $servicePackages = $result->fetch_all(MYSQLI_ASSOC);
}

// Get service categories
$serviceCategories = [];
$sql = "SELECT * FROM service_categories WHERE is_active = 1 ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    $serviceCategories = $result->fetch_all(MYSQLI_ASSOC);
}

// Get service testimonials
$testimonials = [
    [
        'name' => 'David Mugisha',
        'location' => 'Kampala',
        'rating' => 5,
        'comment' => 'Roncom provided excellent routers and professional installation for our office. The network has been flawless for 6 months!'
    ],
    [
        'name' => 'Sarah Katusiime',
        'location' => 'Mbarara',
        'rating' => 5,
        'comment' => 'Ordered switches and cables for our school. Delivery was prompt and installation was included. Highly recommended!'
    ],
    [
        'name' => 'Robert Turyahabwe',
        'location' => 'Rukungiri',
        'rating' => 5,
        'comment' => 'Great quality access points for our hotel. The team climbed to install them perfectly. WiFi coverage is now excellent!'
    ]
];

// Helper functions
function sendBookingConfirmation($email, $bookingNumber, $serviceType, $date) {
    // In production, implement actual email sending
    // This is just a placeholder
    error_log("Booking confirmation sent to $email for booking $bookingNumber");
}

function sendAdminNotification($bookingNumber, $serviceType, $location) {
    // In production, implement actual notification system
    error_log("New booking: $bookingNumber - $serviceType at $location");
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
        .services-page {
            padding: 60px 0;
        }
        
        .services-intro {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 50px;
        }
        
        .services-intro h2 {
            margin-bottom: 20px;
        }
        
        .service-packages {
            background: var(--light);
            padding: 60px 0;
            margin: 60px 0;
            border-radius: 10px;
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .package-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            transition: var(--transition);
            border: 2px solid transparent;
        }
        
        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .package-card.featured {
            border-color: var(--primary);
        }
        
        .package-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--secondary);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .package-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .package-header h3 {
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .package-price {
            margin-top: 15px;
        }
        
        .package-price .price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }
        
        .package-price .period {
            color: var(--gray);
            font-size: 14px;
        }
        
        .package-features ul {
            list-style: none;
            margin-bottom: 30px;
        }
        
        .package-features li {
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .package-features li:last-child {
            border-bottom: none;
        }
        
        .package-features li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .package-features li i.fa-check {
            color: var(--success);
        }
        
        .package-features li i.fa-times {
            color: var(--danger);
        }
        
        .package-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .service-categories {
            margin: 60px 0;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .service-category {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        
        .service-category:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .category-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .service-category h3 {
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .service-category p {
            color: var(--gray);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .service-features {
            list-style: none;
            margin: 20px 0;
        }
        
        .service-features li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
            font-size: 14px;
        }
        
        .service-features li:before {
            content: '✓';
            color: var(--success);
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .service-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            margin-top: 15px;
        }
        
        .service-link:hover {
            color: var(--primary-dark);
        }
        
        .service-booking {
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin: 60px 0;
        }
        
        .booking-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .booking-form .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .booking-form .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .booking-form .form-group {
            margin-bottom: 20px;
        }
        
        .booking-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .booking-form input,
        .booking-form select,
        .booking-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .booking-form input:focus,
        .booking-form select:focus,
        .booking-form textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 77, 162, 0.1);
            outline: none;
        }
        
        .booking-note {
            background: var(--light);
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .booking-note i {
            color: var(--primary);
            margin-top: 3px;
        }
        
        .service-coverage {
            background: var(--light);
            padding: 50px;
            border-radius: 10px;
            margin: 60px 0;
        }
        
        .coverage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .coverage-area {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .coverage-area h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .coverage-area ul {
            list-style: none;
        }
        
        .coverage-area li {
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .coverage-area li:last-child {
            border-bottom: none;
        }
        
        .coverage-note {
            background: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 30px;
        }
        
        .coverage-note i {
            margin-right: 10px;
        }
        
        .service-testimonials {
            margin: 60px 0;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .testimonial-rating {
            color: var(--secondary);
            margin-bottom: 15px;
        }
        
        .testimonial-card p {
            color: var(--gray);
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .testimonial-author h4 {
            margin-bottom: 5px;
        }
        
        .testimonial-author span {
            color: var(--gray);
            font-size: 14px;
        }
        
        .success-message {
            text-align: center;
            padding: 40px;
            background: #f8fff8;
            border: 2px solid var(--success);
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 48px;
            color: var(--success);
            margin-bottom: 20px;
        }
        
        .error-message {
            background: #fff5f5;
            border: 2px solid var(--danger);
            color: var(--danger);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Professional Networking Services</h1>
            <p>Expert installation, configuration, and support services across Uganda</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section services-page">
        <div class="container">
            <div class="services-intro">
                <h2>Complete Networking Solutions</h2>
                <p>Buy networking equipment with confidence knowing that our expert technicians will handle the installation and setup. We provide end-to-end networking solutions for homes, offices, and businesses across Uganda.</p>
            </div>
            
            <?php if ($bookingSuccess): ?>
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Service Booking Successful!</h3>
                    <p>Thank you for booking our service. Our team will contact you within 24 hours to confirm the details and schedule.</p>
                    <p>You'll receive a confirmation email shortly.</p>
                    <a href="account.php?tab=services" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-calendar-check"></i> View My Bookings
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ($bookingError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $bookingError; ?>
                </div>
            <?php endif; ?>
            
            <!-- Service Packages -->
            <div class="service-packages">
                <h2 class="text-center">Service Packages</h2>
                <p class="section-subtitle text-center">Choose the perfect service package for your needs</p>
                
                <div class="packages-grid">
                    <?php foreach ($servicePackages as $package): ?>
                        <div class="package-card <?php echo $package['is_featured'] ? 'featured' : ''; ?>">
                            <?php if ($package['is_featured']): ?>
                                <div class="package-badge">MOST POPULAR</div>
                            <?php endif; ?>
                            <div class="package-header">
                                <h3><?php echo $package['name']; ?></h3>
                                <p><?php echo $package['description']; ?></p>
                                <div class="package-price">
                                    <span class="price">UGX <?php echo number_format($package['price'], 2); ?></span>
                                    <span class="period"><?php echo $package['duration']; ?></span>
                                </div>
                            </div>
                            <div class="package-features">
                                <ul>
                                    <?php 
                                    $features = json_decode($package['features'], true);
                                    if ($features) {
                                        foreach ($features as $feature): ?>
                                            <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                                        <?php endforeach; 
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="package-actions">
                                <button type="button" class="btn btn-primary book-package-btn" 
                                        data-package="<?php echo $package['slug']; ?>"
                                        data-package-name="<?php echo $package['name']; ?>"
                                        data-price="<?php echo $package['price']; ?>">
                                    <i class="fas fa-calendar-check"></i> Book Now
                                </button>
                                <button type="button" class="btn btn-outline add-service-to-cart" 
                                        data-service-type="package"
                                        data-service-id="<?php echo $package['id']; ?>"
                                        data-service-name="<?php echo $package['name']; ?>"
                                        data-price="<?php echo $package['price']; ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Service Categories -->
            <div class="service-categories">
                <h2 class="text-center">Our Services</h2>
                <p class="section-subtitle text-center">Professional networking services tailored to your needs</p>
                
                <div class="category-grid">
                    <?php foreach ($serviceCategories as $category): ?>
                        <div class="service-category">
                            <div class="category-icon">
                                <i class="<?php echo $category['icon']; ?>"></i>
                            </div>
                            <div class="category-info">
                                <h3><?php echo $category['name']; ?></h3>
                                <p><?php echo $category['description']; ?></p>
                                <div class="service-features">
                                    <li>Site survey and planning</li>
                                    <li>Professional installation</li>
                                    <li>Equipment configuration</li>
                                    <li>Testing and optimization</li>
                                    <li>After-service support</li>
                                </div>
                                <div class="service-price" style="margin: 20px 0; font-weight: 600; color: var(--primary);">
                                    Starting from UGX <?php echo number_format($category['base_price'], 2); ?>
                                </div>
                                <button type="button" class="service-link book-category-btn"
                                        data-category="<?php echo $category['slug']; ?>"
                                        data-category-name="<?php echo $category['name']; ?>"
                                        data-base-price="<?php echo $category['base_price']; ?>">
                                    <i class="fas fa-calendar-check"></i> Book <?php echo $category['name']; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Service Testimonials -->
            <div class="service-testimonials">
                <h2 class="text-center">What Our Clients Say</h2>
                <p class="section-subtitle text-center">Trusted by businesses across Uganda</p>
                
                <div class="testimonials-grid">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $testimonial['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <p>"<?php echo $testimonial['comment']; ?>"</p>
                            <div class="testimonial-author">
                                <div>
                                    <h4><?php echo $testimonial['name']; ?></h4>
                                    <span><?php echo $testimonial['location']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Service Booking Form -->
            <div class="service-booking" id="bookingForm">
                <div class="booking-header">
                    <h2>Book a Service</h2>
                    <p>Fill out the form below to schedule professional networking services</p>
                </div>
                
                <form method="POST" action="services.php" id="serviceBookingForm">
                    <input type="hidden" name="book_service" value="1">
                    <input type="hidden" name="service_type" id="serviceTypeInput" value="">
                    <input type="hidden" name="service_category" id="serviceCategoryInput" value="">
                    <input type="hidden" name="service_package" id="servicePackageInput" value="">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="service_selection">Service Type *</label>
                            <input type="text" id="service_selection" readonly 
                                   placeholder="Select a service package or category above" required>
                            <small id="selectedServicePrice" style="display: block; margin-top: 5px; color: var(--primary); font-weight: 600;"></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Service Location *</label>
                            <input type="text" id="location" name="location" required 
                                   value="<?php echo htmlspecialchars($bookingData['location'] ?? ''); ?>"
                                   placeholder="e.g., Kampala, Mbarara, Rukungiri">
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="preferred_date">Preferred Date *</label>
                            <input type="date" id="preferred_date" name="preferred_date" required
                                   value="<?php echo htmlspecialchars($bookingData['preferred_date'] ?? ''); ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="preferred_time">Preferred Time *</label>
                            <select id="preferred_time" name="preferred_time" required>
                                <option value="">Select Time</option>
                                <option value="morning" <?php echo ($bookingData['preferred_time'] ?? '') == 'morning' ? 'selected' : ''; ?>>Morning (8AM - 12PM)</option>
                                <option value="afternoon" <?php echo ($bookingData['preferred_time'] ?? '') == 'afternoon' ? 'selected' : ''; ?>>Afternoon (12PM - 4PM)</option>
                                <option value="evening" <?php echo ($bookingData['preferred_time'] ?? '') == 'evening' ? 'selected' : ''; ?>>Evening (4PM - 6PM)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Service Requirements *</label>
                        <textarea id="description" name="description" rows="4" required 
                                  placeholder="Please describe what you need help with..."><?php echo htmlspecialchars($bookingData['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customer_name">Your Name *</label>
                            <input type="text" id="customer_name" name="customer_name" required
                                   value="<?php echo htmlspecialchars($bookingData['customer_name'] ?? ($auth->isLoggedIn() ? $_SESSION['user_name'] : '')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_email">Email Address *</label>
                            <input type="email" id="customer_email" name="customer_email" required
                                   value="<?php echo htmlspecialchars($bookingData['customer_email'] ?? ($auth->isLoggedIn() ? $_SESSION['user_email'] : '')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone">Phone Number *</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required
                                   value="<?php echo htmlspecialchars($bookingData['customer_phone'] ?? ''); ?>"
                                   placeholder="+256 XXX XXX XXX">
                        </div>
                    </div>
                    
                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Booking Request
                        </button>
                        <button type="reset" class="btn btn-outline">
                            Clear Form
                        </button>
                    </div>
                    
                    <div class="booking-note">
                        <i class="fas fa-info-circle"></i>
                        <span>We'll contact you within 24 hours to confirm your booking and provide a final quote.</span>
                    </div>
                </form>
            </div>
            
            <!-- Service Coverage -->
            <div class="service-coverage">
                <h2 class="text-center">Service Coverage Areas</h2>
                <p class="section-subtitle text-center">We provide networking services throughout Uganda</p>
                
                <div class="coverage-grid">
                    <div class="coverage-area">
                        <h3><i class="fas fa-city"></i> Central Uganda</h3>
                        <ul>
                            <li>Kampala</li>
                            <li>Entebbe</li>
                            <li>Jinja</li>
                            <li>Mukono</li>
                            <li>Masaka</li>
                        </ul>
                    </div>
                    
                    <div class="coverage-area">
                        <h3><i class="fas fa-mountain"></i> Western Uganda</h3>
                        <ul>
                            <li>Mbarara</li>
                            <li>Rukungiri</li>
                            <li>Fort Portal</li>
                            <li>Kabale</li>
                            <li>Kasese</li>
                        </ul>
                    </div>
                    
                    <div class="coverage-area">
                        <h3><i class="fas fa-map"></i> Nationwide</h3>
                        <ul>
                            <li>Gulu (Northern)</li>
                            <li>Mbale (Eastern)</li>
                            <li>Arua</li>
                            <li>Lira</li>
                            <li>And all other regions!</li>
                        </ul>
                    </div>
                </div>
                
                <div class="coverage-note">
                    <p><i class="fas fa-car"></i> <strong>We come to you!</strong> Our technicians travel to your location anywhere in Uganda to provide professional networking services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    // Service booking buttons
    document.querySelectorAll('.book-package-btn, .book-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const serviceType = this.classList.contains('book-package-btn') ? 'package' : 'category';
            const serviceId = this.dataset[serviceType];
            const serviceName = this.dataset[serviceType + 'Name'];
            const price = this.dataset.price || this.dataset.basePrice;
            
            // Set form values
            document.getElementById('service_selection').value = serviceName;
            document.getElementById('selectedServicePrice').textContent = 'Price: UGX ' + parseFloat(price).toLocaleString();
            document.getElementById('serviceTypeInput').value = serviceType;
            
            if (serviceType === 'package') {
                document.getElementById('servicePackageInput').value = serviceId;
                document.getElementById('serviceCategoryInput').value = '';
            } else {
                document.getElementById('serviceCategoryInput').value = serviceId;
                document.getElementById('servicePackageInput').value = '';
            }
            
            // Scroll to booking form
            document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });
        });
    });
    
    // Add service to cart
    document.querySelectorAll('.add-service-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const serviceId = this.dataset.serviceId;
            const serviceName = this.dataset.serviceName;
            const price = this.dataset.price;
            
            // In a real application, you would make an AJAX request to add to cart
            fetch('api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    service_name: serviceName,
                    price: price,
                    type: 'service'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(serviceName + ' added to cart!');
                    // Update cart count
                    if (data.cart_count) {
                        document.getElementById('cartCount').textContent = data.cart_count;
                    }
                } else {
                    alert('Failed to add service to cart: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        });
    });
    
    // Format phone number
    document.getElementById('customer_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.startsWith('256')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+256' + value.substring(1);
        }
        
        e.target.value = value;
    });
    
    // Form validation
    document.getElementById('serviceBookingForm').addEventListener('submit', function(e) {
        const serviceSelection = document.getElementById('service_selection').value;
        if (!serviceSelection) {
            e.preventDefault();
            alert('Please select a service package or category first');
            return;
        }
        
        // Validate phone number
        const phone = document.getElementById('customer_phone').value;
        const phoneRegex = /^(\+256|0)[0-9]{9}$/;
        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid Uganda phone number');
            document.getElementById('customer_phone').focus();
            return;
        }
        
        // Validate date
        const selectedDate = new Date(document.getElementById('preferred_date').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            e.preventDefault();
            alert('Please select a future date');
            document.getElementById('preferred_date').focus();
            return;
        }
    });
    </script>
</body>
</html>