<?php
$pageTitle = 'My Orders';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

requireLogin();

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders_result = $stmt->get_result();
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
    <h1 class="fw-bold mb-4">My Orders</h1>
    
    <?php if ($orders_result->num_rows == 0): ?>
        <div class="alert alert-info text-center">
            <h4>No orders yet</h4>
            <p>Start shopping to see your orders here!</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <?php
                // Get order items
                $items_query = "SELECT oi.*, p.title, p.file_path FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
                $items_stmt = $conn->prepare($items_query);
                $items_stmt->bind_param("i", $order['id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                ?>
                
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                <small class="ms-3"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <div class="row align-items-center mb-3 border-bottom pb-3">
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="fw-bold"><?php echo CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <?php if ($order['payment_status'] == 'completed' && !empty($item['file_path'])): ?>
                                            <a href="download.php?order_id=<?php echo $order['id']; ?>&product_id=<?php echo $item['product_id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-lock me-1"></i>Unavailable
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <span class="text-muted">Total: </span>
                                    <span class="fw-bold text-primary h5"><?php echo CURRENCY_SYMBOL . number_format($order['final_amount'], 2); ?></span>
                                </div>
                                <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-file-invoice me-1"></i>Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
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
