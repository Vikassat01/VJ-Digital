<?php
$pageTitle = 'My Wishlist';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? '';

// Handle add/remove from wishlist
if ($action && $product_id) {
    $product_id = (int)$product_id;
    
    if ($action == 'add') {
        // Check if already in wishlist
        $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
        }
    } elseif ($action == 'remove') {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
    
    header('Location: wishlist.php');
    exit();
}

// Get wishlist items
$query = "SELECT p.*, w.created_at as wishlist_added_at 
          FROM wishlist w 
          JOIN products p ON w.product_id = p.id 
          WHERE w.user_id = ? AND p.status = 'active' 
          ORDER BY w.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Mobile AppBar -->
<nav class="navbar navbar-dark bg-dark d-md-none">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
        <button class="btn btn-outline-light btn-sm" onclick="toggleTheme()">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</nav>

<!-- Desktop Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark d-none d-md-block">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <li class="nav-item">
                    <button class="btn btn-outline-light btn-sm ms-2" onclick="toggleTheme()">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4 pb-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-heart text-danger me-2"></i>My Wishlist</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="row g-4">
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <?php 
                        $screenshots = json_decode($product['screenshots'], true);
                        $first_image = !empty($screenshots) ? $screenshots[0] : 'https://via.placeholder.com/400x300';
                        $has_discount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
                        $display_price = $has_discount ? $product['discount_price'] : $product['price'];
                        $is_in_stock = $product['stock'] > 0;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 product-card shadow-sm position-relative">
                                <!-- Remove Button -->
                                <a href="wishlist.php?action=remove&id=<?php echo $product['id']; ?>" class="btn btn-danger position-absolute top-0 end-0 m-2" style="z-index: 10;">
                                    <i class="fas fa-times"></i>
                                </a>
                                
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
                                            <span class="badge bg-success">In Stock</span>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <h4><i class="fas fa-heart-broken me-2"></i>Your wishlist is empty</h4>
                    <p>Browse our products and add items to your wishlist!</p>
                    <a href="products.php" class="btn btn-primary">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-bottom d-md-none pb-4">
    <div class="container-fluid justify-content-around">
        <a class="nav-link" href="index.php"><i class="fas fa-home"></i><br><small>Home</small></a>
        <a class="nav-link" href="products.php"><i class="fas fa-box"></i><br><small>Products</small></a>
        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i><br><small>Cart</small></a>
        <a class="nav-link active" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
    </div>
</nav>

<?php require_once 'includes/footer.php'; ?>
