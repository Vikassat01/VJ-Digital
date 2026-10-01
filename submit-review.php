<?php
require_once 'includes/auth_functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $review = sanitizeInput($_POST['review']);
    $user_id = $_SESSION['user_id'];
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        header('Location: product-detail.php?id=' . $product_id . '&error=invalid_rating');
        exit();
    }
    
    // Check if user already reviewed this product
    $check = $conn->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $check->bind_param("ii", $product_id, $user_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        header('Location: product-detail.php?id=' . $product_id . '&error=already_reviewed');
        exit();
    }
    
    // Insert review
    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $review);
    $stmt->execute();
    
    // Update product rating
    $update_rating = $conn->prepare("UPDATE products SET rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = ?), rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ?) WHERE id = ?");
    $update_rating->bind_param("iii", $product_id, $product_id, $product_id);
    $update_rating->execute();
    
    header('Location: product-detail.php?id=' . $product_id . '&success=review_submitted');
    exit();
}

header('Location: products.php');
exit();
