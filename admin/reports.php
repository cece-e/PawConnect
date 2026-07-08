<?php
session_start();
include("../database.php");

// Check Admin Login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

// Dashboard Statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM users WHERE role='user'"));

$totalPets = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM pets"));

$totalAdoptions = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM adoption_history"));

$sql = "
SELECT

adoption_history.*,
users.fullname,
pets.pet_name,
pets.species,
pets.breed

FROM adoption_history

INNER JOIN users
ON adoption_history.user_id = users.user_id

INNER JOIN pets
ON adoption_history.pet_id = pets.pet_id

ORDER BY adoption_date DESC
";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>

<head>

<title>Reports</title>

<link rel="stylesheet" href="../style.css">

<style>

@media print{

header,
button,
footer{
display:none;
}

}

</style>

</head>

<body>

<header>

<div class="navbar">

<div class="logo">

<i class="fa-solid fa-paw"></i>

<h2>Admin Reports</h2>

</div>

<nav>

<a href="dashboard.php">Dashboard</a>
<a href="pet_inventory.php">Pet Inventory</a>
<a href="adoption_requests.php">Requests</a>
<a href="manage_users.php">Users</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>System Summary</h2>

<br>

<div class="pet-grid">

<div class="card">

<h3>Total Users</h3>

<h1><?php echo $totalUsers['total']; ?></h1>

</div>

<div class="card">

<h3>Total Pets</h3>

<h1><?php echo $totalPets['total']; ?></h1>

</div>

<div class="card">

<h3>Total Successful Adoptions</h3>

<h1><?php echo $totalAdoptions['total']; ?></h1>

</div>

</div>

<br>

<button
class="btn"
onclick="window.print()">

🖨 Print Report

</button>

</div>

<br>

<div class="card">

<h2>Adoption History</h2>

<br>

<table>

<tr>

<th>ID</th>
<th>Adopter</th>
<th>Pet Name</th>
<th>Species</th>
<th>Breed</th>
<th>Adoption Date</th>

</tr>

<?php

if(mysqli_num_rows($result)>0)
{

while($row=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['history_id']; ?></td>

<td><?php echo $row['fullname']; ?></td>

<td><?php echo $row['pet_name']; ?></td>

<td><?php echo $row['species']; ?></td>

<td><?php echo $row['breed']; ?></td>

<td><?php echo $row['adoption_date']; ?></td>

</tr>

<?php

}

}
else
{

echo "<tr>
<td colspan='6'>
No adoption history found.
</td>
</tr>";

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