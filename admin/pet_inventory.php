<?php
session_start();
include("../database.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

// Species list (matches the ENUM in the pets table)
$speciesList = [
    'Dog'    => ['label' => 'Dogs',    'icon' => '🐕', 'class' => 'dog'],
    'Cat'    => ['label' => 'Cats',    'icon' => '🐱', 'class' => 'cat'],
    'Bird'   => ['label' => 'Birds',   'icon' => '🐦', 'class' => 'bird'],
    'Fish'   => ['label' => 'Fish',    'icon' => '🐠', 'class' => 'fish'],
    'Rabbit' => ['label' => 'Rabbits', 'icon' => '🐰', 'class' => 'rabbit'],
];

// Count of Available pets per species
$speciesCounts = [];
foreach($speciesList as $key => $info)
{
    $countRes = mysqli_query($conn,"SELECT COUNT(*) AS total FROM pets WHERE species='$key' AND status='Available'");
    $speciesCounts[$key] = mysqli_fetch_assoc($countRes)['total'];
}

// Check if filtering by species
$selected_species = "";

if(isset($_GET['species']) && array_key_exists($_GET['species'], $speciesList))
{
    $selected_species = $_GET['species'];
}

if($selected_species != "")
{
    $selected_species_esc = mysqli_real_escape_string($conn,$selected_species);
    $result = mysqli_query($conn,"SELECT * FROM pets WHERE species='$selected_species_esc' ORDER BY pet_id DESC");
}
else
{
    $result = mysqli_query($conn,"SELECT * FROM pets ORDER BY pet_id DESC");
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Pet Inventory</title>

<link rel="stylesheet" href="../style.css">

<style>

table img{
    width:80px;
    height:80px;
    object-fit:cover;
    border:5px solid #172B23;
    border-radius:28px;
}
table img:hover{
    transform:scale(1.8);
    box-shadow:0 10px 25px rgba(0,0,0,.35);
    z-index:100;
    position:relative;
}
</style>

</head>

<body>

<header>

<div class="navbar">

<div class="logo">

<i class="fa-solid fa-paw"></i>

<h2>PawConnect</h2>

</div>

<nav>

<a href="dashboard.php">Dashboard</a>
<a href="adoption_requests.php">Requests</a>
<a href="manage_users.php">Users</a>
<a href="reports.php">Reports</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="browse-type">

<p class="section-label">Browse by Type</p>

<h2>Choose Your Pet</h2>

<div class="type-grid">

<?php foreach($speciesList as $key => $info): ?>

<a
class="type-card <?php echo $info['class']; ?><?php echo ($selected_species==$key) ? ' active' : ''; ?>"
href="pet_inventory.php?species=<?php echo urlencode($key); ?>">

<span class="icon"><?php echo $info['icon']; ?></span>

<h3><?php echo $info['label']; ?></h3>

<p><?php echo $speciesCounts[$key]; ?> Available</p>

<span class="arrow">&#8594;</span>

</a>

<?php endforeach; ?>

</div>

</div>

<div class="card">

<h2>Pet Inventory</h2>

<?php if($selected_species != ""): ?>

<br>

<span class="filter-badge">
Showing: <?php echo $speciesList[$selected_species]['label']; ?>
</span>

<a class="clear-filter" href="pet_inventory.php">&times; Clear Filter</a>

<?php endif; ?>

<br>

<a href="add_pet.php" class="btn">

+ Add New Pet

</a>

<br><br>

<table>

<tr>

<th>ID</th>
<th>Image</th>
<th>Name</th>
<th>Species</th>
<th>Breed</th>
<th>Weight</th>
<th>Age</th>
<th>Status</th>
<th>Actions</th>

</tr>

<?php

while($pet=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $pet['pet_id']; ?></td>

<td>

<img
src="../uploads/<?php echo $pet['image']; ?>">

</td>

<td><?php echo $pet['pet_name']; ?></td>

<td><?php echo $pet['species']; ?></td>

<td><?php echo $pet['breed']; ?></td>

<td><?php echo $pet['weight']; ?></td>

<td><?php echo $pet['age']; ?></td>

<td><?php echo $pet['status']; ?></td>

<td>

<a
class="btn btn-green"
href="edit_pet.php?id=<?php echo $pet['pet_id']; ?>">

Edit

</a>

<a
class="btn btn-red"
href="delete_pet.php?id=<?php echo $pet['pet_id']; ?>"
onclick="return confirm('Delete this pet?')">

Delete

</a>

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