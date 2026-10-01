<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Get categories for navigation
$categories_query = "SELECT * FROM categories WHERE status = 'active' LIMIT 8";
$categories_result = $conn->query($categories_query);

// Get featured products
$featured_query = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8";
$featured_result = $conn->query($featured_query);
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
            <?php while ($cat = $categories_result->fetch_assoc()): ?>
                <a href="products.php?category=<?php echo urlencode($cat['name']); ?>" class="text-white text-decoration-none small">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</nav>

<!-- Hero Banner -->
<section style="background: linear-gradient(to right, #232f3e, #146eb4); padding: 60px 0; color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-3">Welcome to <?php echo SITE_NAME; ?></h1>
                <p class="lead mb-4">Discover premium digital products - software, templates, e-books, graphics, and more. Instant download, lifetime access.</p>
                <div class="d-flex gap-2">
                    <a href="products.php" class="btn btn-lg" style="background-color: #ff9900; border: none; color: #000;">Shop Now</a>
                    <a href="faq.php" class="btn btn-outline-light btn-lg">Learn More</a>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <div style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 30px;">
                    <h3 class="fw-bold mb-3">Why Choose Us?</h3>
                    <ul class="list-unstyled text-start">
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #ff9900;"></i>Instant Download</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #ff9900;"></i>Lifetime Access</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #ff9900;"></i>Secure Payments</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #ff9900;"></i>24/7 Support</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Cards -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Shop by Category</h2>
        <div class="row g-3">
            <?php 
            $categories_result->data_seek(0);
            $category_icons = ['fa-laptop', 'fa-book', 'fa-file-alt', 'fa-image', 'fa-music', 'fa-video', 'fa-gamepad', 'fa-graduation-cap'];
            $icon_index = 0;
            while ($cat = $categories_result->fetch_assoc()): 
            ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="products.php?category=<?php echo urlencode($cat['name']); ?>" class="text-decoration-none">
                        <div class="card h-100 text-center" style="border: 1px solid #e0e0e0; transition: all 0.3s;">
                            <div class="card-body py-3">
                                <i class="fas <?php echo $category_icons[$icon_index++ % count($category_icons)]; ?> fs-2 mb-2" style="color: #146eb4;"></i>
                                <h6 class="card-title small mb-0" style="color: var(--text-primary);"><?php echo htmlspecialchars($cat['name']); ?></h6>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5" style="background-color: var(--bg-secondary);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Featured Products</h2>
            <a href="products.php" class="text-decoration-none" style="color: #146eb4;">See all <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="row g-3">
            <?php while ($product = $featured_result->fetch_assoc()): ?>
                <?php 
                $screenshots = json_decode($product['screenshots'], true);
                $first_image = !empty($screenshots) ? $screenshots[0] : 'https://via.placeholder.com/300x300?text=Product';
                $has_discount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
                $display_price = $has_discount ? $product['discount_price'] : $product['price'];
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card" style="border: 1px solid #e0e0e0; transition: all 0.3s;">
                        <div style="position: relative;">
                            <img src="<?php echo $first_image; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>" style="height: 180px; object-fit: cover;">
                            <?php if ($has_discount): ?>
                                <span class="position-absolute top-0 end-0 badge bg-danger m-2">-<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title small mb-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($product['title']); ?></h6>
                            <div class="mb-2">
                                <?php if ($product['rating'] > 0): ?>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= round($product['rating']) ? 'text-warning' : 'text-muted'; ?>" style="font-size: 12px;"></i>
                                    <?php endfor; ?>
                                    <span class="small text-muted"><?php echo $product['rating_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($has_discount): ?>
                                        <span class="fw-bold" style="color: #b12704;"><?php echo CURRENCY_SYMBOL . number_format($display_price, 2); ?></span>
                                        <span class="text-muted text-decoration-line-through small ms-1"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold" style="color: #b12704;"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm" style="background-color: #ffd814; border: none; color: #000;">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4 text-center">Why Buy From Us?</h2>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <i class="fas fa-bolt fs-1 mb-3" style="color: #ff9900;"></i>
                    <h5 class="fw-bold">Instant Download</h5>
                    <p class="text-muted small">Get your files immediately after purchase</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <i class="fas fa-shield-alt fs-1 mb-3" style="color: #ff9900;"></i>
                    <h5 class="fw-bold">Secure Payment</h5>
                    <p class="text-muted small">100% secure payment processing</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <i class="fas fa-infinity fs-1 mb-3" style="color: #ff9900;"></i>
                    <h5 class="fw-bold">Lifetime Access</h5>
                    <p class="text-muted small">Use your products forever</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4">
                    <i class="fas fa-headset fs-1 mb-3" style="color: #ff9900;"></i>
                    <h5 class="fw-bold">24/7 Support</h5>
                    <p class="text-muted small">We're here to help anytime</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer style="background-color: #131A22; color: white; padding: 40px 0 20px;">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3" style="color: white;">Get to Know Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="about.php" class="text-white text-decoration-none" style="color: #ddd;">About Us</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Careers</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Press Releases</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3" style="color: white;">Connect with Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Facebook</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Twitter</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Instagram</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3" style="color: white;">Make Money with Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Sell on <?php echo SITE_NAME; ?></a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Become an Affiliate</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Advertise Your Products</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold mb-3" style="color: white;">Let Us Help You</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none" style="color: #ddd;">Your Account</a></li>
                    <li class="mb-2"><a href="orders.php" class="text-white text-decoration-none" style="color: #ddd;">Your Orders</a></li>
                    <li class="mb-2"><a href="faq.php" class="text-white text-decoration-none" style="color: #ddd;">Help & FAQ</a></li>
                </ul>
            </div>
        </div>
        <hr style="border-color: #3a4553;">
        <div class="text-center" style="color: #ddd; font-size: 14px;">
            &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
        </div>
    </div>
</footer>

<?php require_once 'includes/footer.php'; ?>
