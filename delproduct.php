<?php
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("location: alogin.php");
    exit();
}

// Check if emp_id is provided
if (!isset($_GET['product_id'])) {
    header("location: product.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['product_id']);

// Delete employee from database
$sql = "DELETE FROM products WHERE product_id='$product_id' AND unique_id='$unique_id'";
if ($conn->query($sql) === TRUE) {
    // Redirect to employee page with success message
    header("location: product.php?msg=deleted");
    exit();
} else {
    // Redirect with error message
    header("location: product.php?msg=error");
    exit();
}
?>
