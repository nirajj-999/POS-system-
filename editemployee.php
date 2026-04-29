<?php
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("location: alogin.php");
    exit();
}


$qry = mysqli_query($conn, "SELECT * FROM user WHERE unique_id = '$unique_id'");
if ($row = mysqli_fetch_assoc($qry)) {
    $_SESSION['verification_status'] = $row['verification_status'];
    if ($row['verification_status'] != 'Verified') {
        header("location: verify.php");
        exit();
    }
}

$emp_id = $ename = $email = $phone = $address = $password = "";
$errorMessage = $successMessage = "";

// GET request to fetch existing data
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["emp_id"])) {
        header("location: employee.php");
        exit;
    }

    $emp_id = mysqli_real_escape_string($conn, $_GET["emp_id"]);
    $sql = "SELECT * FROM employees WHERE emp_id='$emp_id' AND unique_id='$unique_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: employee.php");
        exit;
    }

    $ename    = $row["ename"];
    $email    = $row["email"];
    $phone    = $row["phone"];
    $address  = $row["address"];
    $password = ""; 
} else {
    
    $emp_id_post   = mysqli_real_escape_string($conn, $_POST['emp_id']);
    $ename_post    = mysqli_real_escape_string($conn, $_POST['name']);
    $email_post    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_post    = mysqli_real_escape_string($conn, $_POST['phone']);
    $address_post  = mysqli_real_escape_string($conn, $_POST['address']);
    $password_post = $_POST['password'];

    $emp_id   = $emp_id_post;
    $ename    = $ename_post;
    $email    = $email_post;
    $phone    = $phone_post;
    $address  = $address_post;
    $password = $password_post;

    do {
        if (empty($emp_id_post) || empty($ename_post) || empty($email_post) || empty($phone_post) || empty($address_post)) {
            $errorMessage = "All fields are required!";
            break;
        }

        if (!preg_match('/^\d{10}$/', $phone_post)) {
            $errorMessage = "Phone number must be exactly 10 digits.";
            break;
        }

        // Check for duplicate email or phone (excluding current emp_id)
        $dupCheck = mysqli_query($conn, "SELECT * FROM employees WHERE (email='$email_post' OR phone='$phone_post') AND emp_id!='$emp_id_post' AND unique_id='$unique_id'");
        if (mysqli_num_rows($dupCheck) > 0) {
            $dup = mysqli_fetch_assoc($dupCheck);
            if ($dup['email'] == $email_post) {
                $errorMessage = "Email already exists. Use a different email.";
            } else {
                $errorMessage = "Phone number already exists. Use a different phone.";
            }
            break;
        }

        // Build update query
        $fields = [
            "ename='$ename_post'",
            "email='$email_post'",
            "phone='$phone_post'",
            "address='$address_post'"
        ];

        if (!empty($password_post)) {
            $hashedPassword = password_hash($password_post, PASSWORD_DEFAULT);
            $fields[] = "password='$hashedPassword'";
        }

        $updateQry = "UPDATE employees SET " . implode(", ", $fields) . " WHERE emp_id='$emp_id_post' AND unique_id='$unique_id'";
        $result = $conn->query($updateQry);

        if (!$result) {
            $errorMessage = "Error: " . $conn->error;
            break;
        }

        $successMessage = "Employee updated successfully!";
        header("location: employee.php");
        exit;

    } while (false);
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
    <h2>Edit Employee</h2>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="emp_id" class="form-label">EMP ID</label>
            <input type="text" class="form-control" id="emp_id" name="emp_id" value="<?php echo htmlspecialchars($emp_id); ?>" readonly>
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
            <label for="password" class="form-label">Password (Leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <button type="submit" class="btn btn-primary">Update Employee</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='employee.php'">Cancel</button>
    </form>
</div>
</body>
</html>
