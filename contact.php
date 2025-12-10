<?php
// Include FIRST - before any HTML
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$pageTitle = "Contact - Roncom Networking Store";
$activePage = "contact";

$db = new Database();
$conn = $db->getConnection();

// Handle contact form submission
$successMessage = '';
$errorMessage = '';
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($errors)) {
        // Save to database
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            $successMessage = 'Thank you for contacting us! We will get back to you within 24 hours.';
            
            // Send email notification (in production, you would implement this)
            // $to = "info@roncom.com";
            // $emailSubject = "New Contact Message: " . $subject;
            // $emailBody = "Name: $name\nEmail: $email\nPhone: $phone\nMessage:\n$message";
            // mail($to, $emailSubject, $emailBody);
            
            // Clear form data
            $formData = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'message' => ''
            ];
        } else {
            $errorMessage = 'Sorry, there was an error sending your message. Please try again later.';
            $formData = compact('name', 'email', 'phone', 'subject', 'message');
        }
    } else {
        $errorMessage = implode('<br>', $errors);
        $formData = compact('name', 'email', 'phone', 'subject', 'message');
    }
}

// Get contact information
$contactInfo = [
    'phone' => '+256 700 123 456',
    'email' => 'info@roncom.com',
    'address' => 'Plot 24, Kampala Road<br>Kampala, Uganda',
    'hours' => 'Mon-Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 4:00 PM<br>Sun: Closed'
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
    <style>
        .contact-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #0a4da2 100%);
            color: white;
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="M500,50L506,55C512,60,524,70,536,75C548,80,560,80,572,75C584,70,596,60,608,55C620,50,632,50,644,55C656,60,668,70,680,75C692,80,704,80,716,75C728,70,740,60,752,55C764,50,776,50,788,55C800,60,812,70,824,75C836,80,848,80,860,75C872,70,884,60,896,55C908,50,920,50,932,55C944,60,956,70,968,75C980,80,992,80,1000,75L1000,100L0,100L0,75C8,80,20,80,32,75C44,70,56,60,68,55C80,50,92,50,104,55C116,60,128,70,140,75C152,80,164,80,176,75C188,70,200,60,212,55C224,50,236,50,248,55C260,60,272,70,284,75C296,80,308,80,320,75C332,70,344,60,356,55C368,50,380,50,392,55C404,60,416,70,428,75C440,80,452,80,464,75C476,70,488,60,500,50Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 100% 100%;
        }
        
        .contact-hero-content {
            position: relative;
            z-index: 1;
        }
        
        .contact-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .contact-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .contact-section {
            padding: 80px 0;
        }
        
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }
        
        @media (max-width: 992px) {
            .contact-layout {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }
        
        .contact-info {
            padding: 40px;
            background: var(--light);
            border-radius: 10px;
        }
        
        .contact-info h2 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: var(--dark);
            font-family: 'Montserrat', sans-serif;
        }
        
        .info-grid {
            display: grid;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-item {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        
        .info-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .info-content h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .info-content p {
            color: var(--gray);
            line-height: 1.7;
        }
        
        .business-hours {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 1px solid var(--gray-light);
        }
        
        .business-hours h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .hours-list {
            list-style: none;
            padding: 0;
        }
        
        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
            color: var(--gray);
        }
        
        .hours-list li:last-child {
            border-bottom: none;
        }
        
        .hours-list strong {
            color: var(--dark);
        }
        
        .contact-form-container {
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .contact-form-container h2 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: var(--dark);
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(10, 77, 162, 0.1);
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
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
        
        .branches-section {
            padding: 80px 0;
            background: var(--light);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 15px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .section-title p {
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
            font-size: 1.1rem;
        }
        
        .branches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .branch-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .branch-image {
            height: 200px;
            overflow: hidden;
        }
        
        .branch-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .branch-card:hover .branch-image img {
            transform: scale(1.1);
        }
        
        .branch-info {
            padding: 30px;
        }
        
        .branch-info h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .branch-details {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .branch-details li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--gray);
        }
        
        .branch-details i {
            color: var(--primary);
            margin-top: 3px;
        }
        
        .branch-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-direction {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--light);
            border: none;
            border-radius: 5px;
            color: var(--dark);
            text-decoration: none;
            font-size: 14px;
            transition: var(--transition);
        }
        
        .btn-direction:hover {
            background: var(--primary);
            color: white;
        }
        
        .map-section {
            padding: 80px 0;
        }
        
        .map-container {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .faq-section {
            padding: 80px 0;
            background: var(--light);
        }
        
        .faq-grid {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .faq-question {
            padding: 25px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--dark);
            transition: var(--transition);
        }
        
        .faq-question:hover {
            background: rgba(10, 77, 162, 0.05);
        }
        
        .faq-question.active {
            background: var(--primary);
            color: white;
        }
        
        .faq-question i {
            transition: transform 0.3s ease;
        }
        
        .faq-question.active i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .faq-answer.active {
            padding: 0 30px 25px;
            max-height: 500px;
        }
        
        .faq-answer p {
            color: var(--gray);
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <!-- Header already included above -->

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1>Get In Touch</h1>
                <p>Have questions about our products or services? Our team is ready to help you with expert advice and support.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
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
            
            <div class="contact-layout">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Contact Information</h2>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="info-content">
                                <h4>Phone Number</h4>
                                <p><?php echo $contactInfo['phone']; ?></p>
                                <p style="font-size: 14px; margin-top: 5px;">Available Mon-Sat, 8AM-6PM</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h4>Email Address</h4>
                                <p><?php echo $contactInfo['email']; ?></p>
                                <p style="font-size: 14px; margin-top: 5px;">We respond within 24 hours</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h4>Main Office</h4>
                                <p><?php echo $contactInfo['address']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="business-hours">
                        <h3>Business Hours</h3>
                        <ul class="hours-list">
                            <li>
                                <strong>Monday - Friday:</strong>
                                <span>8:00 AM - 6:00 PM</span>
                            </li>
                            <li>
                                <strong>Saturday:</strong>
                                <span>9:00 AM - 4:00 PM</span>
                            </li>
                            <li>
                                <strong>Sunday:</strong>
                                <span>Closed</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>Send Us a Message</h2>
                    <form method="POST" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Your Name *</label>
                                <input type="text" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($formData['name']); ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($formData['email']); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <select id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="General Inquiry" <?php echo $formData['subject'] == 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Product Information" <?php echo $formData['subject'] == 'Product Information' ? 'selected' : ''; ?>>Product Information</option>
                                    <option value="Technical Support" <?php echo $formData['subject'] == 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                    <option value="Service Booking" <?php echo $formData['subject'] == 'Service Booking' ? 'selected' : ''; ?>>Service Booking</option>
                                    <option value="Bulk Order" <?php echo $formData['subject'] == 'Bulk Order' ? 'selected' : ''; ?>>Bulk Order</option>
                                    <option value="Partnership" <?php echo $formData['subject'] == 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                                    <option value="Other" <?php echo $formData['subject'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" required><?php echo htmlspecialchars($formData['message']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Branches Section -->
    <section class="branches-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Branches</h2>
                <p>Visit us at any of our convenient locations across Uganda</p>
            </div>
            
            <div class="branches-grid">
                <div class="branch-card">
                    <div class="branch-image">
                        <img src="images/branch-kampala.jpg" alt="Kampala Branch" onerror="this.src='https://images.unsplash.com/photo-1558561594-fd3d3db8a097?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="branch-info">
                        <h3>Kampala Main Branch</h3>
                        <ul class="branch-details">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Plot 24, Kampala Road, Kampala</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>+256 700 123 456</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon-Fri: 8AM-6PM, Sat: 9AM-4PM</span>
                            </li>
                        </ul>
                        <div class="branch-actions">
                            <a href="tel:+256700123456" class="btn-direction">
                                <i class="fas fa-phone"></i> Call Now
                            </a>
                            <a href="https://maps.google.com/?q=Plot+24+Kampala+Road+Kampala" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="branch-card">
                    <div class="branch-image">
                        <img src="images/branch-mbarara.jpg" alt="Mbarara Branch" onerror="this.src='https://images.unsplash.com/photo-1511895426328-dc8714191300?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="branch-info">
                        <h3>Mbarara Branch</h3>
                        <ul class="branch-details">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Plot 15, Mbarara High Street, Mbarara</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>+256 700 123 457</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon-Fri: 8AM-6PM, Sat: 9AM-4PM</span>
                            </li>
                        </ul>
                        <div class="branch-actions">
                            <a href="tel:+256700123457" class="btn-direction">
                                <i class="fas fa-phone"></i> Call Now
                            </a>
                            <a href="https://maps.google.com/?q=Plot+15+Mbarara+High+Street+Mbarara" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="branch-card">
                    <div class="branch-image">
                        <img src="images/branch-gulu.jpg" alt="Gulu Branch" onerror="this.src='https://images.unsplash.com/photo-1500917293891-ef795e70e1f6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="branch-info">
                        <h3>Gulu Branch</h3>
                        <ul class="branch-details">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Plot 8, Gulu Main Street, Gulu</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>+256 700 123 458</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon-Fri: 8AM-6PM, Sat: 9AM-4PM</span>
                            </li>
                        </ul>
                        <div class="branch-actions">
                            <a href="tel:+256700123458" class="btn-direction">
                                <i class="fas fa-phone"></i> Call Now
                            </a>
                            <a href="https://maps.google.com/?q=Plot+8+Gulu+Main+Street+Gulu" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="section-title">
                <h2>Find Us on Map</h2>
                <p>Visit our main office in Kampala or contact us for directions to any branch</p>
            </div>
            
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7536223157963!2d32.58164727465788!3d0.313181564054372!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x177dbb93c5695ab7%3A0x8e9d5fbd5a5b73b5!2sKampala%2C%20Uganda!5e0!3m2!1sen!2sus!4v1641234567890!5m2!1sen!2sus" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Find quick answers to common questions about our products and services</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What payment methods do you accept?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We accept multiple payment methods including mobile money (MTN, Airtel), bank transfers, cash on delivery, and credit/debit cards. For corporate clients, we offer credit facilities with approved credit terms.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Do you offer installation services?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we offer professional installation services for all networking equipment. Our certified technicians provide site surveys, installation, configuration, and testing. Installation fees vary based on the scope of work and location.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What is your delivery time and coverage?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We deliver nationwide across Uganda. Delivery times are 1-2 business days for Kampala, 2-3 days for major towns, and 3-5 days for upcountry locations. Express delivery options are available for urgent orders.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Do you provide warranty on your products?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>All our products come with manufacturer's warranty ranging from 1-3 years depending on the product. We also offer extended warranty options. Our technical support team provides warranty claims assistance.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can I get technical support after purchase?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we provide comprehensive technical support via phone, email, and on-site visits. Our support team is available during business hours, and we offer emergency support for critical network issues.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Do you offer bulk purchase discounts?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we offer significant discounts for bulk purchases and corporate orders. Contact our sales team for customized quotes based on your volume requirements. We also offer special pricing for educational institutions and government organizations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // FAQ Toggle Functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const isActive = question.classList.contains('active');
                
                // Close all other FAQs
                document.querySelectorAll('.faq-question').forEach(q => {
                    q.classList.remove('active');
                    q.nextElementSibling.classList.remove('active');
                });
                
                // Toggle current FAQ if it wasn't active
                if (!isActive) {
                    question.classList.add('active');
                    answer.classList.add('active');
                }
            });
        });
        
        // Form validation
        document.querySelector('.contact-form').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;
            
            if (phone && !/^\+?[\d\s\-\(\)]{10,}$/.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                document.getElementById('phone').focus();
                return false;
            }
            
            if (message.trim().length < 10) {
                e.preventDefault();
                alert('Please enter a message of at least 10 characters');
                document.getElementById('message').focus();
                return false;
            }
        });
        
        // Auto-resize textarea
        document.getElementById('message').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>