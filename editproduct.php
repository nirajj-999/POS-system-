<?php
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("location: alogin.php");
    exit();
}

// Fetch user and verification
$qry = mysqli_query($conn, "SELECT * FROM user WHERE unique_id = '$unique_id'");
if ($row = mysqli_fetch_assoc($qry)) {
    $_SESSION['verification_status'] = $row['verification_status'];
    if ($row['verification_status'] != 'Verified') {
        header("location: verify.php");
        exit();
    }
}

$product_id = $name = $category = $price = "";
$image = "";
$errorMessage = $successMessage = "";

// GET request to fetch existing product data
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["product_id"])) {
        header("location: product.php");
        exit;
    }

    $product_id = mysqli_real_escape_string($conn, $_GET["product_id"]);
    $sql = "SELECT * FROM products WHERE product_id='$product_id' AND unique_id='$unique_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: product.php");
        exit;
    }

    $name     = $row["name"];
    $category = $row["category"];
    $price    = $row["price"];
    $image    = $row["image"];
} else {
    // POST request to update product
    $product_id_post = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name_post       = mysqli_real_escape_string($conn, $_POST['name']);
    $category_post   = mysqli_real_escape_string($conn, $_POST['category']);
    $price_post      = mysqli_real_escape_string($conn, $_POST['price']);

    do {
        if (empty($product_id_post) || empty($name_post) || empty($category_post) || empty($price_post)) {
            $errorMessage = "All fields are required!";
            break;
        }

        // Check for duplicate product_id excluding current
        $dupCheck = mysqli_query($conn, "SELECT * FROM products WHERE product_id='$product_id_post' AND unique_id='$unique_id' AND product_id!='$product_id_post'");
        if (mysqli_num_rows($dupCheck) > 0) {
            $errorMessage = "Product ID already exists. Use a different ID.";
            break;
        }

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $imageName = time() . "_" . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_post = $targetFile;
            } else {
                $errorMessage = "Failed to upload image.";
                break;
            }
        } else {
            $image_post = $_POST['current_image']; // keep current
        }

        // Update query
        $updateQry = "UPDATE products SET 
                        name='$name_post',
                        category='$category_post',
                        price='$price_post',
                        image='$image_post'
                      WHERE product_id='$product_id_post' AND unique_id='$unique_id'";
        if (!mysqli_query($conn, $updateQry)) {
            $errorMessage = "Error: " . mysqli_error($conn);
            break;
        }

        $successMessage = "Product updated successfully!";
        header("location: product.php");
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
    <h2>Edit Product</h2>

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

    <form action="" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width:600px;">
        <div class="mb-3">
            <label for="product_id" class="form-label">Product ID</label>
            <input type="text" class="form-control" id="product_id" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>">
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price (₹)</label>
            <input type="text" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image (Leave blank to keep current)</label>
            <input type="file" class="form-control" id="image" name="image">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image); ?>">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='product.php'">Cancel</button>
        </div>
    </form>
</div>
</body>
</html>
