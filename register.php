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

        <h1>Admin Signup</h1>
        <form action="" enctype="multipart/form-data" >
            
            <div class="error-text">Error</div>
        
            <div class="grid-details">
                <div class="input">
                    <label>Name</label><br>
                    <input type="text" id="aname" name="aname" placeholder="Enter your Name" required pattern="[A-Za-z'-'\s]*" title="Only letters and spaces are allowed">    
                </div>
                <br>
                <div class="input">
                    <label>UserName</label><br>
                    <input type="text" id="uname" name="uname" placeholder="Enter your Username" required >    
                </div>
                <br>
                <div class="input">
                    <label>Email</label><br>
                    <input type="email" id="email" name="email" placeholder="Enter your Email" required >    
                </div>
                <br>
                <div class="input">
                    <label>Password</label><br>
                    <input type="password" id="password" name="password" placeholder="Enter your Password" required >    
                </div>
                <br>
                <div class="input">
                    <label>Confirm Password</label><br>
                    <input type="password" id="cpassword" name="cpassword" placeholder="Enter your Password" required >    
                </div>
                <br>
                <div class="submit">
                    <input type="submit" value="Sign Up" name="submit" class="btn">
                </div>
        </form>
        <div class="link">
            <p>Already have an account? <a href="alogin.php">Login</a></p>
            <p>Go to <a href="index.html">Home</a></p>
    </div>
    <script>
        const form = document.querySelector('.form form'),
        Submitbtn = form.querySelector('.submit input'),
        errortxt = form.querySelector('.error-text');

        form.onsubmit = (e) => {
            e.preventDefault(); // Prevent form submission
        }

        Submitbtn.onclick = ()=>{
            let xhr = new XMLHttpRequest(); 
            xhr.open("POST", "signup.php", true); 
            xhr.onload = () => { 
                if(xhr.readyState === XMLHttpRequest.DONE){ 
                    if(xhr.status === 200){ 
                        let data = xhr.response; 
                        if(data === "success"){ 
                            location.href = "verify.php";
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