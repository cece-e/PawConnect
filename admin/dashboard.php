<?php
session_start();
include("../database.php");

// Check if logged in
if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

// Only admin can access
if($_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

// Dashboard Statistics

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM users WHERE role='user'"));

$totalPets = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM pets"));

$availablePets = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM pets WHERE status='Available'"));

$adoptedPets = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM pets WHERE status='Adopted'"));

$pendingRequests = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Pending'"));

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

?>

<!DOCTYPE html>
<html>

<head>

<title>Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" 
href="../style.css">

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
<a href="pet_inventory.php">Pet Inventory</a>
<a href="adoption_requests.php">Adoption Requests</a>
<a href="manage_users.php">Users</a>
<a href="reports.php">Reports</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<section class="hero">
<div class="hero-text">


<h1>Welcome, <?php echo $_SESSION['fullname']; ?></h1>

<span class="hero-tag">

Manage pets, users, and adoption requests from this dashboard.

</span>
</div>

</div>

</section>
<br>

<div class="pet-grid">

<div class="card">

<h3>👤 Total Users </h3>

<h1><?php echo $totalUsers['total']; ?></h1>

</div>

<div class="card">

<h3>🐾 Total Pets</h3>

<h1><?php echo $totalPets['total']; ?></h1>

</div>

<div class="card">

<h3>🐶 Available Pets</h3>

<h1><?php echo $availablePets['total']; ?></h1>

</div>

<div class="card">

<h3>💕 Adopted Pets 💕</h3>

<h1><?php echo $adoptedPets['total']; ?></h1>

</div>

<div class="card">

<h3>📋 Pending Requests</h3>

<h1><?php echo $pendingRequests['total']; ?></h1>

</div>
</div>

<br>

<div class="browse-type">

<p class="section-label">🔎 Browse by Type</p>

<h2>Choose Your Pet</h2>

<div class="type-grid">

<?php foreach($speciesList as $key => $info): ?>

<a
class="type-card <?php echo $info['class']; ?>"
href="pet_inventory.php?species=<?php echo urlencode($key); ?>">

<span class="icon"><?php echo $info['icon']; ?></span>

<h3><?php echo $info['label']; ?></h3>

<p><?php echo $speciesCounts[$key]; ?> Available</p>

<span class="arrow">&#8594;</span>

</a>

<?php endforeach; ?>

</div>

</div>

<div class="admin-menu">

<div class="menu-card">

<h3>🐶 Pet Inventory</h3>

<p>Add, Edit, Delete Pets</p>

<br>

<a href="pet_inventory.php" class="btn">

Manage Pets

</a>

</div>

<div class="menu-card">

<h3>❤️ Adoption Requests</h3>

<p>Approve or Reject Requests</p>

<br>

<a href="adoption_requests.php" class="btn">

View Requests

</a>

</div>

<div class="menu-card">

<h3>👤 User Management</h3>

<p>View Registered Users</p>

<br>

<a href="manage_users.php" class="btn">

Manage Users

</a>

</div>

<div class="menu-card">

<h3>📊 Reports</h3>

<p>View Adoption Reports</p>

<br>

<a href="reports.php" class="btn">

View Reports

</a>

</div>

</div>

</div>

<footer>

<p>© 2026 Pet Adoption System</p>

</footer>

</body>

</html>