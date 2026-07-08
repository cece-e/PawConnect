<?php
session_start();
include("../database.php");

// Check Admin Login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

/* ===========================
   DELETE USER
=========================== */

if(isset($_GET['delete']))
{
    $user_id = intval($_GET['delete']);

    // Prevent deleting yourself
    if($user_id != $_SESSION['user_id'])
    {
        mysqli_query($conn,"
        DELETE FROM users
        WHERE user_id='$user_id'
        AND role='user'
        ");
    }

    header("Location: manage_users.php");
    exit();
}

/* ===========================
   GET USERS
=========================== */

$result = mysqli_query($conn,"
SELECT *
FROM users
ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Users</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<div class="navbar">

<div class="logo">

<i class="fa-solid fa-paw"></i>

<h2>Admin Panel</h2>

</div>

<nav>

<a href="dashboard.php">Dashboard</a>
<a href="pet_inventory.php">Pet Inventory</a>
<a href="adoption_requests.php">Requests</a>
<a href="reports.php">Reports</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>Manage Users</h2>

<br>

<table>

<tr>

<th>ID</th>
<th>Full Name</th>
<th>Email</th>
<th>Phone</th>
<th>Username</th>
<th>Role</th>
<th>Registered</th>
<th>Action</th>

</tr>

<?php

while($user=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $user['user_id']; ?></td>

<td><?php echo $user['fullname']; ?></td>

<td><?php echo $user['email']; ?></td>

<td><?php echo $user['phone']; ?></td>

<td><?php echo $user['username']; ?></td>

<td><?php echo ucfirst($user['role']); ?></td>

<td><?php echo $user['created_at']; ?></td>

<td>

<?php

if($user['role']=="admin")
{
    echo "<strong>Administrator</strong>";
}
elseif($user['user_id']==$_SESSION['user_id'])
{
    echo "<strong>Your Account</strong>";
}
else
{

?>

<a
class="btn btn-red"
href="manage_users.php?delete=<?php echo $user['user_id']; ?>"
onclick="return confirm('Delete this user account?')">

Delete

</a>

<?php

}

?>

</td>

</tr>

<?php

}

?>

</table>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>