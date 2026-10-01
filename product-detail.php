<?php
$pageTitle = 'Product Details';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Get categories for navigation
$categories_query = "SELECT * FROM categories WHERE status = 'active' LIMIT 8";
$categories_nav_result = $conn->query($categories_query);

$product_id = $_GET['id'] ?? 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();
$screenshots = json_decode($product['screenshots'], true);
$features = json_decode($product['features'], true);
$specifications = json_decode($product['specifications'], true);
$has_discount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
$display_price = $has_discount ? $product['discount_price'] : $product['price'];
$discount_percent = $has_discount ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
$is_in_stock = $product['stock'] > 0;

// Get product reviews
$reviews_query = "SELECT pr.*, u.name as user_name FROM product_reviews pr JOIN users u ON pr.user_id = u.id WHERE pr.product_id = ? ORDER BY pr.created_at DESC LIMIT 10";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Check if product is in user's wishlist
$is_in_wishlist = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $wishlist_check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wishlist_check->bind_param("ii", $user_id, $product_id);
    $wishlist_check->execute();
    $wishlist_result = $wishlist_check->get_result();
    $is_in_wishlist = $wishlist_result->num_rows > 0;
}

// Get related products
$related_query = "SELECT * FROM products WHERE category = ? AND id != ? AND status = 'active' LIMIT 4";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("si", $product['category'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
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
                    <input type="text" name="search" class="form-control" placeholder="Search products..." style="border-radius: 4px 0 0 4px;">
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
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['title']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="carousel slide" data-mdb-ride="carousel" id="productCarousel">
                    <div class="carousel-inner">
                        <?php if (!empty($screenshots)): ?>
                            <?php foreach ($screenshots as $index => $screenshot): ?>
                                <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo $screenshot; ?>" class="d-block w-100" alt="Product Screenshot" style="height: 400px; object-fit: cover;">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active">
                                <img src="https://via.placeholder.com/600x400" class="d-block w-100" alt="Product Image" style="height: 400px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($screenshots) && count($screenshots) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-mdb-target="#productCarousel" data-mdb-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-mdb-target="#productCarousel" data-mdb-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <!-- Badges -->
                    <div class="mb-2">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($product['category']); ?></span>
                        <?php if (!empty($product['brand'])): ?>
                            <span class="badge bg-info text-dark ms-1"><?php echo htmlspecialchars($product['brand']); ?></span>
                        <?php endif; ?>
                        <?php if ($product['badge'] != 'none'): ?>
                            <?php 
                            $badge_class = 'bg-success';
                            if ($product['badge'] == 'new_arrival') $badge_class = 'bg-primary';
                            if ($product['badge'] == 'discount') $badge_class = 'bg-danger';
                            if ($product['badge'] == 'trending') $badge_class = 'bg-warning';
                            ?>
                            <span class="badge <?php echo $badge_class; ?> ms-1"><?php echo ucwords(str_replace('_', ' ', $product['badge'])); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="h2 fw-bold mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>
                    
                    <!-- Rating -->
                    <?php if ($product['rating_count'] > 0): ?>
                        <div class="mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($product['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                            <span class="ms-2"><?php echo $product['rating']; ?> (<?php echo $product['rating_count']; ?> reviews)</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <?php if ($has_discount): ?>
                            <span class="h3 text-success"><?php echo CURRENCY_SYMBOL . number_format($display_price, 2); ?></span>
                            <span class="text-muted text-decoration-line-through h5 ms-2"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                            <span class="badge bg-danger ms-2"><?php echo $discount_percent; ?>% OFF</span>
                        <?php else: ?>
                            <span class="h3 text-primary"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mb-4">
                        <?php if ($is_in_stock): ?>
                            <span class="badge bg-success">In Stock (<?php echo $product['stock']; ?> available)</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <!-- Features -->
                    <?php if (!empty($features) && is_array($features)): ?>
                        <div class="mb-4">
                            <h5>Features</h5>
                            <ul class="list-unstyled">
                                <?php foreach ($features as $feature): ?>
                                    <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <h5>Features</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Instant Download</li>
                                <li><i class="fas fa-check text-success me-2"></i>Lifetime Access</li>
                                <li><i class="fas fa-check text-success me-2"></i>Free Updates</li>
                                <li><i class="fas fa-check text-success me-2"></i>Premium Support</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Specifications -->
                    <?php if (!empty($specifications) && is_array($specifications)): ?>
                        <div class="mb-4">
                            <h5>Specifications</h5>
                            <table class="table table-sm">
                                <?php foreach ($specifications as $key => $value): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($key); ?>:</td>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <?php if ($is_in_stock): ?>
                            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </a>
                            <a href="cart.php?action=buy_now&id=<?php echo $product['id']; ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-bolt me-2"></i>Buy Now
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="fas fa-times me-2"></i>Out of Stock
                            </button>
                        <?php endif; ?>
                        <?php if (isLoggedIn()): ?>
                            <?php if ($is_in_wishlist): ?>
                                <a href="wishlist.php?action=remove&id=<?php echo $product['id']; ?>" class="btn btn-outline-danger">
                                    <i class="fas fa-heart me-2"></i>Remove from Wishlist
                                </a>
                            <?php else: ?>
                                <a href="wishlist.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-outline-danger">
                                    <i class="far fa-heart me-2"></i>Add to Wishlist
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4 text-muted small">
                        <p><i class="fas fa-shield-alt me-2"></i>Secure Payment</p>
                        <p><i class="fas fa-download me-2"></i>Instant Delivery</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="mt-5">
        <h3 class="fw-bold mb-4">Customer Reviews</h3>
        <?php if ($reviews_result->num_rows > 0): ?>
            <div class="row">
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="col-12 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p class="mb-0">No reviews yet. Be the first to review this product!</p>
            </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn()): ?>
            <div class="mt-4">
                <h5 class="mb-3">Write a Review</h5>
                <form method="POST" action="submit-review.php">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select" required>
                            <option value="">Select Rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Review</label>
                        <textarea name="review" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <a href="login.php" class="btn btn-outline-primary">Login to write a review</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Related Products -->
    <?php if ($related_result->num_rows > 0): ?>
        <div class="mt-5">
            <h3 class="fw-bold mb-4">Related Products</h3>
            <div class="row g-4">
                <?php while ($related = $related_result->fetch_assoc()): ?>
                    <?php 
                    $related_screenshots = json_decode($related['screenshots'], true);
                    $related_image = !empty($related_screenshots) ? $related_screenshots[0] : 'https://via.placeholder.com/400x300';
                    $related_has_discount = !empty($related['discount_price']) && $related['discount_price'] < $related['price'];
                    $related_display_price = $related_has_discount ? $related['discount_price'] : $related['price'];
                    ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 product-card shadow-sm">
                            <img src="<?php echo $related_image; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related['title']); ?>" style="height: 150px; object-fit: cover;">
                            <div class="card-body">
                                <span class="badge bg-light text-dark mb-2 small"><?php echo htmlspecialchars($related['category']); ?></span>
                                <h6 class="card-title"><?php echo htmlspecialchars($related['title']); ?></h6>
                                <?php if ($related_has_discount): ?>
                                    <span class="text-success fw-bold"><?php echo CURRENCY_SYMBOL . number_format($related_display_price, 2); ?></span>
                                    <span class="text-muted text-decoration-line-through small ms-1"><?php echo CURRENCY_SYMBOL . number_format($related['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-primary fw-bold"><?php echo CURRENCY_SYMBOL . number_format($related['price'], 2); ?></span>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-sm btn-outline-primary w-100">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-bottom d-md-none pb-4">
    <div class="container-fluid justify-content-around">
        <a class="nav-link" href="index.php"><i class="fas fa-home"></i><br><small>Home</small></a>
        <a class="nav-link" href="products.php"><i class="fas fa-box"></i><br><small>Products</small></a>
        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i><br><small>Cart</small></a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
    </div>
</nav>

<?php require_once 'includes/footer.php'; ?>
