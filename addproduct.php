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

$product_id = $name = $category = $price = "";
$image = "";
$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id'] ?? '');
    $name       = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $category   = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $price      = mysqli_real_escape_string($conn, $_POST['price'] ?? '');
    
    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $targetFile;
        } else {
            $errorMessage = "Failed to upload image.";
        }
    } else {
        $image = "uploads/default.png"; // fallback default image
    }

    // Validation
    if (empty($product_id) || empty($name) || empty($category) || empty($price)) {
        $errorMessage = "All fields are required.";
    } else {
        // Check for duplicate product_id
        $checkQry = mysqli_query($conn, "SELECT * FROM products WHERE product_id = '$product_id' AND unique_id='$unique_id'");
        if (mysqli_num_rows($checkQry) > 0) {
            $errorMessage = "Product ID already exists. Please choose a different ID.";
        } else {
            // Insert into database
            $insertQry = "INSERT INTO products (unique_id, product_id, name, category, price, image) 
                          VALUES ('$unique_id', '$product_id', '$name', '$category', '$price', '$image')";
            if (mysqli_query($conn, $insertQry)) {
                
                header("Location: product.php");
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
        <h2 class="text-center mb-4">Add New Product</h2>

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

        <form action="" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width:600px;">
            <div class="mb-3">
                <label for="product_id" class="form-label">Product ID</label>
                <input type="text" class="form-control" id="product_id" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>" placeholder="Enter Product ID">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter Product Name">
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>" placeholder="Enter Product Category">
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price (₹)</label>
                <input type="text" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" placeholder="Enter Product Price">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>

            <!-- Buttons aligned to left -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='product.php'">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
