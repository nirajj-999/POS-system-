<?php
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("location: alogin.php");
    exit();
}

// Check if emp_id is provided
if (!isset($_GET['emp_id'])) {
    header("location: employee.php");
    exit();
}

$emp_id = mysqli_real_escape_string($conn, $_GET['emp_id']);

// Delete employee from database
$sql = "DELETE FROM employees WHERE emp_id='$emp_id' AND unique_id='$unique_id'";
if ($conn->query($sql) === TRUE) {
    // Redirect to employee page with success message
    header("location: employee.php?msg=deleted");
    exit();
} else {
    // Redirect with error message
    header("location: employee.php?msg=error");
    exit();
}
?>
