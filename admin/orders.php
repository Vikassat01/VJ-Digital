<?php
$pageTitle = 'Manage Orders';
require_once '../includes/header.php';
require_once '../includes/auth_functions.php';

requireAdminLogin();

$error = '';
$success = '';

// Handle order operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_order'])) {
        $order_id = $_POST['order_id'];
        $payment_status = $_POST['payment_status'];
        $order_status = $_POST['order_status'];
        
        $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $payment_status, $order_status, $order_id);
        
        if ($stmt->execute()) {
            $success = 'Order updated successfully!';
        } else {
            $error = 'Failed to update order.';
        }
    }
    
    if (isset($_POST['refund_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'refunded', order_status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            $success = 'Order refunded successfully!';
        } else {
            $error = 'Failed to refund order.';
        }
    }
}

// Get all orders
$orders = $conn->query("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>

<style>
.admin-sidebar {
    min-height: calc(100vh - 56px);
    background: #2c3e50;
}
.admin-sidebar .nav-link {
    color: #ecf0f1;
    padding: 12px 20px;
}
.admin-sidebar .nav-link:hover,
.admin-sidebar .nav-link.active {
    background: #34495e;
    color: #fff;
}
</style>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">VJ Digital Admin</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 admin-sidebar py-4 d-none d-md-block">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="orders.php">
                        <i class="fas fa-shopping-bag me-2"></i>Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="coupons.php">
                        <i class="fas fa-ticket-alt me-2"></i>Coupons
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="support.php">
                        <i class="fas fa-headset me-2"></i>Support
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-external-link-alt me-2"></i>View Website
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 py-4">
            <h2 class="fw-bold mb-4">Manage Orders</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['user_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                                        </td>
                                        <td><?php echo CURRENCY_SYMBOL . number_format($order['final_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : ($order['payment_status'] == 'refunded' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $order['order_status'] == 'completed' ? 'success' : 'info'; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-mdb-toggle="modal" data-mdb-target="#viewOrderModal<?php echo $order['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Order Modal -->
                                    <div class="modal fade" id="viewOrderModal<?php echo $order['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h5>
                                                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    // Get order items
                                                    $items_query = "SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
                                                    $items_stmt = $conn->prepare($items_query);
                                                    $items_stmt->bind_param("i", $order['id']);
                                                    $items_stmt->execute();
                                                    $items_result = $items_stmt->get_result();
                                                    ?>
                                                    
                                                    <div class="mb-4">
                                                        <h6>Order Details</h6>
                                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                                                        <p><strong>Total Amount:</strong> <?php echo CURRENCY_SYMBOL . number_format($order['final_amount'], 2); ?></p>
                                                        <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <h6>Order Items</h6>
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Price</th>
                                                                    <th>Quantity</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                                        <td><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></td>
                                                                        <td><?php echo $item['quantity']; ?></td>
                                                                        <td><?php echo CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    <form method="POST">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Payment Status</label>
                                                                <select name="payment_status" class="form-select">
                                                                    <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="completed" <?php echo $order['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                    <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                                    <option value="refunded" <?php echo $order['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Order Status</label>
                                                                <select name="order_status" class="form-select">
                                                                    <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                                    <option value="completed" <?php echo $order['order_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                    <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
                                                            <?php if ($order['payment_status'] == 'completed'): ?>
                                                                <button type="submit" name="refund_order" class="btn btn-danger" onclick="return confirm('Are you sure you want to refund this order?')">Refund Order</button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
