<?php 
    session_start();
    include 'db.php';

    $unique_id = $_SESSION['unique_id'];
    if(empty($unique_id)){
        header("location: alogin.php");
    }
    $qry = mysqli_query($conn, "SELECT * FROM user WHERE unique_id = '$unique_id'");
    if(mysqli_num_rows($qry) > 0){
        $row = mysqli_fetch_assoc($qry);
        if($row){
            $_SEESSION['verification_status'] = $row['verification_status'];
            if($row['verification_status'] == 'Verified'){
                header("location: dashboard.php");
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
    <link rel="stylesheet" href="style.css">
    <style>
        .otp_field {
            border-radius: 5px;
            font-size: 60px;
            height: 100px;
            width: 100px;
            border: 3px solid #ccc;
            margin: 1%;
            text-align: center;
            font-weight: 600;
            outline: none;
        }
        .fields-input {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
        }
        .otp_field::-webkit-inner-spin-button,
        .otp_field::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .otp_field:valid {
            border-color: #fc8019;
            box-shadow: 0 0 5px rgba(252, 128, 25, 0.5);
        }
        
        .form p{
            text-align: center;
            font-size: 20px;
            color:#9f9f9e
        }
        @media only screen and (max-width: 455px) {
            .otp_field {
                width: 80px;
                height: 80px;
                font-size: 55px;
            }
        }
        </style>
</head>
<body>
    <div class="form">
        <h1>Verify Your Account</h1>
        <p>Please enter the verification code sent to <?php echo htmlspecialchars($row['email']); ?></p>
        <form action="" autocomplete="off" method="post">
           <div class="error-text">Error</div>
           <div class="fields-input">
            <input type="number" name="otp1" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
            <input type="number" name="otp2" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
            <input type="number" name="otp3" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
            <input type="number" name="otp4" class="otp_field" placeholder="0" min="0" max="9" required onpaste="return false">
           </div>
           <div class="submit">
                <input type="submit" value="Verify" name="verify" class="btn">
            </div>
        </form>
    </div>
    <script>
        const otpFields = document.querySelectorAll('.otp_field');
        otpFields.forEach((field, index) => {
            field.addEventListener('input', () => {
                if (field.value.length >= 1 && index < otpFields.length - 1) {
                    otpFields[index + 1].focus();
                }
            });
            field.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && field.value.length === 0 && index > 0) {
                    otpFields[index - 1].focus();
                }
            });
        });


        const form = document.querySelector('.form form'),
        Submitbtn = form.querySelector('.submit .btn'),
        errortxt = form.querySelector('.error-text');

        form.onsubmit = (e) => {
            e.preventDefault(); // Prevent form submission
        }

        Submitbtn.onclick = ()=>{
            let xhr = new XMLHttpRequest(); 
            xhr.open("POST", "otp.php", true); 
            xhr.onload = () => { 
                if(xhr.readyState === XMLHttpRequest.DONE){ 
                    if(xhr.status === 200){ 
                        let data = xhr.response; 
                        if(data === "success"){ 
                            location.href = "dashboard.php";
                        }else{ 
                            errortxt.textContent = data; 
                            errortxt.style.display = "block"; 
                        }
                    }
                }
            }
        
            let formData = new FormData(form); 
            xhr.send(formData); 
        }
        </script>
</body>
</html>