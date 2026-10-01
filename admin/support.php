<?php
$pageTitle = 'Support Management';
require_once '../includes/header.php';
require_once '../includes/auth_functions.php';

requireAdminLogin();

$error = '';
$success = '';

// Handle support ticket operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_ticket'])) {
        $ticket_id = $_POST['ticket_id'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $ticket_id);
        
        if ($stmt->execute()) {
            $success = 'Ticket updated successfully!';
        } else {
            $error = 'Failed to update ticket.';
        }
    }
}

// Get all support tickets
$tickets = $conn->query("SELECT st.*, u.name as user_name, u.email as user_email FROM support_tickets st JOIN users u ON st.user_id = u.id ORDER BY st.created_at DESC");
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
                    <a class="nav-link active" href="support.php">
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
            <h2 class="fw-bold mb-4">Support Tickets</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($tickets->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ticket = $tickets->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($ticket['user_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($ticket['user_email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $ticket['status'] == 'open' ? 'danger' : 
                                                    ($ticket['status'] == 'in_progress' ? 'warning' : 
                                                    ($ticket['status'] == 'resolved' ? 'success' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-mdb-toggle="modal" data-mdb-target="#viewTicketModal<?php echo $ticket['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Ticket Modal -->
                                        <div class="modal fade" id="viewTicketModal<?php echo $ticket['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Ticket #<?php echo $ticket['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-4">
                                                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($ticket['subject']); ?></p>
                                                            <p><strong>User:</strong> <?php echo htmlspecialchars($ticket['user_name']); ?> (<?php echo htmlspecialchars($ticket['user_email']); ?>)</p>
                                                            <p><strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?></p>
                                                            <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></p>
                                                        </div>
                                                        
                                                        <div class="mb-4">
                                                            <h6>Message:</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <form method="POST">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Update Status</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                                                                    <option value="in_progress" <?php echo $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                                    <option value="resolved" <?php echo $ticket['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                                    <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                                                </select>
                                                            </div>
                                                            <button type="submit" name="update_ticket" class="btn btn-primary">Update Status</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <h4>No support tickets</h4>
                            <p>When users submit support requests, they will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
