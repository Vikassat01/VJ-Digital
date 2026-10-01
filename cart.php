<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Get categories for navigation
$categories_query = "SELECT * FROM categories WHERE status = 'active' LIMIT 8";
$categories_nav_result = $conn->query($categories_query);

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? 0;

if ($action == 'add' && $product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'quantity' => 1,
                'image' => json_decode($product['screenshots'], true)[0] ?? 'https://via.placeholder.com/100'
            ];
        }
    }
    
    if ($action == 'buy_now') {
        header('Location: checkout.php');
        exit();
    }
    
    header('Location: cart.php');
    exit();
}

if ($action == 'remove' && $product_id > 0) {
    unset($_SESSION['cart'][$product_id]);
    header('Location: cart.php');
    exit();
}

if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['quantities'] as $id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$id]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    header('Location: cart.php');
    exit();
}

if ($action == 'clear') {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
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
    <h1 class="fw-bold mb-4">Shopping Cart</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info text-center">
            <h4>Your cart is empty</h4>
            <p>Add some products to get started!</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="cart.php?action=update">
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="row align-items-center mb-4 border-bottom pb-4">
                                    <div class="col-md-3">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-fluid rounded" style="height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h5>
                                        <p class="text-muted mb-0"><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group" style="width: 120px;">
                                            <input type="number" name="quantities[<?php echo $item['id']; ?>]" class="form-control" value="<?php echo $item['quantity']; ?>" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <p class="fw-bold mb-2"><?php echo CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">Update Cart</button>
                                    <a href="cart.php?action=clear" class="btn btn-outline-danger">Clear Cart</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span><?php echo CURRENCY_SYMBOL . number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tax (0%)</span>
                            <span><?php echo CURRENCY_SYMBOL . '0.00'; ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-primary"><?php echo CURRENCY_SYMBOL . number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <!-- Coupon Code -->
                        <div class="mb-4">
                            <label class="form-label">Coupon Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Enter coupon code" id="couponCode">
                                <button class="btn btn-outline-secondary" type="button">Apply</button>
                            </div>
                        </div>
                        
                        <?php if (isLoggedIn()): ?>
                            <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-lock me-2"></i>Login to Checkout
                            </a>
                            <p class="text-center text-muted small mt-2">You need to login to complete your purchase</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-bottom d-md-none pb-4">
    <div class="container-fluid justify-content-around">
        <a class="nav-link" href="index.php"><i class="fas fa-home"></i><br><small>Home</small></a>
        <a class="nav-link" href="products.php"><i class="fas fa-box"></i><br><small>Products</small></a>
        <a class="nav-link active" href="cart.php"><i class="fas fa-shopping-cart"></i><br><small>Cart</small></a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
    </div>
</nav>

<?php require_once 'includes/footer.php'; ?>
