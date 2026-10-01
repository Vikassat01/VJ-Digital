<?php
$pageTitle = 'Invoice';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

requireLogin();

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    header('Location: orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<style>
@media print {
    .no-print { display: none !important; }
    .container { max-width: 100% !important; }
}
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <!-- Invoice Header -->
                    <div class="d-flex justify-content-between align-items-start mb-5">
                        <div>
                            <h2 class="fw-bold"><?php echo SITE_NAME; ?></h2>
                            <p class="text-muted mb-0">Digital Products Marketplace</p>
                        </div>
                        <div class="text-end">
                            <h4 class="fw-bold mb-1">INVOICE</h4>
                            <p class="text-muted mb-0">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>
                    
                    <!-- Invoice Details -->
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Bill To:</h6>
                            <p class="mb-1"><strong><?php echo htmlspecialchars($order['name']); ?></strong></p>
                            <p class="mb-0"><?php echo htmlspecialchars($order['email']); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="fw-bold mb-3">Invoice Details:</h6>
                            <p class="mb-1">Date: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1">Payment Method: <?php echo ucfirst($order['payment_method']); ?></p>
                            <p class="mb-0">Status: <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>
                        </div>
                    </div>
                    
                    <!-- Invoice Items -->
                    <table class="table mb-5">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td class="text-end"><?php echo CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <!-- Invoice Summary -->
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo CURRENCY_SYMBOL . number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <?php if ($order['discount_amount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-<?php echo CURRENCY_SYMBOL . number_format($order['discount_amount'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (0%):</span>
                                <span><?php echo CURRENCY_SYMBOL . '0.00'; ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold h5"><?php echo CURRENCY_SYMBOL . number_format($order['final_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Footer -->
                    <div class="mt-5 pt-4 border-top">
                        <p class="text-muted text-center mb-0">Thank you for your purchase!</p>
                        <p class="text-muted text-center small">For any questions, please contact support@vjdigital.com</p>
                    </div>
                </div>
            </div>
            
            <!-- Print Button -->
            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Print Invoice
                </button>
                <a href="orders.php" class="btn btn-outline-secondary ms-2">Back to Orders</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
