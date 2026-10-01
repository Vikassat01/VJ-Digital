<?php
$pageTitle = 'Settings';
require_once '../includes/header.php';
require_once '../includes/auth_functions.php';

requireAdminLogin();

$error = '';
$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $site_name = sanitizeInput($_POST['site_name']);
    $currency = $_POST['currency'];
    $tax_rate = $_POST['tax_rate'];
    $razorpay_key = sanitizeInput($_POST['razorpay_key']);
    $razorpay_secret = sanitizeInput($_POST['razorpay_secret']);
    $stripe_key = sanitizeInput($_POST['stripe_key']);
    $stripe_secret = sanitizeInput($_POST['stripe_secret']);
    $paypal_client_id = sanitizeInput($_POST['paypal_client_id']);
    $paypal_secret = sanitizeInput($_POST['paypal_secret']);
    $email_notifications = isset($_POST['email_notifications']) ? '1' : '0';
    
    // Update settings
    $settings = [
        'site_name' => $site_name,
        'currency' => $currency,
        'tax_rate' => $tax_rate,
        'razorpay_key' => $razorpay_key,
        'razorpay_secret' => $razorpay_secret,
        'stripe_key' => $stripe_key,
        'stripe_secret' => $stripe_secret,
        'paypal_client_id' => $paypal_client_id,
        'paypal_secret' => $paypal_secret,
        'email_notifications' => $email_notifications
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
    }
    
    $success = 'Settings updated successfully!';
}

// Get current settings
$settings_result = $conn->query("SELECT * FROM settings");
$current_settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}
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
                    <a class="nav-link active" href="settings.php">
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
            <h2 class="fw-bold mb-4">Settings</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-mdb-toggle="tab" data-mdb-target="#general">General</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-mdb-toggle="tab" data-mdb-target="#payment">Payment Gateways</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-mdb-toggle="tab" data-mdb-target="#notifications">Notifications</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general">
                                <div class="mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($current_settings['site_name'] ?? SITE_NAME); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Currency</label>
                                    <select name="currency" class="form-select">
                                        <option value="USD" <?php echo ($current_settings['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                        <option value="EUR" <?php echo ($current_settings['currency'] ?? 'USD') == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                        <option value="GBP" <?php echo ($current_settings['currency'] ?? 'USD') == 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                        <option value="INR" <?php echo ($current_settings['currency'] ?? 'USD') == 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tax Rate (%)</label>
                                    <input type="number" name="tax_rate" class="form-control" step="0.01" value="<?php echo $current_settings['tax_rate'] ?? 0; ?>">
                                </div>
                            </div>
                            
                            <!-- Payment Gateways -->
                            <div class="tab-pane fade" id="payment">
                                <h5 class="mb-3">Razorpay</h5>
                                <div class="mb-3">
                                    <label class="form-label">Razorpay Key</label>
                                    <input type="text" name="razorpay_key" class="form-control" value="<?php echo htmlspecialchars($current_settings['razorpay_key'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Razorpay Secret</label>
                                    <input type="password" name="razorpay_secret" class="form-control" value="<?php echo htmlspecialchars($current_settings['razorpay_secret'] ?? ''); ?>">
                                </div>
                                
                                <hr>
                                
                                <h5 class="mb-3">Stripe</h5>
                                <div class="mb-3">
                                    <label class="form-label">Stripe Key</label>
                                    <input type="text" name="stripe_key" class="form-control" value="<?php echo htmlspecialchars($current_settings['stripe_key'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stripe Secret</label>
                                    <input type="password" name="stripe_secret" class="form-control" value="<?php echo htmlspecialchars($current_settings['stripe_secret'] ?? ''); ?>">
                                </div>
                                
                                <hr>
                                
                                <h5 class="mb-3">PayPal</h5>
                                <div class="mb-3">
                                    <label class="form-label">PayPal Client ID</label>
                                    <input type="text" name="paypal_client_id" class="form-control" value="<?php echo htmlspecialchars($current_settings['paypal_client_id'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">PayPal Secret</label>
                                    <input type="password" name="paypal_secret" class="form-control" value="<?php echo htmlspecialchars($current_settings['paypal_secret'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Notifications -->
                            <div class="tab-pane fade" id="notifications">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" id="emailNotifications" <?php echo ($current_settings['email_notifications'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="emailNotifications">Enable Email Notifications</label>
                                </div>
                                <p class="text-muted small">Send email notifications for order confirmations, payment failures, etc.</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
