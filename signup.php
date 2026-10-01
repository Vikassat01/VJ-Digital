<?php
$pageTitle = 'Sign Up';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Please login.';
                header('Location: login.php?success=registered');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!-- Mobile Navigation Bar (Bottom) -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-bottom d-md-none">
    <div class="container-fluid justify-content-around">
        <a class="nav-link" href="index.php"><i class="fas fa-home"></i><br><small>Home</small></a>
        <a class="nav-link" href="products.php"><i class="fas fa-box"></i><br><small>Products</small></a>
        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i><br><small>Cart</small></a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i><br><small>Profile</small></a>
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
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Sign Up</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-outline mb-4">
                            <input type="text" id="name" name="name" class="form-control" required>
                            <label class="form-label" for="name">Full Name</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="email" id="email" name="email" class="form-control" required>
                            <label class="form-label" for="email">Email address</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <label class="form-label" for="password">Password</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block w-100 mb-3">Sign Up</button>
                        
                        <div class="text-center">
                            <p>Already have an account? <a href="login.php">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
