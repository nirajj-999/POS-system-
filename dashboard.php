<?php
// dashboard.php
session_start();
include 'db.php';

$unique_id = $_SESSION['unique_id'] ?? '';
if (empty($unique_id)) {
    header("Location: alogin.php");
    exit();
}

// Fetch logged-in user
$qry = mysqli_query($conn, "SELECT * FROM `user` WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' LIMIT 1");
if (mysqli_num_rows($qry) == 0) {
    header("Location: alogin.php");
    exit();
}
$row = mysqli_fetch_assoc($qry);

// Check verification
if (($row['verification_status'] ?? '') !== 'Verified') {
    header("Location: verify.php");
    exit();
}

// ---- Month Filter ----
$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear  = date('Y');

// ---- Totals for Selected Month (logged-in user only) ----
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount),0) AS total_sales 
    FROM `orders` 
    WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' 
      AND MONTH(order_time)=$selectedMonth 
      AND YEAR(order_time)=$selectedYear
"))['total_sales'];

$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_orders 
    FROM `orders` 
    WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' 
      AND MONTH(order_time)=$selectedMonth 
      AND YEAR(order_time)=$selectedYear
"))['total_orders'];

$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_products 
    FROM `products` 
    WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' 
"))['total_products'];

// ---- Sales Data for Chart (logged-in user only) ----
$monthlySales = [];
$res = mysqli_query($conn, "
    SELECT DATE(order_time) AS day, SUM(total_amount) AS daily_sales
    FROM `orders`
    WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' 
      AND MONTH(order_time)=$selectedMonth 
      AND YEAR(order_time)=$selectedYear
    GROUP BY day 
    ORDER BY day
");
while($r = mysqli_fetch_assoc($res)) $monthlySales[] = $r;

// ---- Top Selling Products (logged-in user only) ----
$productCounts = [];
$res = mysqli_query($conn, "
    SELECT order_details 
    FROM `orders` 
    WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' 
      AND MONTH(order_time)=$selectedMonth 
      AND YEAR(order_time)=$selectedYear
");
while($rowOrd = mysqli_fetch_assoc($res)){
    $items = json_decode($rowOrd['order_details'], true);
    if(is_array($items)){
        foreach($items as $i){
            $name = $i['name'] ?? '';
            $qty  = (int)($i['qty'] ?? 1);
            if(!$name) continue;
            $productCounts[$name] = ($productCounts[$name] ?? 0) + $qty;
        }
    }
}
arsort($productCounts);
$topProducts = array_slice($productCounts, 0, 5, true);
$topLabels = array_keys($topProducts);
$topData   = array_values($topProducts);

// ---- Recent 15 Orders (logged-in user only) ----
$recentOrders = [];
$res = mysqli_query($conn, "
    SELECT * 
    FROM `orders` 
    WHERE unique_id = '".mysqli_real_escape_string($conn, $unique_id)."' 
    ORDER BY order_time DESC 
    LIMIT 15
");
while($r = mysqli_fetch_assoc($res)) $recentOrders[] = $r;
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OrderFlow</title>
<link rel="icon" href="logo.png" type="image/x-icon">
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="chart.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>

<body>
<aside class="sidebar">
    <h2>OrderFlow</h2>
    <ul>
        <li><a href="dashboard.php"><img src="https://img.icons8.com/?size=100&id=Yj5svDsC4jQA&format=png&color=000000" class="icon">Dashboard</a></li>
        <li><a href="product.php"><img src="https://img.icons8.com/?size=100&id=85058&format=png&color=000000" class="icon">Products</a></li>
        <li><a href="employee.php"><img src="https://img.icons8.com/?size=100&id=85470&format=png&color=000000" class="icon">Employees</a></li>
        <li><a href="orderhistory.php"><img src="https://img.icons8.com/?size=100&id=100123&format=png&color=000000" class="icon">Order History</a></li>
    </ul>
    <ul>
        <li><a href="logout.php?logout_id=<?php echo $unique_id?>"><img src="https://img.icons8.com/?size=100&id=vGj0AluRnTSa&format=png&color=000000" class="logout">Logout</a></li>
    </ul>
</aside>   

<header class="dashboard-header">
    <div class="dashboard-title"><h1>Dashboard</h1></div>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($row['aname']); ?></span>
            <span class="user-email"><?php echo htmlspecialchars($row['email']); ?></span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="filter-period">
        <label for="monthSelect">Select Month:</label>
        <select id="monthSelect">
            <?php
            for($m=1;$m<=12;$m++){
                $monthName = date('F', mktime(0,0,0,$m,1));
                $selected = ($m == $selectedMonth) ? 'selected' : '';
                echo "<option value='$m' $selected>$monthName</option>";
            }
            ?>
        </select>
    </div>

    <div class="cards">
        <div class="card">
            <i class="fa fa-rupee-sign"></i>
            <h2>Total Sales</h2>
            <div class="value" id="totalSales">₹<?php echo number_format($totalSales,2);?></div>
        </div>
        <div class="card">
            <i class="fa fa-shopping-cart"></i>
            <h2>Total Orders</h2>
            <div class="value" id="totalOrders"><?php echo $totalOrders;?></div>
        </div>
        <div class="card">
            <i class="fa fa-cubes"></i>
            <h2>Total Products</h2>
            <div class="value" id="totalProducts"><?php echo $totalProducts;?></div>
        </div>
    </div>

    <div class="charts">
        <div class="chart-box">
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Top 5 Selling Products</h3>
            <canvas id="productChart"></canvas>
        </div>
    </div>

    <div class="recent-orders">
        <h3>Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Details</th>
                    <th>Total Amount</th>
                    <th>Order Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recentOrders as $ord): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ord['order_id']); ?></td>
                    <td>
<?php 
$items = json_decode($ord['order_details'], true);
if(is_array($items)){
    foreach($items as $item){
        $name  = htmlspecialchars($item['name'] ?? '');
        $qty   = (int)($item['qty'] ?? 1);
        $price = number_format((float)($item['price'] ?? 0), 2);
        echo "$name (Qty: $qty)<br>";
    }
} else {
    echo "-"; // show dash if no items
}
?>
</td>
                    <td>₹<?php echo number_format($ord['total_amount'],2); ?></td>
                    <td><?php echo date("d M Y H:i", strtotime($ord['order_time'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
const monthlySales = <?php echo json_encode($monthlySales); ?>;
const topLabels = <?php echo json_encode($topLabels); ?>;
const topData = <?php echo json_encode($topData); ?>;

function mapToSeries(arr) {
    return { labels: arr.map(x=>x.day), data: arr.map(x=>Number(x.daily_sales)) };
}

let salesSeries = mapToSeries(monthlySales);

let salesChart = new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: { 
        labels: salesSeries.labels, 
        datasets:[{
            label:'Sales (₹)', 
            data: salesSeries.data, 
            borderColor:'#3498db', 
            backgroundColor:'rgba(52,152,219,0.2)', 
            fill:true, 
            tension:0.3
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        interaction: {
            mode: 'nearest', // shows tooltip for nearest point
            intersect: false
        },
        plugins:{
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(context) {
                        return '₹' + context.parsed.y.toLocaleString(); // formatted value
                    }
                }
            },
            legend:{ display:true }
        },
        scales:{ 
            x:{offset: true, ticks:{ color:'#000000ff' ,maxRotation: 45, minRotation: 45  }},
            y:{ ticks:{ color:'#24a201ff',maxRotation: 45, minRotation: 45 }}
        }
    }
});

let productChart = new Chart(document.getElementById('productChart'), {
    type:'pie',
    data:{ labels:topLabels, datasets:[{ 
        data:topData, 
        backgroundColor:['#f375e4ff','#e74c3c','#2ecc71','#f1c40f','#9b59b6'],     
    }]},
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{ position:'bottom',labels: {color: '#000000ff'},
            font: {
                        family: 'Arial',    
                        size: 16,           
                        weight: 'bold'      
                    }
                },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.raw || 0;
                        return label + ': ' + value;
                    }
                }
            },
            datalabels: {
                color: 'Black',       // set the color of the value text
                font: {
                    family: 'Arial',
                    style: 'italic',
                    weight: 'bold',
                    size: 14
                },
                formatter: function(value, context) {
                    return value;      // customize displayed value if needed
                }
            }
        }
        
    },
    plugins:[ChartDataLabels]
});


document.getElementById('monthSelect').addEventListener('change', function(){
    const val = this.value;
    window.location.href = "?month=" + val;
});
</script>
</body>
</html>
