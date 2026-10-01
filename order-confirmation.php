<?php
$pageTitle = 'Order Confirmation';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

requireLogin();

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    header('Location: orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, p.title, p.file_path FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
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

<div class="container py-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                    </div>
                    <h1 class="fw-bold mb-3">Order Placed Successfully!</h1>
                    <p class="text-muted mb-4">Thank you for your purchase. Your order has been received.</p>
                    
                    <div class="alert alert-info">
                        <p class="mb-0"><strong>Order ID:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p class="mb-0"><strong>Total Amount:</strong> <?php echo CURRENCY_SYMBOL . number_format($order['final_amount'], 2); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted">A confirmation email has been sent to your email address.</p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="orders.php" class="btn btn-primary">View Orders</a>
                        <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
                    </div>
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
        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
    </div>
</nav>

<?php require_once 'includes/footer.php'; ?>
