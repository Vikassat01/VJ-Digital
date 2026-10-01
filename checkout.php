<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

requireLogin();

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle coupon
$discount = 0;
$coupon_error = '';
$coupon_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = sanitizeInput($_POST['coupon_code']);
    
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE()");
    $stmt->bind_param("s", $coupon_code);
    $stmt->execute();
    $coupon_result = $stmt->get_result();
    
    if ($coupon_result->num_rows == 1) {
        $coupon = $coupon_result->fetch_assoc();
        
        if ($coupon['used_count'] >= $coupon['usage_limit']) {
            $coupon_error = 'Coupon usage limit exceeded.';
        } else {
            if ($coupon['discount_type'] == 'flat') {
                $discount = $coupon['discount_value'];
            } else {
                $discount = ($subtotal * $coupon['discount_value']) / 100;
            }
            $_SESSION['applied_coupon'] = $coupon;
            $coupon_success = 'Coupon applied successfully!';
        }
    } else {
        $coupon_error = 'Invalid or expired coupon code.';
    }
}

if (isset($_SESSION['applied_coupon'])) {
    $discount = $_SESSION['applied_coupon']['discount_type'] == 'flat' 
        ? $_SESSION['applied_coupon']['discount_value'] 
        : ($subtotal * $_SESSION['applied_coupon']['discount_value']) / 100;
}

$total = $subtotal - $discount;

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'];
    
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, 'pending', 'pending')");
    $stmt->bind_param("iddds", $_SESSION['user_id'], $subtotal, $discount, $total, $payment_method);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Add order items
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidi", $order_id, $item['id'], $item['price'], $item['quantity']);
        $stmt->execute();
    }
    
    // Update coupon usage
    if (isset($_SESSION['applied_coupon'])) {
        $stmt = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['applied_coupon']['id']);
        $stmt->execute();
        unset($_SESSION['applied_coupon']);
    }
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Redirect to order confirmation
    header('Location: order-confirmation.php?id=' . $order_id);
    exit();
}

// Get payment gateway settings
$razorpay_key = '';
$stripe_key = '';
$paypal_client_id = '';

$settings_query = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('razorpay_key', 'stripe_key', 'paypal_client_id')";
$settings_result = $conn->query($settings_query);
while ($setting = $settings_result->fetch_assoc()) {
    if ($setting['setting_key'] == 'razorpay_key') $razorpay_key = $setting['setting_value'];
    if ($setting['setting_key'] == 'stripe_key') $stripe_key = $setting['setting_value'];
    if ($setting['setting_key'] == 'paypal_client_id') $paypal_client_id = $setting['setting_value'];
}
?>

<!-- Mobile AppBar -->
<nav class="navbar navbar-dark bg-dark d-md-none">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
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
                <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4 pb-5">
    <h1 class="fw-bold mb-4">Checkout</h1>
    
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Billing Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="checkout.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="checkout.php">
                        <?php if (!empty($razorpay_key)): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="razorpay" value="razorpay" checked>
                                <label class="form-check-label" for="razorpay">
                                    <i class="fas fa-credit-card me-2"></i>Razorpay
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($stripe_key)): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" <?php echo empty($razorpay_key) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="stripe">
                                    <i class="fab fa-stripe me-2"></i>Stripe
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($paypal_client_id)): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" <?php echo (empty($razorpay_key) && empty($stripe_key)) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="paypal">
                                    <i class="fab fa-paypal me-2"></i>PayPal
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" <?php echo (empty($razorpay_key) && empty($stripe_key) && empty($paypal_client_id)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="bank_transfer">
                                <i class="fas fa-university me-2"></i>Bank Transfer
                            </label>
                        </div>
                        
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-2"></i>
                            For demo purposes, you can select any payment method. In production, integrate actual payment gateways.
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($item['title']); ?></p>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span><?php echo CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?php echo CURRENCY_SYMBOL . number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>-<?php echo CURRENCY_SYMBOL . number_format($discount, 2); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (0%)</span>
                        <span><?php echo CURRENCY_SYMBOL . '0.00'; ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary h4"><?php echo CURRENCY_SYMBOL . number_format($total, 2); ?></span>
                    </div>
                    
                    <!-- Coupon -->
                    <div class="mb-4">
                        <?php if ($coupon_error): ?>
                            <div class="alert alert-danger small"><?php echo $coupon_error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($coupon_success): ?>
                            <div class="alert alert-success small"><?php echo $coupon_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!isset($_SESSION['applied_coupon'])): ?>
                            <form method="POST" action="checkout.php">
                                <div class="input-group">
                                    <input type="text" name="coupon_code" class="form-control" placeholder="Coupon code">
                                    <button type="submit" name="apply_coupon" class="btn btn-outline-secondary">Apply</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success small">
                                <i class="fas fa-check-circle me-2"></i>
                                Coupon: <?php echo htmlspecialchars($_SESSION['applied_coupon']['code']); ?> applied
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="checkout.php">
                        <input type="hidden" name="payment_method" id="selected_payment" value="bank_transfer">
                        <button type="submit" name="place_order" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-lock me-2"></i>Place Order
                        </button>
                    </form>
                    
                    <p class="text-center text-muted small mt-3">
                        <i class="fas fa-shield-alt me-1"></i>Your payment information is secure
                    </p>
                </div>
            </div>
        </div>
    </div>
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

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('selected_payment').value = this.value;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
