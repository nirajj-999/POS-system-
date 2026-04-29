<?php
session_start();
include 'db.php';

// Handle AJAX request for login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $uname = $_POST['uname'] ?? '';
    $password = md5($_POST['password']) ?? '';

    if (!empty($uname) && !empty($password)) {
        $sql = mysqli_query($conn, "SELECT * FROM user WHERE uname = '$uname' AND password = '$password'");
        if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
            $_SESSION['unique_id'] = $row['unique_id'];
            $_SESSION['uname'] = $row['uname'];
            $_SESSION['otp'] = $row['otp'];
            echo "success";
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "All input fields are required!";
    }
    exit; // stop further HTML output
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
</head>
<body>
    <div class="form">
        <h1>Admin Login</h1>
        <form action="" method="post">
            <div class="error-text" style="display:none;color:red;">Error</div>
            <div class="input">
                <label>UserName</label><br>
                <input type="text" id="uname" name="uname" placeholder="Enter your Username" required>    
            </div>
            <br>
            <div class="input">
                <label>Password</label><br>
                <input type="password" id="password" name="password" placeholder="Enter your Password" required>    
            </div>
            <br>
            <div class="submit">
                <input type="submit" value="Login" name="submit" class="btn">
            </div>
        </form>
        <div class="link">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            <p>GoTo <a href="index.html">Home</a></p>
        </div>
    </div>

    <script>
        const form = document.querySelector('.form form'),
        Submitbtn = form.querySelector('.submit input'),
        errortxt = form.querySelector('.error-text');

        form.onsubmit = (e) => {
            e.preventDefault(); // prevent normal form submit
        }

        Submitbtn.onclick = ()=> {
            let xhr = new XMLHttpRequest(); 
            xhr.open("POST", "", true); // same file
            xhr.onload = () => { 
                if(xhr.readyState === XMLHttpRequest.DONE){ 
                    if(xhr.status === 200){ 
                        let data = xhr.response.trim(); 
                        if(data === "success"){ 
                            location.href = "dashboard.php";
                        } else { 
                            errortxt.textContent = data; 
                            errortxt.style.display = "block"; 
                        }
                    }
                }
            }
            let formData = new FormData(form); 
            formData.append("ajax", "1"); // flag for PHP
            xhr.send(formData); 
        }
    </script>
</body>
</html>
