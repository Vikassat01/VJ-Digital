<?php
$pageTitle = 'Manage Products';
require_once '../includes/header.php';
require_once '../includes/auth_functions.php';

requireAdminLogin();

$error = '';
$success = '';

// Handle product operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['edit_product'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $price = $_POST['price'];
        $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : NULL;
        $category = sanitizeInput($_POST['category']);
        $subcategory = !empty($_POST['subcategory']) ? sanitizeInput($_POST['subcategory']) : NULL;
        $brand = !empty($_POST['brand']) ? sanitizeInput($_POST['brand']) : NULL;
        $stock = !empty($_POST['stock']) ? (int)$_POST['stock'] : 999;
        $badge = sanitizeInput($_POST['badge']);
        $features = !empty($_POST['features']) ? json_encode(explode('\n', $_POST['features'])) : NULL;
        $specifications = !empty($_POST['specifications']) ? json_encode(explode('\n', $_POST['specifications'])) : NULL;
        $status = $_POST['status'];
        
        // Handle file upload
        $file_path = '';
        if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] == 0) {
            $file_name = time() . '_' . $_FILES['product_file']['name'];
            $file_path = 'uploads/products/' . $file_name;
            move_uploaded_file($_FILES['product_file']['tmp_name'], '../' . $file_path);
        }
        
        // Handle screenshots
        $screenshots = [];
        if (isset($_FILES['screenshots'])) {
            foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['screenshots']['error'][$key] == 0) {
                    $screenshot_name = time() . '_' . $_FILES['screenshots']['name'][$key];
                    $screenshot_path = 'uploads/screenshots/' . $screenshot_name;
                    move_uploaded_file($tmp_name, '../' . $screenshot_path);
                    $screenshots[] = SITE_URL . $screenshot_path;
                }
            }
        }
        
        if (isset($_POST['edit_product'])) {
            $product_id = $_POST['product_id'];
            $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, discount_price = ?, category = ?, subcategory = ?, brand = ?, stock = ?, badge = ?, features = ?, specifications = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssddsssssssi", $title, $description, $price, $discount_price, $category, $subcategory, $brand, $stock, $badge, $features, $specifications, $status, $product_id);
            
            if ($stmt->execute()) {
                $success = 'Product updated successfully!';
            } else {
                $error = 'Failed to update product.';
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO products (title, description, price, discount_price, category, subcategory, brand, stock, badge, features, specifications, file_path, screenshots, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $screenshots_json = json_encode($screenshots);
            $stmt->bind_param("ssddsssssssss", $title, $description, $price, $discount_price, $category, $subcategory, $brand, $stock, $badge, $features, $specifications, $file_path, $screenshots_json, $status);
            
            if ($stmt->execute()) {
                $success = 'Product added successfully!';
            } else {
                $error = 'Failed to add product.';
            }
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $success = 'Product deleted successfully!';
        } else {
            $error = 'Failed to delete product.';
        }
    }
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
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
                    <a class="nav-link active" href="products.php">
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
                <h2 class="fw-bold">Manage Products</h2>
                <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add Product
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
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Badge</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = $products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td><?php echo htmlspecialchars($product['brand'] ?? '-'); ?></td>
                                        <td>
                                            <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                                <span class="text-success"><?php echo CURRENCY_SYMBOL . number_format($product['discount_price'], 2); ?></span>
                                                <br><small class="text-muted text-decoration-line-through"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></small>
                                            <?php else: ?>
                                                <?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $product['stock']; ?></td>
                                        <td>
                                            <?php if ($product['badge'] != 'none'): ?>
                                                <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $product['badge'])); ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-mdb-toggle="modal" data-mdb-target="#editProductModal<?php echo $product['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editProductModal<?php echo $product['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Product</h5>
                                                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <input type="hidden" name="edit_product" value="1">
                                                        <div class="mb-3">
                                                            <label class="form-label">Title</label>
                                                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($product['title']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Category</label>
                                                            <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Subcategory</label>
                                                            <input type="text" name="subcategory" class="form-control" value="<?php echo htmlspecialchars($product['subcategory'] ?? ''); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Brand</label>
                                                            <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Price</label>
                                                            <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $product['price']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Discount Price (Optional)</label>
                                                            <input type="number" name="discount_price" class="form-control" step="0.01" value="<?php echo $product['discount_price'] ?? ''; ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Stock</label>
                                                            <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Badge</label>
                                                            <select name="badge" class="form-select">
                                                                <option value="none" <?php echo $product['badge'] == 'none' ? 'selected' : ''; ?>>None</option>
                                                                <option value="best_seller" <?php echo $product['badge'] == 'best_seller' ? 'selected' : ''; ?>>Best Seller</option>
                                                                <option value="new_arrival" <?php echo $product['badge'] == 'new_arrival' ? 'selected' : ''; ?>>New Arrival</option>
                                                                <option value="discount" <?php echo $product['badge'] == 'discount' ? 'selected' : ''; ?>>Discount</option>
                                                                <option value="trending" <?php echo $product['badge'] == 'trending' ? 'selected' : ''; ?>>Trending</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Features (One per line)</label>
                                                            <textarea name="features" class="form-control" rows="3"><?php echo htmlspecialchars($product['features'] ? implode('\n', json_decode($product['features'], true)) : ''); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Specifications (One per line, format: Key: Value)</label>
                                                            <textarea name="specifications" class="form-control" rows="3"><?php echo htmlspecialchars($product['specifications'] ? implode('\n', json_decode($product['specifications'], true)) : ''); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Update Product</button>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_product" value="1">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <input type="text" name="subcategory" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Price (Optional)</label>
                        <input type="number" name="discount_price" class="form-control" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="999">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Badge</label>
                        <select name="badge" class="form-select">
                            <option value="none">None</option>
                            <option value="best_seller">Best Seller</option>
                            <option value="new_arrival">New Arrival</option>
                            <option value="discount">Discount</option>
                            <option value="trending">Trending</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Features (One per line)</label>
                        <textarea name="features" class="form-control" rows="3" placeholder="Feature 1&#10;Feature 2&#10;Feature 3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Specifications (One per line, format: Key: Value)</label>
                        <textarea name="specifications" class="form-control" rows="3" placeholder="Version: 1.0&#10;License: Commercial&#10;Format: ZIP"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product File</label>
                        <input type="file" name="product_file" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Screenshots</label>
                        <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
