// js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Close mobile menu when clicking on a link
    const navItems = document.querySelectorAll('.nav-links a');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    });
    
    // Update current year in footer
    const currentYear = new Date().getFullYear();
    const yearElements = document.querySelectorAll('#currentYear');
    yearElements.forEach(element => {
        element.textContent = currentYear;
    });
    
    // Contact form handling
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const name = formData.get('name');
            const email = formData.get('email');
            const phone = formData.get('phone');
            const location = formData.get('location');
            const service = formData.get('service');
            const message = formData.get('message');
            
            // Simple validation
            if (!name || !email || !phone || !location || !message) {
                showFormMessage('Please fill in all required fields.', 'error');
                return;
            }
            
            // In a real application, you would send the data to a server here
            // For this example, we'll just show a success message
            showFormMessage('Thank you for your message! We will contact you soon.', 'success');
            
            // Reset form
            contactForm.reset();
            
            // Scroll to message
            document.getElementById('formMessage').scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    // Helper function for form messages
    function showFormMessage(text, type) {
        const formMessage = document.getElementById('formMessage');
        formMessage.textContent = text;
        formMessage.className = 'form-message ' + type;
        formMessage.style.display = 'block';
        
        // Hide message after 5 seconds
        setTimeout(() => {
            formMessage.style.display = 'none';
        }, 5000);
    }
    
    // Add active class to current page in navigation
    const currentPage = window.location.pathname.split('/').pop();
    const navLinksAll = document.querySelectorAll('.nav-links a');
    
    navLinksAll.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only process if it's an anchor link (starts with #)
            if (href.startsWith('#')) {
                e.preventDefault();
                
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Animate elements on scroll
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.service-card, .feature-card, .area-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Set initial state for animation
    const animatedElements = document.querySelectorAll('.service-card, .feature-card, .area-card');
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    // Run animation on load and scroll
    window.addEventListener('load', animateOnScroll);
    window.addEventListener('scroll', animateOnScroll);
});



