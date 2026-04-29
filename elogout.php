<?php
session_start();
include 'db.php';

if(isset($_SESSION['email'])){
    $logout_id = mysqli_real_escape_string($conn, $_GET['logout_id']);
    if(isset($logout_id)){
       session_unset();
       session_destroy();
       header("location: elogin.php");
    }else{
        header("location: pos.php");
    }
}else{
    header("location: elogin.php"); 
} 
?>