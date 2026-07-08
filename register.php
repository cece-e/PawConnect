<?php
session_start();
include("database.php");

$message = "";

if(isset($_POST['register']))
{
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Check if passwords match
    if($password != $confirm){
        $message = "<div class='error'>Passwords do not match.</div>";
    }
    else{

        // Check username
        $checkUser = mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");

        if(mysqli_num_rows($checkUser) > 0){

            $message = "<div class='error'>Username already exists.</div>";

        }else{

            // Check email
            $checkEmail = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

            if(mysqli_num_rows($checkEmail) > 0){

                $message = "<div class='error'>Email already exists.</div>";

            }else{

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users
                (fullname,email,phone,address,username,password,role)
                VALUES
                ('$fullname','$email','$phone','$address','$username','$hashedPassword','user')";

                if(mysqli_query($conn,$sql))
                {
                    $message = "<div class='success'>
                    Registration Successful! <br>
                    <a href='login.php'>Click here to Login</a>
                    </div>";
                }
                else
                {
                    $message = "<div class='error'>Registration Failed.</div>";
                }

            }

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Register</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>

<header>

<div class="navbar">

<div class="logo">

<i class="fa-solid fa-paw"></i>

<h2>PawConnect</h2>

</div>

<nav>

<a href="index.php">Home</a>
<a href="login.php">Login</a>

</nav>

</header>
<div class="auth-container">
<div class="form-box">

<h2>Create Account</h2>

<?php echo $message; ?>

<form method="POST">

<label>Full Name</label>

<input
type="text"
name="fullname"
required>

<label>Email</label>

<input
type="email"
name="email"
required>

<label>Phone</label>

<input
type="text"
name="phone"
required>

<label>Address</label>

<textarea
name="address"
required></textarea>

<label>Username</label>

<input
type="text"
name="username"
required>

<label>Password</label>

<input
type="password"
name="password"
required>

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
required>

<br><br>

<button
class="btn"
name="register">
Register
</button>

</form>

<br>

<center>

Already have an account?

<br><br>

<a href="login.php">Login Here</a>

</center>

</div>
</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>