// js/script.js - E-commerce Version
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Cart functionality
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const closeCart = document.getElementById('closeCart');
    const cartCount = document.getElementById('cartCount');
    const cartItems = document.getElementById('cartItems');
    
    // Sample product data
    const products = [
        {
            id: 1,
            name: "Cisco RV340 Dual WAN Router",
            price: 850000,
            oldPrice: 950000,
            category: "routers",
            description: "Business-grade router with VPN capabilities",
            rating: 4.5,
            reviews: 28,
            badge: "BEST SELLER"
        },
        {
            id: 2,
            name: "TP-Link 24-Port Gigabit Switch",
            price: 420000,
            category: "switches",
            description: "Managed switch with VLAN support",
            rating: 4.8,
            reviews: 35
        },
        {
            id: 3,
            name: "Ubiquiti Access Point U6-Pro",
            price: 650000,
            category: "access-points",
            description: "WiFi 6 access point for high-density areas",
            rating: 4.7,
            reviews: 42,
            badge: "NEW"
        },
        {
            id: 4,
            name: "Cat6 Ethernet Cable 100m",
            price: 120000,
            oldPrice: 150000,
            category: "cables",
            description: "High-speed network cable with connectors",
            rating: 4.3,
            reviews: 24
        },
        {
            id: 5,
            name: "Network Security Firewall",
            price: 1200000,
            category: "security",
            description: "Enterprise-grade security appliance",
            rating: 5,
            reviews: 18,
            badge: "SALE"
        },
        {
            id: 6,
            name: "Outdoor Access Point",
            price: 750000,
            category: "access-points",
            description: "Weatherproof WiFi for outdoor areas",
            rating: 4.9,
            reviews: 32
        }
    ];
    
    // Cart state
    let cart = JSON.parse(localStorage.getItem('roncomCart')) || [];
    
    // Initialize cart count
    updateCartCount();
    
    // Load featured products on homepage
    const featuredProducts = document.getElementById('featuredProducts');
    if (featuredProducts) {
        loadFeaturedProducts();
    }
    
    // Load products on products page
    const productsGrid = document.getElementById('productsGrid');
    if (productsGrid) {
        loadAllProducts();
    }
    
    // Cart sidebar toggle
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        cartIcon.addEventListener('click', function(e) {
            e.preventDefault();
            openCart();
        });
    }
    
    if (closeCart) {
        closeCart.addEventListener('click', closeCartSidebar);
    }
    
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartSidebar);
    }
    
    // Add to cart buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            const productId = parseInt(e.target.dataset.id || e.target.closest('.add-to-cart').dataset.id);
            const product = products.find(p => p.id === productId);
            if (product) {
                addToCart(product);
                openCart();
            }
        }
        
        // Remove item from cart
        if (e.target.classList.contains('remove-item') || 
            e.target.closest('.remove-item')) {
            const itemId = parseInt(e.target.dataset.id || e.target.closest('.remove-item').dataset.id);
            removeFromCart(itemId);
        }
        
        // Update cart quantity
        if (e.target.classList.contains('qty-btn')) {
            const input = e.target.parentElement.querySelector('.qty-input');
            const itemId = parseInt(e.target.closest('.cart-item').dataset.id);
            
            if (e.target.classList.contains('minus')) {
                input.value = Math.max(1, parseInt(input.value) - 1);
            } else if (e.target.classList.contains('plus')) {
                input.value = parseInt(input.value) + 1;
            }
            
            updateCartItemQuantity(itemId, parseInt(input.value));
        }
    });
    
    // Functions
    function loadFeaturedProducts() {
        const featured = products.slice(0, 4);
        featuredProducts.innerHTML = featured.map(product => `
            <div class="product-card">
                ${product.badge ? `<div class="product-badge">${product.badge}</div>` : ''}
                <div class="product-image">
                    <div class="image-placeholder">
                        <i class="fas fa-${getProductIcon(product.category)}"></i>
                    </div>
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-price">
                        <span class="price">UGX ${product.price.toLocaleString()}</span>
                        ${product.oldPrice ? `<span class="old-price">UGX ${product.oldPrice.toLocaleString()}</span>` : ''}
                    </div>
                    <div class="product-rating">
                        ${getRatingStars(product.rating)}
                        <span>(${product.reviews})</span>
                    </div>
                </div>
                <button class="btn btn-outline btn-block add-to-cart" data-id="${product.id}">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </div>
        `).join('');
    }
    
    function loadAllProducts() {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category') || 'all';
        
        let filteredProducts = products;
        if (category !== 'all') {
            filteredProducts = products.filter(p => p.category === category);
        }
        
        productsGrid.innerHTML = filteredProducts.map(product => `
            <div class="product-card">
                ${product.badge ? `<div class="product-badge">${product.badge}</div>` : ''}
                <div class="product-image">
                    <div class="image-placeholder">
                        <i class="fas fa-${getProductIcon(product.category)}"></i>
                    </div>
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-price">
                        <span class="price">UGX ${product.price.toLocaleString()}</span>
                        ${product.oldPrice ? `<span class="old-price">UGX ${product.oldPrice.toLocaleString()}</span>` : ''}
                    </div>
                    <div class="product-rating">
                        ${getRatingStars(product.rating)}
                        <span>(${product.reviews})</span>
                    </div>
                </div>
                <button class="btn btn-outline btn-block add-to-cart" data-id="${product.id}">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </div>
        `).join('');
        
        // Update products count
        const productsCount = document.getElementById('productsCount');
        if (productsCount) {
            productsCount.textContent = filteredProducts.length;
        }
    }
    
    function getProductIcon(category) {
        const icons = {
            routers: 'router',
            switches: 'server',
            'access-points': 'wifi',
            cables: 'network-wired',
            security: 'shield-alt',
            modems: 'broadcast-tower',
            racks: 'server'
        };
        return icons[category] || 'network-wired';
    }
    
    function getRatingStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }
    
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                ...product,
                quantity: 1,
                includeInstallation: true
            });
        }
        
        saveCart();
        updateCartCount();
        updateCartDisplay();
    }
    
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        saveCart();
        updateCartCount();
        updateCartDisplay();
    }
    
    function updateCartItemQuantity(productId, quantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            saveCart();
            updateCartCount();
            updateCartDisplay();
        }
    }
    
    function saveCart() {
        localStorage.setItem('roncomCart', JSON.stringify(cart));
    }
    
    function updateCartCount() {
        if (cartCount) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            
            // Update all cart count elements
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = totalItems;
            });
        }
    }
    
    function updateCartDisplay() {
        if (cartItems) {
            if (cart.length === 0) {
                cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
                document.querySelector('.cart-total .total-price').textContent = 'UGX 0';
                return;
            }
            
            cartItems.innerHTML = cart.map(item => `
                <div class="cart-sidebar-item" data-id="${item.id}">
                    <div class="item-image">
                        <i class="fas fa-${getProductIcon(item.category)}"></i>
                    </div>
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <div class="item-price">UGX ${item.price.toLocaleString()} × ${item.quantity}</div>
                    </div>
                    <button class="remove-item" data-id="${item.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
            
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.querySelector('.cart-total .total-price').textContent = `UGX ${total.toLocaleString()}`;
        }
    }
    
    function openCart() {
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.add('active');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            updateCartDisplay();
        }
    }
    
    function closeCartSidebar() {
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
    
    // Update current year in footer
    const currentYear = new Date().getFullYear();
    const yearElements = document.querySelectorAll('#currentYear');
    yearElements.forEach(element => {
        element.textContent = currentYear;
    });
    
    // Form handling for contact page
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const name = formData.get('name');
            const email = formData.get('email');
            const phone = formData.get('phone');
            const location = formData.get('location');
            const service = formData.get('service');
            const message = formData.get('message');
            
            // Simple validation
            if (!name || !email || !phone || !location || !message) {
                showFormMessage('Please fill in all required fields.', 'error');
                return;
            }
            
            // Show success message
            showFormMessage('Thank you for your message! We will contact you soon.', 'success');
            
            // Reset form
            contactForm.reset();
            
            // Scroll to message
            document.getElementById('formMessage').scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    function showFormMessage(text, type) {
        const formMessage = document.getElementById('formMessage');
        formMessage.textContent = text;
        formMessage.className = 'form-message ' + type;
        formMessage.style.display = 'block';
        
        // Hide message after 5 seconds
        setTimeout(() => {
            formMessage.style.display = 'none';
        }, 5000);
    }
    
    // Add active class to current page in navigation
    const currentPage = window.location.pathname.split('/').pop();
    const navLinksAll = document.querySelectorAll('.nav-links a');
    
    navLinksAll.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href.startsWith('#')) {
                e.preventDefault();
                
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Product filtering
    const applyFiltersBtn = document.getElementById('applyFilters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            // Get selected filters
            const priceRange = document.getElementById('priceSlider').value;
            const selectedBrands = Array.from(document.querySelectorAll('.brand-filters input:checked'))
                .map(input => input.nextElementSibling.textContent);
            
            // Filter products based on selected filters
            const filteredProducts = products.filter(product => {
                // Price filter
                if (product.price > priceRange) return false;
                
                // Brand filter (simplified for demo)
                if (selectedBrands.length > 0) {
                    // This is a simplified brand filter - in reality, products would have brand property
                    return true;
                }
                
                return true;
            });
            
            // Update products display
            productsGrid.innerHTML = filteredProducts.map(product => `
                <div class="product-card">
                    ${product.badge ? `<div class="product-badge">${product.badge}</div>` : ''}
                    <div class="product-image">
                        <div class="image-placeholder">
                            <i class="fas fa-${getProductIcon(product.category)}"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        <div class="product-price">
                            <span class="price">UGX ${product.price.toLocaleString()}</span>
                            ${product.oldPrice ? `<span class="old-price">UGX ${product.oldPrice.toLocaleString()}</span>` : ''}
                        </div>
                        <div class="product-rating">
                            ${getRatingStars(product.rating)}
                            <span>(${product.reviews})</span>
                        </div>
                    </div>
                    <button class="btn btn-outline btn-block add-to-cart" data-id="${product.id}">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            `).join('');
            
            // Update products count
            const productsCount = document.getElementById('productsCount');
            if (productsCount) {
                productsCount.textContent = filteredProducts.length;
            }
        });
    }
    
    // Product sorting
    const sortProducts = document.getElementById('sortProducts');
    if (sortProducts) {
        sortProducts.addEventListener('change', function() {
            const sortValue = this.value;
            let sortedProducts = [...products];
            
            switch(sortValue) {
                case 'price-low':
                    sortedProducts.sort((a, b) => a.price - b.price);
                    break;
                case 'price-high':
                    sortedProducts.sort((a, b) => b.price - a.price);
                    break;
                case 'name':
                    sortedProducts.sort((a, b) => a.name.localeCompare(b.name));
                    break;
                case 'newest':
                    // In reality, products would have a date property
                    sortedProducts.reverse();
                    break;
            }
            
            // Update products display
            productsGrid.innerHTML = sortedProducts.map(product => `
                <div class="product-card">
                    ${product.badge ? `<div class="product-badge">${product.badge}</div>` : ''}
                    <div class="product-image">
                        <div class="image-placeholder">
                            <i class="fas fa-${getProductIcon(product.category)}"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        <div class="product-price">
                            <span class="price">UGX ${product.price.toLocaleString()}</span>
                            ${product.oldPrice ? `<span class="old-price">UGX ${product.oldPrice.toLocaleString()}</span>` : ''}
                        </div>
                        <div class="product-rating">
                            ${getRatingStars(product.rating)}
                            <span>(${product.reviews})</span>
                        </div>
                    </div>
                    <button class="btn btn-outline btn-block add-to-cart" data-id="${product.id}">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            `).join('');
        });
    }
});



// js/checkout.js
document.addEventListener('DOMContentLoaded', function() {
    // Payment tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Place order button
    const placeOrderBtn = document.getElementById('placeOrder');
    const orderModal = document.getElementById('orderModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate form
            const firstName = document.getElementById('firstName');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const address = document.getElementById('address');
            const terms = document.getElementById('terms');
            
            let isValid = true;
            
            // Simple validation
            [firstName, email, phone, address].forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--danger)';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!terms.checked) {
                alert('Please agree to the Terms & Conditions');
                isValid = false;
            }
            
            if (isValid) {
                // Show order confirmation modal
                orderModal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // In a real application, you would submit the order to a server here
                console.log('Order placed successfully');
                
                // Clear cart (in a real app, this would be done after successful payment)
                localStorage.removeItem('roncomCart');
            }
        });
    }
    
    // Close modal functionality
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            orderModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
    
    // Close modal when clicking outside
    orderModal.addEventListener('click', function(e) {
        if (e.target === orderModal) {
            orderModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // City selection
    const citySelect = document.getElementById('city');
    const districtInput = document.getElementById('district');
    
    if (citySelect && districtInput) {
        // Set district based on city selection
        const cityDistricts = {
            kampala: 'Kampala',
            mbale: 'Mbale',
            mbarara: 'Mbarara',
            rukungiri: 'Rukungiri',
            jinja: 'Jinja',
            gulu: 'Gulu',
            fortportal: 'Kabarole'
        };
        
        citySelect.addEventListener('change', function() {
            const district = cityDistricts[this.value];
            if (district && this.value !== 'other') {
                districtInput.value = district;
            } else if (this.value === 'other') {
                districtInput.value = '';
                districtInput.focus();
            }
        });
    }
    
    // Shipping method price update
    const shippingMethods = document.querySelectorAll('input[name="shipping"]');
    const shippingPriceElement = document.querySelector('.summary-row:nth-child(3) span:last-child');
    
    shippingMethods.forEach(method => {
        method.addEventListener('change', function() {
            const shippingInfo = this.parentElement.querySelector('.shipping-price');
            if (shippingInfo) {
                const priceText = shippingInfo.textContent;
                if (priceText === 'FREE') {
                    shippingPriceElement.textContent = 'FREE';
                    shippingPriceElement.className = 'free';
                } else {
                    shippingPriceElement.textContent = priceText;
                    shippingPriceElement.className = '';
                }
                
                // Recalculate total (in a real app, this would update the total)
                updateOrderTotal();
            }
        });
    });
    
    function updateOrderTotal() {
        // This is a simplified version
        // In a real app, you would calculate the total based on cart items, taxes, shipping, etc.
        console.log('Updating order total...');
    }
    
    // Mobile money validation
    const mobileNumberInput = document.getElementById('mobileNumber');
    if (mobileNumberInput) {
        mobileNumberInput.addEventListener('input', function() {
            // Format mobile number
            let value = this.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = '+256' + value.substring(1);
            }
            this.value = value;
        });
    }
});


// js/account.js
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const menuItems = document.querySelectorAll('.menu-item');
    const tabContents = document.querySelectorAll('.tab-content');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get target tab ID
            const targetId = this.getAttribute('href').substring(1);
            
            // Remove active class from all menu items and tabs
            menuItems.forEach(i => i.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Add active class to clicked menu item and corresponding tab
            this.classList.add('active');
            document.getElementById(targetId).classList.add('active');
            
            // Load content for the tab if needed
            if (targetId === 'wishlist') {
                loadWishlist();
            }
        });
    });
    
    // Address modal
    const addAddressBtn = document.getElementById('addAddress');
    const addressModal = document.getElementById('addressModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    if (addAddressBtn) {
        addAddressBtn.addEventListener('click', function() {
            addressModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal functionality
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            addressModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
    
    // Close modal when clicking outside
    addressModal.addEventListener('click', function(e) {
        if (e.target === addressModal) {
            addressModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Address form submission
    const addressForm = document.getElementById('addressForm');
    if (addressForm) {
        addressForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const addressType = formData.get('addressType');
            const addressName = formData.get('addressName');
            const addressPhone = formData.get('addressPhone');
            const addressStreet = formData.get('addressStreet');
            const addressCity = formData.get('addressCity');
            const addressDistrict = formData.get('addressDistrict');
            const addressRegion = formData.get('addressRegion');
            const setAsDefault = formData.get('setAsDefault');
            
            // In a real application, you would save this to a server
            console.log('Address saved:', {
                type: addressType,
                name: addressName,
                phone: addressPhone,
                street: addressStreet,
                city: addressCity,
                district: addressDistrict,
                region: addressRegion,
                default: setAsDefault
            });
            
            // Close modal
            addressModal.classList.remove('active');
            document.body.style.overflow = 'auto';
            
            // Show success message
            alert('Address saved successfully!');
            
            // Reset form
            addressForm.reset();
        });
    }
    
    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const firstName = document.getElementById('settingsFirstName').value;
            const lastName = document.getElementById('settingsLastName').value;
            const email = document.getElementById('settingsEmail').value;
            const phone = document.getElementById('settingsPhone').value;
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate password change
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match!');
                    return;
                }
                
                if (!currentPassword) {
                    alert('Please enter your current password to change it.');
                    return;
                }
            }
            
            // In a real application, you would save this to a server
            console.log('Profile updated:', {
                firstName,
                lastName,
                email,
                phone,
                passwordChanged: !!newPassword
            });
            
            // Show success message
            alert('Profile updated successfully!');
        });
    }
    
    // Cancel changes button
    const cancelChangesBtn = document.getElementById('cancelChanges');
    if (cancelChangesBtn) {
        cancelChangesBtn.addEventListener('click', function() {
            // Reset form to original values (in a real app, you would fetch from server)
            document.getElementById('settingsFirstName').value = 'David';
            document.getElementById('settingsLastName').value = 'Mugisha';
            document.getElementById('settingsEmail').value = 'david@example.com';
            document.getElementById('settingsPhone').value = '+256 772 123 456';
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        });
    }
    
    // Order filtering
    const orderFilter = document.getElementById('orderFilter');
    if (orderFilter) {
        orderFilter.addEventListener('change', function() {
            const filterValue = this.value;
            const orderRows = document.querySelectorAll('.order-row:not(.header)');
            
            orderRows.forEach(row => {
                const statusBadge = row.querySelector('.status-badge');
                const status = statusBadge ? statusBadge.classList[1].replace('status-', '') : '';
                
                if (filterValue === 'all' || status === filterValue) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Load wishlist items
    function loadWishlist() {
        const wishlistProducts = document.getElementById('wishlistProducts');
        if (!wishlistProducts) return;
        
        // Sample wishlist data
        const wishlistItems = [
            {
                id: 1,
                name: "Cisco Catalyst 2960X Switch",
                price: 1850000,
                oldPrice: 2100000,
                description: "24-port managed switch with PoE+ support",
                rating: 4.5,
                reviews: 42,
                badge: "BEST SELLER",
                inStock: true
            },
            {
                id: 2,
                name: "Ubiquiti UniFi 6 Long-Range Access Point",
                price: 950000,
                description: "WiFi 6 access point with extended range",
                rating: 5,
                reviews: 36,
                inStock: true
            },
            {
                id: 3,
                name: "FortiGate 60F Network Security Firewall",
                price: 3500000,
                description: "Next-generation firewall with threat protection",
                rating: 4.5,
                reviews: 28,
                inStock: false
            }
        ];
        
        wishlistProducts.innerHTML = wishlistItems.map(item => `
            <div class="wishlist-item">
                <div class="item-image">
                    <div class="image-placeholder">
                        <i class="fas fa-server"></i>
                    </div>
                    <button class="remove-wishlist" data-id="${item.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="item-details">
                    <h3>${item.name}</h3>
                    <p class="item-description">${item.description}</p>
                    <div class="item-rating">
                        ${getRatingStars(item.rating)}
                        <span>(${item.reviews} reviews)</span>
                    </div>
                    <div class="item-stock ${item.inStock ? '' : 'out-of-stock'}">
                        <i class="fas fa-${item.inStock ? 'check-circle' : 'clock'}"></i>
                        <span>${item.inStock ? 'In Stock' : 'Back in 7 days'}</span>
                    </div>
                </div>
                <div class="item-price">
                    <span class="price">UGX ${item.price.toLocaleString()}</span>
                    ${item.oldPrice ? `<span class="old-price">UGX ${item.oldPrice.toLocaleString()}</span>` : ''}
                    ${item.badge ? `<span class="discount">Save ${Math.round((1 - item.price/item.oldPrice) * 100)}%</span>` : ''}
                </div>
                <div class="item-actions">
                    <button class="btn btn-primary add-to-cart" data-id="${item.id}">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    ${item.inStock ? 
                        `<button class="btn btn-outline move-to-cart" data-id="${item.id}">
                            <i class="fas fa-shopping-cart"></i> Move to Cart
                        </button>` : 
                        `<button class="btn btn-outline notify-me" data-id="${item.id}">
                            <i class="fas fa-bell"></i> Notify When Available
                        </button>`
                    }
                </div>
            </div>
        `).join('');
    }
    
    function getRatingStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }
    
    // Initialize wishlist if on wishlist tab
    const currentTab = window.location.hash.substring(1) || 'dashboard';
    if (currentTab === 'wishlist') {
        loadWishlist();
    }
});


// js/wishlist.js
document.addEventListener('DOMContentLoaded', function() {
    // Wishlist items data
    const wishlistItems = [
        {
            id: 1,
            name: "Cisco Catalyst 2960X Switch",
            price: 1850000,
            oldPrice: 2100000,
            description: "24-port managed switch with PoE+ support",
            rating: 4.5,
            reviews: 42,
            badge: "BEST SELLER",
            inStock: true
        },
        {
            id: 2,
            name: "Ubiquiti UniFi 6 Long-Range Access Point",
            price: 950000,
            description: "WiFi 6 access point with extended range",
            rating: 5,
            reviews: 36,
            inStock: true
        },
        {
            id: 3,
            name: "FortiGate 60F Network Security Firewall",
            price: 3500000,
            description: "Next-generation firewall with threat protection",
            rating: 4.5,
            reviews: 28,
            inStock: false
        }
    ];
    
    // Update wishlist count
    const wishlistCount = document.getElementById('wishlistCount');
    if (wishlistCount) {
        wishlistCount.textContent = wishlistItems.length;
    }
    
    // Remove item from wishlist
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-wishlist') || 
            e.target.closest('.remove-wishlist')) {
            const button = e.target.classList.contains('remove-wishlist') ? 
                e.target : e.target.closest('.remove-wishlist');
            const itemId = parseInt(button.dataset.id);
            
            // Remove from wishlist
            removeFromWishlist(itemId);
            
            // Update UI
            updateWishlistDisplay();
        }
        
        // Add to cart from wishlist
        if (e.target.classList.contains('add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            const button = e.target.classList.contains('add-to-cart') ? 
                e.target : e.target.closest('.add-to-cart');
            const itemId = parseInt(button.dataset.id);
            
            const item = wishlistItems.find(i => i.id === itemId);
            if (item) {
                addToCart(item);
                alert(`${item.name} added to cart!`);
            }
        }
        
        // Move to cart from wishlist
        if (e.target.classList.contains('move-to-cart') || 
            e.target.closest('.move-to-cart')) {
            const button = e.target.classList.contains('move-to-cart') ? 
                e.target : e.target.closest('.move-to-cart');
            const itemId = parseInt(button.dataset.id);
            
            const item = wishlistItems.find(i => i.id === itemId);
            if (item) {
                addToCart(item);
                removeFromWishlist(itemId);
                updateWishlistDisplay();
                alert(`${item.name} moved to cart!`);
            }
        }
        
        // Notify when available
        if (e.target.classList.contains('notify-me') || 
            e.target.closest('.notify-me')) {
            const button = e.target.classList.contains('notify-me') ? 
                e.target : e.target.closest('.notify-me');
            const itemId = parseInt(button.dataset.id);
            
            const item = wishlistItems.find(i => i.id === itemId);
            if (item) {
                const email = prompt('Please enter your email to get notified when this item is back in stock:');
                if (email) {
                    alert(`You'll be notified at ${email} when ${item.name} is back in stock.`);
                }
            }
        }
        
        // Add to wishlist from product cards
        if (e.target.classList.contains('btn-wishlist') || 
            e.target.closest('.btn-wishlist')) {
            const button = e.target.classList.contains('btn-wishlist') ? 
                e.target : e.target.closest('.btn-wishlist');
            const productCard = button.closest('.product-card');
            const productName = productCard.querySelector('h3').textContent;
            
            // Toggle heart icon
            const icon = button.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                alert(`${productName} added to wishlist!`);
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                alert(`${productName} removed from wishlist!`);
            }
        }
    });
    
    // Clear wishlist
    const clearWishlistBtn = document.getElementById('clearWishlist');
    if (clearWishlistBtn) {
        clearWishlistBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear your entire wishlist?')) {
                // Clear wishlist
                wishlistItems.length = 0;
                updateWishlistDisplay();
                alert('Wishlist cleared!');
            }
        });
    }
    
    // Share wishlist
    const shareWishlistBtn = document.getElementById('shareWishlist');
    const shareModal = document.getElementById('shareModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    if (shareWishlistBtn) {
        shareWishlistBtn.addEventListener('click', function() {
            shareModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal functionality
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            shareModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
    
    // Close modal when clicking outside
    shareModal.addEventListener('click', function(e) {
        if (e.target === shareModal) {
            shareModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Copy link functionality
    const copyButtons = document.querySelectorAll('.btn-copy');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            
            navigator.clipboard.writeText(input.value)
                .then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                });
        });
    });
    
    // Share buttons
    const shareButtons = document.querySelectorAll('.share-btn');
    shareButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.classList[1]; // whatsapp, facebook, etc.
            const wishlistLink = document.getElementById('wishlistLink').value;
            const message = `Check out my networking equipment wishlist on Roncom: ${wishlistLink}`;
            
            let shareUrl = '';
            
            switch(platform) {
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(wishlistLink)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}`;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=My Roncom Wishlist&body=${encodeURIComponent(message)}`;
                    break;
            }
            
            window.open(shareUrl, '_blank');
        });
    });
    
    // Functions
    function removeFromWishlist(itemId) {
        const index = wishlistItems.findIndex(item => item.id === itemId);
        if (index > -1) {
            wishlistItems.splice(index, 1);
        }
    }
    
    function addToCart(product) {
        // This would add to cart in a real application
        console.log('Added to cart:', product);
    }
    
    function updateWishlistDisplay() {
        const wishlistItemsContainer = document.getElementById('wishlistItems');
        const emptyWishlist = document.getElementById('emptyWishlist');
        
        if (wishlistItems.length === 0) {
            wishlistItemsContainer.style.display = 'none';
            emptyWishlist.style.display = 'block';
            
            if (wishlistCount) {
                wishlistCount.textContent = '0';
            }
        } else {
            wishlistItemsContainer.style.display = 'grid';
            emptyWishlist.style.display = 'none';
            
            // Update wishlist items display
            wishlistItemsContainer.innerHTML = wishlistItems.map(item => `
                <div class="wishlist-item">
                    <div class="item-image">
                        <div class="image-placeholder">
                            <i class="fas fa-server"></i>
                        </div>
                        <button class="remove-wishlist" data-id="${item.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="item-details">
                        <h3>${item.name}</h3>
                        <p class="item-description">${item.description}</p>
                        <div class="item-rating">
                            ${getRatingStars(item.rating)}
                            <span>(${item.reviews} reviews)</span>
                        </div>
                        <div class="item-stock ${item.inStock ? '' : 'out-of-stock'}">
                            <i class="fas fa-${item.inStock ? 'check-circle' : 'clock'}"></i>
                            <span>${item.inStock ? 'In Stock' : 'Back in 7 days'}</span>
                        </div>
                    </div>
                    <div class="item-price">
                        <span class="price">UGX ${item.price.toLocaleString()}</span>
                        ${item.oldPrice ? `<span class="old-price">UGX ${item.oldPrice.toLocaleString()}</span>` : ''}
                        ${item.badge ? `<span class="discount">Save ${Math.round((1 - item.price/item.oldPrice) * 100)}%</span>` : ''}
                    </div>
                    <div class="item-actions">
                        <button class="btn btn-primary add-to-cart" data-id="${item.id}">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                        ${item.inStock ? 
                            `<button class="btn btn-outline move-to-cart" data-id="${item.id}">
                                <i class="fas fa-shopping-cart"></i> Move to Cart
                            </button>` : 
                            `<button class="btn btn-outline notify-me" data-id="${item.id}">
                                <i class="fas fa-bell"></i> Notify When Available
                            </button>`
                        }
                    </div>
                </div>
            `).join('');
            
            if (wishlistCount) {
                wishlistCount.textContent = wishlistItems.length;
            }
        }
    }
    
    function getRatingStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }
    
    // Initialize wishlist display
    updateWishlistDisplay();
});


