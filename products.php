<?php
// products.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$pageTitle = "Products - Roncom Networking Store";
$activePage = "products";

$db = new Database();
$conn = $db->getConnection();

// Get filters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 5000000;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";
$params = [];
$types = "";

if ($category) {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
    $types .= "s";
}

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

$sql .= " AND p.price BETWEEN ? AND ?";
$params[] = $minPrice;
$params[] = $maxPrice;
$types .= "dd";

// Sorting
switch ($sort) {
    case 'price-low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price-high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'newest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    default:
        $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";
        break;
}

// Get total count for pagination
$countSql = str_replace("SELECT p.*, c.name as category_name, c.slug as category_slug", 
                       "SELECT COUNT(*) as total", $sql);
$stmt = $conn->prepare($countSql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalRows = $result->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Add pagination to main query
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Execute main query
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get categories for sidebar
$categories = [];
$catSql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$catResult = $conn->query($catSql);
if ($catResult) {
    $categories = $catResult->fetch_all(MYSQLI_ASSOC);
}

// Get current category name if selected
$currentCategoryName = '';
if ($category) {
    foreach ($categories as $cat) {
        if ($cat['slug'] == $category) {
            $currentCategoryName = $cat['name'];
            break;
        }
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
</head>
<body>
    <!-- Header (same as index.php) -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Networking Products</h1>
            <p><?php echo $currentCategoryName ? "Category: $currentCategoryName" : "Browse all networking equipment"; ?></p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="section products-page">
        <div class="container">
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="products-sidebar">
                    <form method="GET" action="products.php">
                        <div class="sidebar-widget">
                            <h3>Categories</h3>
                            <ul class="category-list">
                                <li><a href="products.php" class="<?php echo !$category ? 'active' : ''; ?>">All Products</a></li>
                                <?php foreach ($categories as $cat): ?>
                                    <li>
                                        <a href="products.php?category=<?php echo $cat['slug']; ?>" 
                                           class="<?php echo $category == $cat['slug'] ? 'active' : ''; ?>">
                                            <?php echo $cat['name']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="sidebar-widget">
                            <h3>Price Range</h3>
                            <div class="price-filter">
                                <input type="range" min="0" max="5000000" value="<?php echo $maxPrice; ?>" 
                                       class="price-slider" id="priceSlider" name="max_price">
                                <div class="price-values">
                                    <span>UGX 0</span>
                                    <span>UGX <span id="priceValue"><?php echo number_format($maxPrice); ?></span></span>
                                </div>
                                <input type="hidden" name="min_price" value="0">
                            </div>
                        </div>
                        
                        <div class="sidebar-widget">
                            <h3>Sort By</h3>
                            <select name="sort" class="sort-select">
                                <option value="featured" <?php echo $sort == 'featured' ? 'selected' : ''; ?>>Featured</option>
                                <option value="price-low" <?php echo $sort == 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price-high" <?php echo $sort == 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            </select>
                        </div>
                        
                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary btn-full">Apply Filters</button>
                    </form>
                </aside>
                
                <!-- Products Grid -->
                <main class="products-main">
                    <div class="products-header">
                        <div class="products-sorting">
                            <span>Showing <?php echo count($products); ?> of <?php echo $totalRows; ?> products</span>
                        </div>
                        <div class="products-count">
                            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($products)): ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <?php if ($product['badge']): ?>
                                        <div class="product-badge"><?php echo strtoupper($product['badge']); ?></div>
                                    <?php endif; ?>
                                    <div class="product-image">
                                        <?php if ($product['image_url']): ?>
                                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                        <?php else: ?>
                                            <div class="image-placeholder">
                                                <i class="fas fa-network-wired"></i>
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
                                    <div class="product-actions">
                                        <form method="POST" action="cart.php?action=add" class="add-to-cart-form">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-outline btn-block">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        </form>
                                        <form method="POST" action="wishlist.php?action=add" class="wishlist-form">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn-wishlist">
                                                <i class="far fa-heart"></i> Wishlist
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="products.php?<?php echo buildQueryString(['page' => $page - 1]); ?>" class="page-link">
                                        <i class="fas fa-arrow-left"></i> Prev
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                        <a href="products.php?<?php echo buildQueryString(['page' => $i]); ?>" 
                                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                        <span class="page-dots">...</span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="products.php?<?php echo buildQueryString(['page' => $page + 1]); ?>" class="page-link next">
                                        Next <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="no-products">
                            <div class="empty-state">
                                <i class="fas fa-search fa-3x"></i>
                                <h3>No products found</h3>
                                <p>Try adjusting your search or filter criteria</p>
                                <a href="products.php" class="btn btn-primary">View All Products</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    // Update price display
    document.getElementById('priceSlider').addEventListener('input', function() {
        document.getElementById('priceValue').textContent = 
            new Intl.NumberFormat().format(this.value);
    });
    
    // Auto-submit sort select
    document.querySelector('.sort-select').addEventListener('change', function() {
        this.form.submit();
    });
    </script>
</body>
</html>

<?php
// Helper function to build query string
function buildQueryString($updates = []) {
    $params = $_GET;
    foreach ($updates as $key => $value) {
        $params[$key] = $value;
    }
    return http_build_query($params);
}
?>