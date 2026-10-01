<?php
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

requireLogin();

$order_id = $_GET['order_id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;

// Verify order belongs to user and payment is complete
$stmt = $conn->prepare("SELECT o.*, oi.product_id FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.id = ? AND o.user_id = ? AND o.payment_status = 'completed' AND oi.product_id = ?");
$stmt->bind_param("iii", $order_id, $_SESSION['user_id'], $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: orders.php');
    exit();
}

// Get product file path
$stmt = $conn->prepare("SELECT file_path FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (empty($product['file_path'])) {
    die('File not available for download.');
}

$file_path = '../' . $product['file_path'];

if (!file_exists($file_path)) {
    die('File not found.');
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($file_path);
exit();
?>