// js/services.js
document.addEventListener('DOMContentLoaded', function() {
    // Service data
    const servicePackages = {
        basic: {
            name: "Basic Installation",
            price: 50000,
            duration: "1-2 hours",
            includes: "Single device installation, basic configuration, connectivity testing, 30-day support"
        },
        professional: {
            name: "Professional Setup",
            price: 150000,
            duration: "3-4 hours",
            includes: "Up to 3 devices, complete configuration, cable management, network optimization, 90-day support"
        },
        enterprise: {
            name: "Enterprise Solution",
            price: 500000,
            duration: "1-2 days",
            includes: "Complete network setup, site survey, advanced configuration, security setup, 6-month support"
        }
    };
    
    // Service categories
    const serviceCategories = {
        wifi: {
            name: "WiFi Network Setup",
            price: "From UGX 100,000",
            duration: "2-4 hours"
        },
        configuration: {
            name: "Network Configuration",
            price: "From UGX 80,000",
            duration: "1-3 hours"
        },
        troubleshooting: {
            name: "Troubleshooting & Repair",
            price: "From UGX 50,000/hour",
            duration: "Varies"
        },
        cabling: {
            name: "Cabling & Infrastructure",
            price: "From UGX 150,000",
            duration: "3-6 hours"
        },
        consultation: {
            name: "Consultation & Planning",
            price: "From UGX 100,000",
            duration: "2-3 hours"
        },
        security: {
            name: "Security & Maintenance",
            price: "From UGX 200,000",
            duration: "Varies"
        }
    };
    
    // Add service to cart
    const addToCartButtons = document.querySelectorAll('.add-to-cart-service');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const serviceType = this.dataset.service;
            const service = servicePackages[serviceType];
            
            if (service) {
                // In a real application, you would add this to the cart
                alert(`${service.name} added to cart for UGX ${service.price.toLocaleString()}`);
                
                // You could also redirect to cart page or update cart count
                // window.location.href = 'cart.html';
            }
        });
    });
    
    // Book service buttons
    const bookServiceButtons = document.querySelectorAll('.btn-book-service, .service-link');
    const serviceModal = document.getElementById('serviceModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    bookServiceButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            let serviceType = this.dataset.service;
            let serviceName = '';
            let servicePrice = '';
            let serviceDuration = '';
            let serviceIncludes = '';
            
            // Check if it's a package or category
            if (servicePackages[serviceType]) {
                // It's a package
                const service = servicePackages[serviceType];
                serviceName = service.name;
                servicePrice = `UGX ${service.price.toLocaleString()}`;
                serviceDuration = service.duration;
                serviceIncludes = service.includes;
            } else if (serviceCategories[serviceType]) {
                // It's a category
                const service = serviceCategories[serviceType];
                serviceName = service.name;
                servicePrice = service.price;
                serviceDuration = service.duration;
                serviceIncludes = "Customized based on your requirements";
            } else {
                // Default for other links
                serviceName = this.textContent.replace('Book ', '').replace(' Service', '');
                servicePrice = "Custom quote";
                serviceDuration = "To be determined";
                serviceIncludes = "Tailored to your needs";
            }
            
            // Update modal content
            document.getElementById('modalServiceName').textContent = serviceName;
            document.getElementById('quoteServiceType').textContent = serviceName;
            document.getElementById('quotePrice').textContent = servicePrice;
            document.getElementById('quoteDuration').textContent = serviceDuration;
            document.getElementById('quoteIncludes').textContent = serviceIncludes;
            
            // Show modal
            serviceModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Proceed to booking from modal
    const proceedToBookingBtn = document.getElementById('proceedToBooking');
    if (proceedToBookingBtn) {
        proceedToBookingBtn.addEventListener('click', function() {
            // Close service modal
            serviceModal.classList.remove('active');
            
            // Scroll to booking form
            const bookingForm = document.getElementById('bookService');
            if (bookingForm) {
                bookingForm.scrollIntoView({ behavior: 'smooth' });
                
                // Set service type in form
                const serviceTypeSelect = document.getElementById('serviceType');
                const serviceName = document.getElementById('modalServiceName').textContent;
                
                // Find option that matches the service name
                for (let i = 0; i < serviceTypeSelect.options.length; i++) {
                    if (serviceTypeSelect.options[i].text === serviceName) {
                        serviceTypeSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Focus on the form
                serviceTypeSelect.focus();
            }
        });
    }
    
    // Close modal functionality
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            serviceModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
    
    // Close modal when clicking outside
    serviceModal.addEventListener('click', function(e) {
        if (e.target === serviceModal) {
            serviceModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Service booking form
    const bookingForm = document.getElementById('serviceBookingForm');
    const bookingSuccessModal = document.getElementById('bookingSuccessModal');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const serviceType = formData.get('serviceType');
            const location = formData.get('serviceLocation');
            const date = formData.get('preferredDate');
            const time = formData.get('preferredTime');
            const description = formData.get('serviceDescription');
            const name = formData.get('customerName');
            const email = formData.get('customerEmail');
            const phone = formData.get('customerPhone');
            
            // Validate date (must be in the future)
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Please select a future date for the service.');
                return;
            }
            
            // In a real application, you would send this data to a server
            console.log('Service booking submitted:', {
                serviceType,
                location,
                date,
                time,
                description,
                name,
                email,
                phone
            });
            
            // Update success modal
            document.getElementById('bookedServiceType').textContent = 
                document.querySelector(`#serviceType option[value="${serviceType}"]`).text;
            
            // Show success modal
            bookingSuccessModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Reset form
            bookingForm.reset();
            
            // You could also send an email confirmation here
        });
    }
    
    // Clear booking form
    const clearBookingFormBtn = document.getElementById('clearBookingForm');
    if (clearBookingFormBtn) {
        clearBookingFormBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the form?')) {
                bookingForm.reset();
            }
        });
    }
    
    // Set minimum date to today for date input
    const dateInput = document.getElementById('preferredDate');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }
    
    // Close success modal
    bookingSuccessModal.addEventListener('click', function(e) {
        if (e.target === bookingSuccessModal || e.target.classList.contains('close-modal')) {
            bookingSuccessModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Smooth scroll to booking form
    const bookServiceLinks = document.querySelectorAll('a[href="#bookService"]');
    bookServiceLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById('bookService');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});