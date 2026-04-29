<?php
session_start();
include 'db.php';

// Block access if user is not logged in
if (empty($_SESSION['email'])) {
    header("Location: elogin.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch employee info for header and unique_id
$qry = mysqli_query($conn, "SELECT * FROM employees WHERE email = '$email' LIMIT 1");
if(mysqli_num_rows($qry) == 0){
    session_destroy();
    header("Location: elogin.php");
    exit();
}

// Employee data
$user = mysqli_fetch_assoc($qry);
$unique_id = $user['unique_id'];
$employee_name = $user['ename']; // Assuming column is 'ename'
$employee_email = $user['email'];

// Fetch product categories and products for this unique_id
$categories_result = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE unique_id='$unique_id'");
$products_result = mysqli_query($conn, "SELECT * FROM products WHERE unique_id='$unique_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OrderFlow</title>
<link rel="icon" href="logo.png" type="image/x-icon">


<link rel="stylesheet" href="posorder.css">

<style>
    .payment-options {
    display: flex;
    gap: 15px;
    margin-top: 15px;
    margin-bottom: 18px;
}

.payment-options label {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
}

.payment-options input[type="radio"] {
    accent-color: #ff9800; /* orange color for selected radio */
}

.payment-options label:hover {
    border-color: #ff9800;
    background: #fff8f0;
}

</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <h2>OrderFlow</h2>
    <ul id="categoryList">
        <li><a class="active" data-category="all"><img src="https://img.icons8.com/?size=100&id=35040&format=png&color=000000" alt="Logout" class="logout">All Categories</a></li>
        <?php while($cat = mysqli_fetch_assoc($categories_result)) { ?>
            <li><a  data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                <?php echo htmlspecialchars($cat['category']); ?>
            </a></li>
        <?php } ?>
    </ul>
    <ul>
        <li><a href="elogout.php"><img src=" https://img.icons8.com/?size=100&id=vGj0AluRnTSa&format=png&color=000000" alt="Logout" class="logout">Logout</a></li>
    </ul>
</aside>

<!-- Header -->
<header class="dashboard-header">
    <div class="dashboard-title"><h1>Dashboard</h1></div>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($employee_name); ?></span>
            <span class="user-email"><?php echo htmlspecialchars($employee_email); ?></span>
        </div>
    </div>
</header>

<!-- Main Content -->
    <section class="main-content">
    <div class="products" id="productList">
        <?php while($prod = mysqli_fetch_assoc($products_result)) { ?>
        <div class="product-card" 
             data-category="<?php echo htmlspecialchars($prod['category']); ?>" 
             data-name="<?php echo htmlspecialchars($prod['name']); ?>" 
             data-price="<?php echo $prod['price']; ?>"
             onclick="addToOrder('<?php echo htmlspecialchars($prod['name']); ?>', <?php echo $prod['price']; ?>)">
             
            <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
            <h4><?php echo htmlspecialchars($prod['name']); ?></h4>
            <p>₹<?php echo $prod['price']; ?></p>
        </div>
        <?php } ?>
    </div>

    <div class="checkout">
        <h3>Checkout</h3>
        <div id="orderItems"></div>
        <p><strong>Total: ₹<span id="orderTotal">0</span></strong></p>
        
<div class="payment-options">
    <label>
        <input type="radio" name="payment" value="UPI" checked>
        UPI
    </label>
    <label>
        <input type="radio" name="payment" value="CASH">
        Cash
    </label>
    <label>
        <input type="radio" name="payment" value="CARD">
        Card
    </label>
</div>

        <button class="place-order" onclick="placeOrder()">Place Order</button>
    </div>
    
</section>

<script>
// Order management
let order = [];

function addToOrder(name, price) {
    let existing = order.find(item => item.name === name);
    if (existing) {
        existing.qty++;
    } else {
        order.push({ name, price, qty: 1 });
    }
    renderOrder();
}

function renderOrder() {
    let container = document.getElementById('orderItems');
    container.innerHTML = '';
    let total = 0;

    order.forEach((item, index) => {
        let itemTotal = item.price * item.qty; // show per-item total
        total += itemTotal;

        container.innerHTML += `
        <div class="order-item">
            <span>${item.name}</span>
            <span class="order-inline">
                ( ₹<span id="itemTotal-${index}">${itemTotal.toFixed(2)}</span> )ㅤㅤㅤ
                <input type="number" value="${item.qty}" min="1" max="10" onchange="updateQty(${index}, this.value)">ㅤ
                <button onclick="removeItem(${index})">X</button>
            </span> 
        </div>`;
    });

    document.getElementById('orderTotal').innerText = total.toFixed(2);
}

function updateQty(index, qty) {
    qty = parseInt(qty);

    // Validate the input
    if (isNaN(qty) || qty < 1) qty = 1;
    if (qty > 10) qty = 10;

    // Update and re-render
    order[index].qty = qty;
    renderOrder();
}


function removeItem(index) {
    order.splice(index, 1);
    renderOrder();
}

function placeOrder() {
    if (order.length === 0) {
        alert('Add items to order first!');
        return;
    }

    let paymentType = document.querySelector('input[name="payment"]:checked')?.value;
    if (!paymentType) {
        alert('Please select a payment method.');
        return;
    }

    // Prepare order details as JSON
    let orderData = {
        items: order,
        total: parseFloat(document.getElementById('orderTotal').innerText),
        payment: paymentType
    };

    // Send order to server
    fetch('place_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Order placed successfully!');
            order = [];
            renderOrder();

            // Open bill in new window and print
            document.open();
            document.write(data.billHTML);
            document.close();
            window.print();
        } else {
            alert('Error placing order. Try again.');
        }
    })
    .catch(err => {
        console.error('Order error:', err);
        alert('Network error while placing order.');
    });
}

// Category filter
document.querySelectorAll('#categoryList a').forEach(cat => {
    cat.addEventListener('click', () => {
        document.querySelectorAll('#categoryList a').forEach(c => c.classList.remove('active'));
        cat.classList.add('active');
        let selected = cat.dataset.category;
        document.querySelectorAll('.product-card').forEach(prod => {
            prod.style.display = (selected === 'all' || prod.dataset.category === selected) ? 'block' : 'none';
        });
    });
});

</script>

</body>
</html>
