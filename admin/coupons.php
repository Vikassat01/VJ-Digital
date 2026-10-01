<?php
$pageTitle = 'Manage Coupons';
require_once '../includes/header.php';
require_once '../includes/auth_functions.php';

requireAdminLogin();

$error = '';
$success = '';

// Handle coupon operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_coupon']) || isset($_POST['edit_coupon'])) {
        $code = strtoupper(sanitizeInput($_POST['code']));
        $discount_type = $_POST['discount_type'];
        $discount_value = $_POST['discount_value'];
        $expiry_date = $_POST['expiry_date'];
        $usage_limit = $_POST['usage_limit'];
        $status = $_POST['status'];
        
        if (isset($_POST['edit_coupon'])) {
            $coupon_id = $_POST['coupon_id'];
            $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, expiry_date = ?, usage_limit = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssdsisi", $code, $discount_type, $discount_value, $expiry_date, $usage_limit, $status, $coupon_id);
            
            if ($stmt->execute()) {
                $success = 'Coupon updated successfully!';
            } else {
                $error = 'Failed to update coupon.';
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, expiry_date, usage_limit, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsis", $code, $discount_type, $discount_value, $expiry_date, $usage_limit, $status);
            
            if ($stmt->execute()) {
                $success = 'Coupon added successfully!';
            } else {
                $error = 'Failed to add coupon.';
            }
        }
    }
    
    if (isset($_POST['delete_coupon'])) {
        $coupon_id = $_POST['coupon_id'];
        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $coupon_id);
        
        if ($stmt->execute()) {
            $success = 'Coupon deleted successfully!';
        } else {
            $error = 'Failed to delete coupon.';
        }
    }
}

// Get all coupons
$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
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
                    <a class="nav-link active" href="coupons.php">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Manage Coupons</h2>
                <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addCouponModal">
                    <i class="fas fa-plus me-2"></i>Add Coupon
                </button>
            </div>
            
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
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Usage</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($coupon = $coupons->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                        <td><?php echo ucfirst($coupon['discount_type']); ?></td>
                                        <td>
                                            <?php echo $coupon['discount_type'] == 'flat' ? CURRENCY_SYMBOL : ''; ?>
                                            <?php echo number_format($coupon['discount_value'], 2); ?>
                                            <?php echo $coupon['discount_type'] == 'percentage' ? '%' : ''; ?>
                                        </td>
                                        <td><?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($coupon['expiry_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $coupon['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($coupon['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-mdb-toggle="modal" data-mdb-target="#editCouponModal<?php echo $coupon['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                                <button type="submit" name="delete_coupon" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editCouponModal<?php echo $coupon['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Coupon</h5>
                                                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                                        <input type="hidden" name="edit_coupon" value="1">
                                                        <div class="mb-3">
                                                            <label class="form-label">Code</label>
                                                            <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($coupon['code']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Discount Type</label>
                                                            <select name="discount_type" class="form-select">
                                                                <option value="flat" <?php echo $coupon['discount_type'] == 'flat' ? 'selected' : ''; ?>>Flat Amount</option>
                                                                <option value="percentage" <?php echo $coupon['discount_type'] == 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Discount Value</label>
                                                            <input type="number" name="discount_value" class="form-control" step="0.01" value="<?php echo $coupon['discount_value']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Expiry Date</label>
                                                            <input type="date" name="expiry_date" class="form-control" value="<?php echo $coupon['expiry_date']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Usage Limit</label>
                                                            <input type="number" name="usage_limit" class="form-control" value="<?php echo $coupon['usage_limit']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active" <?php echo $coupon['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                <option value="inactive" <?php echo $coupon['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Update Coupon</button>
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

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Coupon</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="add_coupon" value="1">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="SAVE20" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" class="form-select">
                            <option value="flat">Flat Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Value</label>
                        <input type="number" name="discount_value" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usage Limit</label>
                        <input type="number" name="usage_limit" class="form-control" value="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Coupon</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
