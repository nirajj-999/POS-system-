<?php
session_start();
include 'db.php';

if(empty($_SESSION['email'])){
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    header("Location: elogin.php");
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if(!$input){
    header("Location: elogin.php");
    exit();
}

$email = $_SESSION['email'];
$qry = mysqli_query($conn, "SELECT * FROM employees WHERE email='$email' LIMIT 1");
if(mysqli_num_rows($qry) == 0){
    header("Location: elogin.php");
    exit();
}
$employee = mysqli_fetch_assoc($qry);

$unique_id = $employee['unique_id'];     
$employee_id = $employee['emp_id'];      
$orderDetails = json_encode($input['items']); 
$totalAmount = $input['total'];
$paymentType = $input['payment'];
$stmt = $conn->prepare("INSERT INTO orders (unique_id, employee_id, order_details, total_amount, payment_type) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssds", $unique_id, $employee_id, $orderDetails, $totalAmount, $paymentType);

if($stmt->execute()){
    $orderId = $stmt->insert_id; 

    $billHTML = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>OrderFlow</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; color: #333; background: #f9f9f9; }
            .bill-container { max-width: 650px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
            .bill-header { text-align: center; margin-bottom: 20px; }
            .bill-header img { max-width: 100px; margin-bottom: 10px; }
            .bill-header h2 { margin: 0; font-size: 26px; color: #000000ff; }
            .details p { margin: 4px 0; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            table, th, td { border: 1px solid #ccc; }
            th, td { padding: 10px; text-align: center; }
            th { background-color: #222020ff; color: #fff; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            tr.total-row { background-color: #cfceceff; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; font-size: 14px; color: #555; }
            .buttons { text-align: center; margin-top: 20px; }
            .buttons button { padding: 8px 16px; margin: 0 10px; font-size: 14px; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; }
            .btn-print { background-color: #4CAF50; color: #fff; }
            .btn-close { background-color: #f44336; color: #fff; }
            .buttons button:hover { opacity: 0.8; }
            @media print {
                .buttons { display: none; }
                body { margin: 0; }
                .bill-container { box-shadow: none; border: none; }
            }
        </style>
        <script>
            function printBill(){
                window.print();
                window.onafterprint = function(){
                    window.location.href = "pos.php";
                }
            }
            function closeBill(){
                window.location.href = "pos.php";
            }
        </script>
    </head>
    <body>
        <div class="bill-container">
            <div class="bill-header">
                <img src="logo.png" alt="Logo">
                <h2>Order Receipt</h2>
            </div>
            <div class="details">
                <p><strong>Order ID:</strong> '.$orderId.'</p>
                <p><strong>Employee Name:</strong> '.htmlspecialchars($employee['ename']).'</p>
                <p><strong>Payment Type:</strong> '.htmlspecialchars($paymentType).'</p>
                <p><strong>Order Time:</strong> '.date("Y-m-d  [ H:i:s ]").'</p>
            </div>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>';

    foreach($input['items'] as $item){
        $subtotal = $item['price'] * $item['qty'];
        $billHTML .= '
                <tr>
                    <td>'.htmlspecialchars($item['name']).'</td>
                    <td>'.$item['qty'].'</td>
                    <td>₹'.$item['price'].'</td>
                    <td>₹'.$subtotal.'</td>
                </tr>';
    }

    $billHTML .= '
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td>₹'.$totalAmount.'</td>
                </tr>
            </table>
            <div class="footer">Thank you for your order!</div>
            <div class="buttons">
                <button class="btn-print" onclick="printBill()">Print</button>
                <button class="btn-close" onclick="closeBill()">Close</button>
            </div>
        </div>
    </body>
    </html>';

    echo json_encode(['status'=>'success','billHTML'=>$billHTML]);
}else{
    echo json_encode(['status'=>'error','message'=>'Database error']);
}
