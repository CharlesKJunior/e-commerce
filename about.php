<?php
// about.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "About Us - Roncom Networking Store";
$activePage = "about";

$db = new Database();
$conn = $db->getConnection();
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
        .about-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #0a4da2 100%);
            color: white;
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="M500,50L506,55C512,60,524,70,536,75C548,80,560,80,572,75C584,70,596,60,608,55C620,50,632,50,644,55C656,60,668,70,680,75C692,80,704,80,716,75C728,70,740,60,752,55C764,50,776,50,788,55C800,60,812,70,824,75C836,80,848,80,860,75C872,70,884,60,896,55C908,50,920,50,932,55C944,60,956,70,968,75C980,80,992,80,1000,75L1000,100L0,100L0,75C8,80,20,80,32,75C44,70,56,60,68,55C80,50,92,50,104,55C116,60,128,70,140,75C152,80,164,80,176,75C188,70,200,60,212,55C224,50,236,50,248,55C260,60,272,70,284,75C296,80,308,80,320,75C332,70,344,60,356,55C368,50,380,50,392,55C404,60,416,70,428,75C440,80,452,80,464,75C476,70,488,60,500,50Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 100% 100%;
        }
        
        .about-hero-content {
            position: relative;
            z-index: 1;
        }
        
        .about-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .about-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .about-section {
            padding: 80px 0;
        }
        
        .about-section:nth-child(even) {
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
        
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        @media (max-width: 992px) {
            .about-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }
        
        .about-text h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .about-text p {
            margin-bottom: 20px;
            color: var(--gray);
            line-height: 1.8;
        }
        
        .about-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        
        .stat-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .team-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .team-image {
            height: 250px;
            overflow: hidden;
        }
        
        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .team-card:hover .team-image img {
            transform: scale(1.1);
        }
        
        .team-info {
            padding: 25px;
            text-align: center;
        }
        
        .team-info h4 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .team-info .position {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }
        
        .team-info p {
            color: var(--gray);
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .team-social {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .team-social a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .team-social a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .value-card {
            background: white;
            padding: 40px 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .value-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 25px;
        }
        
        .value-card h4 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .value-card p {
            color: var(--gray);
            line-height: 1.7;
        }
        
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 50px auto 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: var(--primary);
        }
        
        @media (max-width: 768px) {
            .timeline::before {
                left: 30px;
            }
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 50px;
        }
        
        @media (max-width: 768px) {
            .timeline-item {
                padding-left: 60px;
            }
        }
        
        .timeline-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: calc(50% - 40px);
        }
        
        @media (max-width: 768px) {
            .timeline-content {
                width: 100%;
            }
        }
        
        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: auto;
        }
        
        @media (max-width: 768px) {
            .timeline-item:nth-child(odd) .timeline-content {
                margin-left: 0;
            }
        }
        
        .timeline-year {
            position: absolute;
            top: 0;
            width: 100px;
            height: 100px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }
        
        @media (max-width: 768px) {
            .timeline-year {
                left: 30px;
                transform: translateX(-50%);
                width: 60px;
                height: 60px;
                font-size: 1rem;
            }
        }
        
        .timeline-content h4 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .timeline-content p {
            color: var(--gray);
            line-height: 1.7;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, #0a4da2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .cta-content p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-outline-light {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-outline-light:hover {
            background: white;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <h1>Connecting Uganda, One Network at a Time</h1>
                <p>Roncom is Uganda's leading provider of networking equipment and IT solutions, dedicated to empowering businesses with reliable connectivity and cutting-edge technology.</p>
                <a href="products.php" class="btn btn-light">Explore Our Products</a>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Story</h2>
                <p>From humble beginnings to becoming a trusted name in networking solutions</p>
            </div>
            
            <div class="about-content">
                <div class="about-text">
                    <h3>Building Networks Since 2015</h3>
                    <p>Founded in 2015, Roncom Networking Store started as a small shop in Kampala with a vision to provide reliable networking equipment to Uganda's growing digital landscape. Our founder, Ronald Kasule, recognized the need for quality networking solutions in a market dominated by substandard imports.</p>
                    <p>What began as a one-person operation has grown into a team of over 50 networking specialists, serving businesses across Uganda. We've expanded from a single store to multiple locations nationwide, with a comprehensive online store reaching customers in every region.</p>
                    <p>Our journey has been guided by a simple principle: provide reliable, affordable, and efficient networking solutions backed by exceptional customer service. This commitment has earned us the trust of over 5,000 satisfied customers, including government institutions, educational establishments, and private businesses.</p>
                </div>
                <div class="about-image">
                    <img src="images/about-story.jpg" alt="Our Team at Roncom" onerror="this.src='https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-number">8+</div>
                    <div class="stat-label">Years in Business</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">5,000+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Regions Covered</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Technical Experts</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Mission & Vision</h2>
                <p>Driving Uganda's digital transformation through innovative networking solutions</p>
            </div>
            
            <div class="about-content">
                <div class="about-image">
                    <img src="images/about-mission.jpg" alt="Networking Infrastructure" onerror="this.src='https://images.unsplash.com/photo-1558494949-ef010cbdcc31?ixlib=rb-4.0.3&auto=format&fit=crop&w-800&q=80'">
                </div>
                <div class="about-text">
                    <h3>Our Mission</h3>
                    <p>To provide reliable, affordable, and cutting-edge networking solutions that empower Ugandan businesses to thrive in the digital age. We aim to bridge the connectivity gap by offering quality products, expert installation services, and comprehensive support to ensure seamless network performance.</p>
                    
                    <h3 style="margin-top: 40px;">Our Vision</h3>
                    <p>To be Uganda's leading networking solutions provider, recognized for innovation, reliability, and exceptional customer service. We envision a fully connected Uganda where every business, regardless of size or location, has access to robust networking infrastructure.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Core Values</h2>
                <p>The principles that guide everything we do at Roncom</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Reliability</h4>
                    <p>We stand behind every product we sell and every service we provide. Our solutions are built to last and perform consistently under Ugandan conditions.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p>We continuously explore new technologies and solutions to bring the best networking advancements to our customers, staying ahead of industry trends.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p>We believe in transparent pricing, honest recommendations, and ethical business practices. Our customers' trust is our most valuable asset.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>Customer Focus</h4>
                    <p>Every decision we make centers around our customers' needs. We listen, understand, and deliver solutions that exceed expectations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Journey Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Journey</h2>
                <p>Key milestones in our growth and development</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-year">2015</div>
                    <div class="timeline-content">
                        <h4>Company Founding</h4>
                        <p>Roncom Networking Store opened its first store in Kampala with a focus on providing quality networking cables and basic equipment.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2017</div>
                    <div class="timeline-content">
                        <h4>Expansion to Services</h4>
                        <p>Launched our professional installation and maintenance services, becoming a full-service networking solutions provider.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2019</div>
                    <div class="timeline-content">
                        <h4>Regional Expansion</h4>
                        <p>Opened branches in Mbarara and Gulu, expanding our reach beyond Kampala to serve customers across Uganda.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2021</div>
                    <div class="timeline-content">
                        <h4>Online Store Launch</h4>
                        <p>Launched our e-commerce platform, making our products and services accessible nationwide with door-to-door delivery.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2023</div>
                    <div class="timeline-content">
                        <h4>Strategic Partnerships</h4>
                        <p>Established partnerships with leading international brands like Cisco, TP-Link, and Ubiquiti to offer premium products.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Meet Our Leadership Team</h2>
                <p>The experts behind Roncom's success</p>
            </div>
            
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-image">
                        <img src="images/team-ronald.jpg" alt="Ronald Kasule" onerror="this.src='https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="team-info">
                        <h4>Ronald Kasule</h4>
                        <span class="position">Founder & CEO</span>
                        <p>With over 15 years of experience in networking and telecommunications, Ronald founded Roncom with a vision to transform Uganda's connectivity landscape.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-image">
                        <img src="images/team-sarah.jpg" alt="Sarah Nakato" onerror="this.src='https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="team-info">
                        <h4>Sarah Nakato</h4>
                        <span class="position">Technical Director</span>
                        <p>Sarah leads our technical team with expertise in network design and implementation. She holds multiple Cisco certifications and has implemented over 500 network solutions.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-image">
                        <img src="images/team-david.jpg" alt="David Ochieng" onerror="this.src='https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="team-info">
                        <h4>David Ochieng</h4>
                        <span class="position">Operations Manager</span>
                        <p>David oversees our nationwide operations, ensuring smooth logistics and timely delivery of products and services across all regions of Uganda.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-image">
                        <img src="images/team-grace.jpg" alt="Grace Namugga" onerror="this.src='https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="team-info">
                        <h4>Grace Namugga</h4>
                        <span class="position">Customer Success Manager</span>
                        <p>Grace leads our customer support team, ensuring every client receives exceptional service and support throughout their journey with Roncom.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Upgrade Your Network?</h2>
                <p>Join thousands of satisfied customers who trust Roncom for their networking needs. Whether you need equipment, installation, or consultation, we're here to help.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-light">Contact Us</a>
                    <a href="products.php" class="btn btn-outline-light">Browse Products</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>