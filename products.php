<?php
$pageTitle = 'Products';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Get categories for navigation
$categories_query = "SELECT * FROM categories WHERE status = 'active' LIMIT 8";
$categories_nav_result = $conn->query($categories_query);

// Get filter parameters
$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$rating = $_GET['rating'] ?? '';
$brand = $_GET['brand'] ?? '';
$stock = $_GET['stock'] ?? '';

// Build query
$query = "SELECT * FROM products WHERE status = 'active'";
$params = [];
$types = '';

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($subcategory)) {
    $query .= " AND subcategory = ?";
    $params[] = $subcategory;
    $types .= 's';
}

if (!empty($price_min)) {
    $query .= " AND price >= ?";
    $params[] = $price_min;
    $types .= 'd';
}

if (!empty($price_max)) {
    $query .= " AND price <= ?";
    $params[] = $price_max;
    $types .= 'd';
}

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR brand LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if (!empty($rating)) {
    $query .= " AND rating >= ?";
    $params[] = $rating;
    $types .= 'd';
}

if (!empty($brand)) {
    $query .= " AND brand = ?";
    $params[] = $brand;
    $types .= 's';
}

if (!empty($stock)) {
    if ($stock == 'in_stock') {
        $query .= " AND stock > 0";
    } elseif ($stock == 'out_of_stock') {
        $query .= " AND stock = 0";
    }
}

// Sort
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY price DESC";
        break;
    case 'popular':
        $query .= " ORDER BY id DESC";
        break;
    case 'rating':
        $query .= " ORDER BY rating DESC";
        break;
    default:
        $query .= " ORDER BY created_at DESC";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories from categories table
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories_result = $conn->query($categories_query);

// Reset the navigation categories result pointer
$categories_nav_result->data_seek(0);

// Get distinct brands
$brands_query = "SELECT DISTINCT brand FROM products WHERE status = 'active' AND brand IS NOT NULL AND brand != '' ORDER BY brand";
$brands_result = $conn->query($brands_query);

// Get distinct subcategories based on selected category
$subcategories = [];
if (!empty($category)) {
    $subcat_query = "SELECT DISTINCT subcategory FROM products WHERE status = 'active' AND category = ? AND subcategory IS NOT NULL AND subcategory != ''";
    $subcat_stmt = $conn->prepare($subcat_query);
    $subcat_stmt->bind_param('s', $category);
    $subcat_stmt->execute();
    $subcat_result = $subcat_stmt->get_result();
    while ($row = $subcat_result->fetch_assoc()) {
        $subcategories[] = $row['subcategory'];
    }
}
?>

<!-- Amazon-style Header -->
<header style="background-color: #232f3e; padding: 10px 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-2">
                <a href="index.php" class="text-white text-decoration-none fw-bold fs-4">
                    <?php echo SITE_NAME; ?>
                </a>
            </div>
            <div class="col-8">
                <form action="products.php" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 4px 0 0 4px;">
                    <button type="submit" class="btn" style="background-color: #ff9900; border-radius: 0 4px 4px 0; border: none;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-2 text-end">
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="text-white text-decoration-none me-3"><i class="fas fa-user"></i></a>
                <?php else: ?>
                    <a href="login.php" class="text-white text-decoration-none me-3"><i class="fas fa-user"></i></a>
                <?php endif; ?>
                <a href="cart.php" class="text-white text-decoration-none position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <button class="btn btn-outline-light btn-sm ms-2" onclick="toggleTheme()">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Category Navigation Bar -->
<nav style="background-color: #37475a; padding: 8px 0;">
    <div class="container">
        <div class="d-flex flex-wrap gap-3 justify-content-end">
            <a href="products.php" class="text-white text-decoration-none small"><i class="fas fa-bars me-1"></i>All</a>
            <?php while ($cat = $categories_nav_result->fetch_assoc()): ?>
                <a href="products.php?category=<?php echo urlencode($cat['name']); ?>" class="text-white text-decoration-none small">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</nav>

