<?php
session_start();
include("database.php");

$message = "";

if(isset($_POST['login']))
{
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $static_admin_user = "admin";
    $static_admin_pass = "admin123";

    if($username === $static_admin_user && $password === $static_admin_pass){
        $_SESSION['user_id'] = 0; // static ID
        $_SESSION['fullname'] = "System Administrator";
        $_SESSION['username'] = $static_admin_user;
        $_SESSION['role'] = "admin";

        header("Location: admin/dashboard.php");
        exit();
    }

    // --- Normal DB login check ---
    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1)
    {
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password']))
        {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if($user['role']=="admin")
            {
                header("Location: admin/dashboard.php");
            }
            else
            {
                header("Location: user/dashboard.php");
            }
            exit();
        }
        else
        {
            $message = "<div class='error'>Incorrect Password!</div>";
        }
    }
    else
    {
        $message = "<div class='error'>Username does not exist!</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>PawConnect: Login</title>

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

<a href="available_pets.php">Available Pets</a>

<a href="#">About</a>

<a href="#">Contact</a>

</nav>

</header>
    <div class="auth-container">
        <div class="form-box">

            <h2>✨Welcome Back✨</h2>

            <p class="subtitle">
                Sign in to continue your adoption journey.
            </p>

        <?php echo $message; ?>
        <form method="POST">
            <label>Username</label>

        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <br><br>

        <button class="btn" name="login">
        Login
        </button>

        </form>

        <br>

        <center>

        Don't have an account?

        <br><br>

        <a href="register.php">Register Here</a>

        </center>

        </div>
    </div>

        <footer>

<p>© PawConnect</p>

        </footer>

</body>
</html>