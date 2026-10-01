<?php
$pageTitle = 'Reports & Analytics';
require_once '../includes/header.php';
require_once '../includes/auth_functions.php';

requireAdminLogin();

// Get statistics
$total_revenue = $conn->query("SELECT SUM(final_amount) as total FROM orders WHERE payment_status = 'completed'")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'completed'")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending'")->fetch_assoc()['count'];

// Monthly sales
$monthly_sales = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(final_amount) as total, COUNT(*) as count FROM orders WHERE payment_status = 'completed' GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 12");

// Top selling products
$top_products = $conn->query("SELECT p.title, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as revenue FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5");
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
                    <a class="nav-link" href="orders.php">
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
                    <a class="nav-link active" href="reports.php">
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
            <h2 class="fw-bold mb-4">Reports & Analytics</h2>
            
            <!-- Revenue Overview -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-primary text-white">
                        <div class="card-body">
                            <h6 class="mb-0">Total Revenue</h6>
                            <h3 class="fw-bold mb-0"><?php echo CURRENCY_SYMBOL . number_format($total_revenue, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-success text-white">
                        <div class="card-body">
                            <h6 class="mb-0">Total Orders</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-info text-white">
                        <div class="card-body">
                            <h6 class="mb-0">Completed</h6>
                            <h3 class="fw-bold mb-0"><?php echo $completed_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-warning text-white">
                        <div class="card-body">
                            <h6 class="mb-0">Pending</h6>
                            <h3 class="fw-bold mb-0"><?php echo $pending_orders; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Monthly Sales -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Monthly Sales</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($monthly_sales->num_rows > 0): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Orders</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($month = $monthly_sales->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                                <td><?php echo $month['count']; ?></td>
                                                <td><?php echo CURRENCY_SYMBOL . number_format($month['total'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No sales data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Top Products -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Top Selling Products</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($top_products->num_rows > 0): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Sold</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $top_products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                                <td><?php echo $product['total_sold']; ?></td>
                                                <td><?php echo CURRENCY_SYMBOL . number_format($product['revenue'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No product data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
