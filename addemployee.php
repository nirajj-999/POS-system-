<?php
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("location: alogin.php");
    exit();
}

$qry = mysqli_query($conn, "SELECT * FROM user WHERE unique_id = '$unique_id'");
if (mysqli_num_rows($qry) > 0) {
    $row = mysqli_fetch_assoc($qry);
    if ($row) {
        $_SESSION['verification_status'] = $row['verification_status'];
        if ($row['verification_status'] != 'Verified') {
            header("location: verify.php");
            exit();
        }
    }
}

$emp_id = $ename = $email = $phone = $address = $password = "";
$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id   = mysqli_real_escape_string($conn, $_POST['emp_id'] ?? '');
    $ename    = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone    = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $address  = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($emp_id) || empty($ename) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $errorMessage = "All fields are required.";
    }  elseif (!preg_match('/^\d{10}$/', $phone)) {
        $errorMessage = "Phone number must be exactly 10 digits.";
    }else {
        // Check duplicates in the database
        $duplicateCheck = mysqli_query($conn, "SELECT * FROM employees WHERE (emp_id='$emp_id' OR email='$email' OR phone='$phone') AND unique_id='$unique_id'");
        
        if (mysqli_num_rows($duplicateCheck) > 0) {
            $row = mysqli_fetch_assoc($duplicateCheck);
            if ($row['emp_id'] == $emp_id) {
                $errorMessage = "Employee with this EMP ID already exists.";
            } elseif ($row['email'] == $email) {
                $errorMessage = "Email already exists. Please use a different email.";
            } elseif ($row['phone'] == $phone) {
                $errorMessage = "Phone number already exists. Please use a different phone number.";
            }
        } else {
            // Insert new employee
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertQry = "INSERT INTO employees (emp_id, ename, email, phone, address, password, unique_id) 
                          VALUES ('$emp_id', '$ename', '$email', '$phone', '$address', '$hashedPassword', '$unique_id')";
            if (mysqli_query($conn, $insertQry)) {
                // Redirect to employee.php after success
                header("Location: employee.php");
                exit();
            } else {
                $errorMessage = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrderFlow</title>
    <link rel="icon" href="logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="emp.css">
</head>
<body>
    <div class="container my-5">
        <h2>Add New Employee</h2>

        <!-- Error Message -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $successMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="emp_id" class="form-label">EMP ID</label>
                <input type="text" class="form-control" id="emp_id" name="emp_id" value="<?php echo htmlspecialchars($emp_id); ?>" >
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($ename); ?>" >
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" >
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" >
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" >
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" >
            </div>

            <button type="submit" class="btn btn-primary">Add Employee</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='employee.php'">Cancel</button>
        </form>
    </div>
</body>
</html>
