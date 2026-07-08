<?php
session_start();
include("../database.php");

// Check login
if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

// Only users
if($_SESSION['role'] != "user")
{
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Update Profile
if(isset($_POST['update']))
{
    $fullname = mysqli_real_escape_string($conn,$_POST['fullname']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);

    // Check duplicate email
    $check = mysqli_query($conn,"
    SELECT *
    FROM users
    WHERE email='$email'
    AND user_id != '$user_id'
    ");

    if(mysqli_num_rows($check)>0)
    {
        $message = "<div class='error'>Email already exists.</div>";
    }
    else
    {
        $sql = "
        UPDATE users
        SET
        fullname='$fullname',
        email='$email',
        phone='$phone',
        address='$address'
        WHERE user_id='$user_id'
        ";

        if(mysqli_query($conn,$sql))
        {
            $_SESSION['fullname'] = $fullname;

            $message = "<div class='success'>
            Profile updated successfully.
            </div>";
        }
        else
        {
            $message = "<div class='error'>
            Failed to update profile.
            </div>";
        }
    }
}

// Load user information
$user = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM users
WHERE user_id='$user_id'
"));
?>

<!DOCTYPE html>
<html>

<head>

<title>My Profile</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<div class="navbar">

<div class="logo">

<i class="fa-solid fa-paw"></i>

<h2>PawConnect</h2>

</div>

<nav>

<a href="../index.php">Home</a>
<a href="dashboard.php">Dashboard</a>
<a href="../available_pets.php">Available Pets</a>
<a href="my_requests.php">My Requests</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="auth-container">

<div class="form-box">

<h2>My Profile</h2>

<?php echo $message; ?>

<form method="POST">

<label>Full Name</label>

<input
type="text"
name="fullname"
value="<?php echo $user['fullname']; ?>"
required>

<label>Email</label>

<input
type="email"
name="email"
value="<?php echo $user['email']; ?>"
required>

<label>Phone</label>

<input
type="text"
name="phone"
value="<?php echo $user['phone']; ?>"
required>

<label>Address</label>

<textarea
name="address"
required><?php echo $user['address']; ?></textarea>

<label>Username</label>

<input
type="text"
value="<?php echo $user['username']; ?>"
readonly>

<br><br>

<button
class="btn"
name="update">

Update Profile

</button>

</form>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>