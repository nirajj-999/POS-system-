<?php
// if user is not verified then redirect to login page
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("location: alogin.php");
    exit();
}

$qry = mysqli_query($conn, "SELECT * FROM user WHERE unique_id = '$unique_id'");
if(mysqli_num_rows($qry) > 0){
    $user = mysqli_fetch_assoc($qry); // renamed to $user
    if($user){
        $_SESSION['verification_status'] = $user['verification_status'];
        if($user['verification_status'] != 'Verified'){
            header("location: verify.php");
            exit();
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
<link rel="stylesheet" href="dashboard.css">
<style>
    
.main-content {
    margin-left: 250px; 
    padding: 30px;
    background-color: #ffffff;
    min-height: 100vh;
}


.main-content .content {
    max-width: 1200px;
    margin: 0 auto;
}


.main-content h2 {
    font-size: 30px;
    font-weight: 600;
    text-align:center;
    margin-bottom: 20px;
    color: #040303ff;
}

/* Add Employee button */
.main-content .btn-primary {
    background-color: #09aa29;
    border: none;
    padding: 8px 15px;
    font-size: 14px;
    border-radius: 5px;
    text-decoration: none;
    color: #fff;
    transition: background 0.3s;
    
    
}

.main-content .btn-primary:hover {
    background-color: #2dc641ff;
}


.main-content table {
    margin-left:12px;
    width: 100%;
    border-collapse:collapse;
    margin-top: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 4px;
    overflow: hidden;
}

/* Table headers */
.main-content table th {
    background-color: #Fc8019;
    color: #fff;
    text-align: center;
    padding: 12px;
    font-weight: 500;
}

/* Table rows */
.main-content table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    color: #555555;
}

/* Table row hover */
.main-content table tr:hover {
    background-color: #f1f1f1;
}

/* Action buttons */
.main-content table .btn-sm {
    padding: 5px 10px;
    font-size: 13px;
    border-radius: 4px;
    text-decoration: none;
}

.main-content table .btn-primary {
    background-color: #28a745;
}

.main-content table .btn-primary:hover {
    background-color: #218838;
}

.main-content table .btn-danger {
    background-color: #dc3545;
    color:#fff;
}

.main-content table .btn-danger:hover {
    background-color: #c82333;
}


@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }

    .main-content table, 
    .main-content table thead, 
    .main-content table tbody, 
    .main-content table th, 
    .main-content table td, 
    .main-content table tr {
        display: block;
    }

    .main-content table tr {
        margin-bottom: 15px;
    }

    .main-content table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    .main-content table td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        width: 50%;
        padding-left: 15px;
        font-weight: bold;
        text-align: left;
    }
}

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
        <li><a href="logout.php?logout_id=<?php echo $unique_id; ?>"> <img src="https://img.icons8.com/?size=100&id=vGj0AluRnTSa&format=png&color=000000" alt="Logout" class="logout">Logout</a></li>
    </ul>
</aside>   

<header class="dashboard-header">
    <div class="dashboard-title">
        <h1>Employees</h1>
    </div>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($user['aname']); ?></span>
            <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
        </div>
    </div>
</header>

<section class="main-content">
    <div class="content my-5">
        <h2>List of Employees</h2>
        <a class="btn btn-primary" href="addemployee.php" role="button">+ Add Employee</a>
        <br><br>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Emp ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Address</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch only employees for the logged-in user
                $sql = "SELECT * FROM employees WHERE unique_id='$unique_id'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0){
                    while($emp = mysqli_fetch_assoc($result)){
                        echo "<tr>
                                <th scope='row'>".htmlspecialchars($emp['emp_id'])."</th>
                                <td>".htmlspecialchars($emp['ename'])."</td>
                                <td>".htmlspecialchars($emp['email'])."</td>
                                <td>".htmlspecialchars($emp['phone'])."</td>
                                <td>".htmlspecialchars($emp['address'])."</td>
                                <td>
                                    <a class='btn btn-sm btn-primary' href='editemployee.php?emp_id=".urlencode($emp['emp_id'])."' role='button'>Edit</a>
                                    <a class='btn btn-sm btn-danger' href='delemployee.php?emp_id=".urlencode($emp['emp_id'])."' role='button'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No employees found</td></tr>";
                }
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
