<?php 
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'];

if(empty($unique_id)){
    header("location: alogin.php");
    exit();
}

$qry = mysqli_query($conn, "SELECT * FROM user WHERE unique_id = '$unique_id'");
if(mysqli_num_rows($qry) > 0){
    $row = mysqli_fetch_assoc($qry);
    if($row){
        $_SESSION['verification_status'] = $row['verification_status'];
        if($row['verification_status'] != 'Verified'){
            header("location: verify.php");
            exit();
        }
    }
}

// Fetch orders for this user (current unique_id)
$order_qry = mysqli_query($conn, "SELECT * FROM orders WHERE unique_id='$unique_id' ORDER BY order_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OrderFlow</title>
<link rel="icon" href="logo.png" type="image/x-icon">
<link rel="stylesheet" href="dashboard.css">
<style>
    .orders-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .orders-table th, .orders-table td { border: 1px solid #ccc; padding: 10px; text-align: center; }
    .orders-table th { background-color: #ff9900ff; color: #fff; }
    .orders-table tr:nth-child(even) { background-color: #f9f9f9; }
    .items-list { text-align: left; }
</style>
</head>
<body>

<aside class="sidebar">
    <h2>OrderFlow</h2>
    <ul>
        <li><a href="dashboard.php"><img src="https://img.icons8.com/?size=100&id=Yj5svDsC4jQA&format=png&color=000000" alt="Dashboard" class="icon">Dashboard</a></li>
        <li><a href="product.php"><img src="https://img.icons8.com/?size=100&id=85058&format=png&color=000000" alt="Products" class="icon">Products</a></li>
        <li><a href="employee.php"><img src="https://img.icons8.com/?size=100&id=85470&format=png&color=000000" alt="Employee" class="icon">Employees</a></li>
        <li><a href="orderhistory.php"><img src="https://img.icons8.com/?size=100&id=100123&format=png&color=000000" alt="Order History" class="icon">Order History</a></li>
    </ul>
    <ul>
        <li><a href="logout.php?logout_id=<?php echo $unique_id?>"> <img src=" https://img.icons8.com/?size=100&id=vGj0AluRnTSa&format=png&color=000000" alt="Logout" class="logout">Logout</a></li>
    </ul>
</aside>   

<header class="dashboard-header">
    <div class="dashboard-title">
        <h1>Order History</h1>
    </div>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($row['aname']); ?></span>
            <span class="user-email"><?php echo htmlspecialchars($row['email']); ?></span>
        </div>
    </div>
</header>

<section class="main-content">
    <table class="orders-table">
        <tr>
            <th>Order ID</th> 
            <th>Employee ID</th>
            <th>Total Amount</th>
            <th>Payment Type</th>
            <th>Order Time</th>
            <th>Items</th>
        </tr>
        <?php if(mysqli_num_rows($order_qry) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($order_qry)): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['employee_id']; ?></td>
                    <td>₹<?php echo $order['total_amount']; ?></td>
                    <td><?php echo htmlspecialchars($order['payment_type']); ?></td>
                    <td><?php echo $order['order_time']; ?></td>
                    <td class="items-list">
                        <?php 
                            $items = json_decode($order['order_details'], true);
                            foreach($items as $item){
                                echo htmlspecialchars($item['name']).' (Qty: '.$item['qty'].', ₹'.$item['price'].')<br>';
                            }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No orders found.</td></tr>
        <?php endif; ?>
    </table>
</section>

</body>
</html>