<div class="container py-4 pb-5">
    <div class="row">
        <!-- Filters Sidebar (Desktop) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="products.php">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select" id="categorySelect" onchange="loadSubcategories()">
                                <option value="">All Categories</option>
                                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category == $cat['name'] ? 'selected' : ''; ?> data-icon="<?php echo htmlspecialchars($cat['icon']); ?>">
                                        <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- Subcategory -->
                        <div class="mb-3" id="subcategoryContainer" style="display: <?php echo !empty($category) ? 'block' : 'none'; ?>;">
                            <label class="form-label fw-bold">Subcategory</label>
                            <select name="subcategory" class="form-select">
                                <option value="">All Subcategories</option>
                                <?php foreach ($subcategories as $subcat): ?>
                                    <option value="<?php echo htmlspecialchars($subcat); ?>" <?php echo $subcategory == $subcat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subcat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Price Range</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" name="price_min" class="form-control" placeholder="Min" value="<?php echo htmlspecialchars($price_min); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="price_max" class="form-control" placeholder="Max" value="<?php echo htmlspecialchars($price_max); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Brand -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Brand</label>
                            <select name="brand" class="form-select">
                                <option value="">All Brands</option>
                                <?php while ($brand_row = $brands_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($brand_row['brand']); ?>" <?php echo $brand == $brand_row['brand'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand_row['brand']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- Rating -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Customer Rating</label>
                            <select name="rating" class="form-select">
                                <option value="">All Ratings</option>
                                <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4★ & above</option>
                                <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3★ & above</option>
                                <option value="2" <?php echo $rating == '2' ? 'selected' : ''; ?>>2★ & above</option>
                                <option value="1" <?php echo $rating == '1' ? 'selected' : ''; ?>>1★ & above</option>
                            </select>
                        </div>
                        
                        <!-- Stock -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Availability</label>
                            <select name="stock" class="form-select">
                                <option value="">All</option>
                                <option value="in_stock" <?php echo $stock == 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                                <option value="out_of_stock" <?php echo $stock == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                            </select>
                        </div>
                        
                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sort By</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Customer Rating</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Mobile Filter Button -->
            <div class="d-lg-none mb-3">
                <button class="btn btn-primary w-100" type="button" data-mdb-toggle="collapse" data-mdb-target="#mobileFilters">
                    <i class="fas fa-filter me-2"></i>Filters
                </button>
            </div>
            
            <!-- Mobile Filters -->
            <div class="collapse d-lg-none mb-3" id="mobileFilters">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="products.php">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php 
                                    $categories_result->data_seek(0);
                                    while ($cat = $categories_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category == $cat['name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3" style="display: <?php echo !empty($category) ? 'block' : 'none'; ?>;">
                                <label class="form-label fw-bold">Subcategory</label>
                                <select name="subcategory" class="form-select">
                                    <option value="">All Subcategories</option>
                                    <?php foreach ($subcategories as $subcat): ?>
                                        <option value="<?php echo htmlspecialchars($subcat); ?>" <?php echo $subcategory == $subcat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subcat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Price Range</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" name="price_min" class="form-control" placeholder="Min" value="<?php echo htmlspecialchars($price_min); ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="price_max" class="form-control" placeholder="Max" value="<?php echo htmlspecialchars($price_max); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Brand</label>
                                <select name="brand" class="form-select">
                                    <option value="">All Brands</option>
                                    <?php 
                                    $brands_result->data_seek(0);
                                    while ($brand_row = $brands_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($brand_row['brand']); ?>" <?php echo $brand == $brand_row['brand'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand_row['brand']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rating</label>
                                <select name="rating" class="form-select">
                                    <option value="">All Ratings</option>
                                    <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4★ & above</option>
                                    <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3★ & above</option>
                                    <option value="2" <?php echo $rating == '2' ? 'selected' : ''; ?>>2★ & above</option>
                                    <option value="1" <?php echo $rating == '1' ? 'selected' : ''; ?>>1★ & above</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Availability</label>
                                <select name="stock" class="form-select">
                                    <option value="">All</option>
                                    <option value="in_stock" <?php echo $stock == 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="out_of_stock" <?php echo $stock == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Sort By</label>
                                <select name="sort" class="form-select">
                                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Customer Rating</option>
                                    <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Results Count -->
            <p class="text-muted mb-3"><?php echo $result->num_rows; ?> products found</p>
            
            <!-- Products Grid -->
            <div class="row g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <?php 
                        $screenshots = json_decode($product['screenshots'], true);
                        $first_image = !empty($screenshots) ? $screenshots[0] : 'https://via.placeholder.com/400x300';
                        $has_discount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
                        $discount_percent = $has_discount ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
                        $display_price = $has_discount ? $product['discount_price'] : $product['price'];
                        $is_in_stock = $product['stock'] > 0;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 product-card shadow-sm position-relative">
                                <!-- Badge -->
                                <?php if ($product['badge'] != 'none'): ?>
                                    <?php 
                                    $badge_class = 'bg-success';
                                    if ($product['badge'] == 'new_arrival') $badge_class = 'bg-primary';
                                    if ($product['badge'] == 'discount') $badge_class = 'bg-danger';
                                    if ($product['badge'] == 'trending') $badge_class = 'bg-warning';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> position-absolute top-0 start-0 m-2"><?php echo ucwords(str_replace('_', ' ', $product['badge'])); ?></span>
                                <?php endif; ?>
                                
                                <!-- Discount Badge -->
                                <?php if ($has_discount): ?>
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2"><?php echo $discount_percent; ?>% OFF</span>
                                <?php endif; ?>
                                
                                <img src="<?php echo $first_image; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <span class="badge bg-light text-dark mb-2"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <?php if (!empty($product['brand'])): ?>
                                        <span class="badge bg-info text-dark mb-2 ms-1"><?php echo htmlspecialchars($product['brand']); ?></span>
                                    <?php endif; ?>
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    
                                    <!-- Rating -->
                                    <?php if ($product['rating_count'] > 0): ?>
                                        <div class="mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= round($product['rating']) ? 'text-warning' : 'text-muted'; ?> small"></i>
                                            <?php endfor; ?>
                                            <span class="text-muted small"><?php echo $product['rating']; ?> (<?php echo $product['rating_count']; ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="card-text text-muted small"><?php echo substr(htmlspecialchars($product['description']), 0, 80); ?>...</p>
                                    
                                    <!-- Price -->
                                    <div class="mb-2">
                                        <?php if ($has_discount): ?>
                                            <span class="h5 mb-0 text-success"><?php echo CURRENCY_SYMBOL . number_format($display_price, 2); ?></span>
                                            <span class="text-muted text-decoration-line-through small ms-2"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="h5 mb-0 text-primary"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Stock Status -->
                                    <div class="mb-2">
                                        <?php if ($is_in_stock): ?>
                                            <span class="badge bg-success">In Stock (<?php echo $product['stock']; ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                            <?php if ($is_in_stock): ?>
                                                <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-cart-plus"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isLoggedIn()): ?>
                                            <a href="wishlist.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-outline-danger btn-sm">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <h4>No products found</h4>
                            <p>Try adjusting your filters or search terms.</p>
                            <a href="products.php" class="btn btn-primary">View All Products</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-bottom d-md-none pb-4">
    <div class="container-fluid justify-content-around">
        <a class="nav-link" href="index.php"><i class="fas fa-home"></i><br><small>Home</small></a>
        <a class="nav-link active" href="products.php"><i class="fas fa-box"></i><br><small>Products</small></a>
        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i><br><small>Cart</small></a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
    </div>
</nav>

<?php require_once 'includes/footer.php'; ?>
