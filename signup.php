<?php
session_start();
include_once "db.php";

// Include PHPMailer classes (without Composer)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$aname = $_POST['aname'];
$uname = $_POST['uname'];
$email = $_POST['email'];
$password_raw = $_POST['password'];
$cpassword_raw = $_POST['cpassword'];
$verification_status = "0";

if (!empty($aname) && !empty($uname) && !empty($email) && !empty($password_raw) && !empty($cpassword_raw)) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $sql = mysqli_query($conn, "SELECT email FROM user WHERE email = '{$email}'");
        if (mysqli_num_rows($sql) > 0) {
            echo "$email - This email already exists!";
        } else {
            // Check if username already exists
            $sql_username = mysqli_query($conn, "SELECT uname FROM user WHERE uname = '{$uname}'");
            if (mysqli_num_rows($sql_username) > 0) {
                echo "$uname - This username already exists!";
            } else {
                if ($password_raw === $cpassword_raw) {
                    $random_id = rand(time(), 10000000);
                    $otp = rand(1111, 9999);
                    $password = md5($password_raw);

                    $sql2 = mysqli_query($conn, "INSERT INTO user (unique_id, aname, uname, email, password, otp, verification_status)
                        VALUES ('{$random_id}', '{$aname}', '{$uname}', '{$email}', '{$password}', '{$otp}', '{$verification_status}')");

                    if ($sql2) {
                        $sql3 = mysqli_query($conn, "SELECT * FROM user WHERE email = '{$email}'");
                        if (mysqli_num_rows($sql3) > 0) {
                            $row = mysqli_fetch_assoc($sql3);
                            $_SESSION['unique_id'] = $row['unique_id'];
                            $_SESSION['email'] = $row['email'];
                            $_SESSION['otp'] = $row['otp'];

                            // Send verification email using PHPMailer
                            $mail = new PHPMailer(true);
                            try {
                            // SMTP configuration
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'nirajyadavv86@gmail.com';         // Your Gmail
                            $mail->Password   = 'dtndtfcrpkgpwcve';                // App password only!
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;

                            // Sender and recipient
                            $mail->setFrom('nirajyadavv86@gmail.com', 'OrderFlow');  // Must be same as Username
                            $mail->addAddress($email, $aname); // Recipient

                            // Email content
                            $mail->isHTML(true);
                            $mail->Subject = 'OrderFlow Email Verification';
                            $mail->Body    = "<span style='font-size:24px;'>Dear $aname,</span> 
                                            <br><br>  
                                            <span style='font-size:16px;'>Thank you for choosing our services.</span>  
                                            <br><br>  
                                            <span style='font-size:16px;'>To complete your verification process, please use the One-Time Password (OTP) provided below:</span>  
                                            <br><br>  


                                            <div style='text-align:center; font-size:40px; color:orange; font-weight:bold;'>$otp</div>  

                                            <br><br>  
                                            <span style='font-size:12px;'>For your security, this OTP is valid for a limited time and can only be used once. Please do not share this code with anyone, including our staff.</span>  
                                            <br><br>  
                                            <span style='font-size:12px;'>If you did not request this verification, please disregard this email immediately.</span>  
                                            <br><br>  

                                            
                                                <div style='text-align:center; margin-top:10px;'>  
                                                <strong style='font-size:30px; vertical-align:middle;'>OrderFlow</strong>  
                                            </div>  ";


                            $mail->send();
                            echo "success";
                        } catch (Exception $e) {
                            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        }
                        }
                    } else {
                        echo "Something went wrong!";
                    }
                } else {
                    echo "Password do not match";
                }
            }
        }
    } else {
        echo "$email - This is not a valid email address!";
    }
} else {
    echo "All input fields are required!";
}
?>
