<?php
session_start();
include("../database.php");

// Check if logged in
if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

// Only users can access
if($_SESSION['role'] != "user")
{
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Total Requests
$request = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM adoption_requests WHERE user_id='$user_id'"));

// Approved Requests
$approved = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM adoption_requests WHERE user_id='$user_id' AND status='Approved'"));

// Pending Requests
$pending = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM adoption_requests WHERE user_id='$user_id' AND status='Pending'"));

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

<title>User Dashboard</title>

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
<a href="../available_pets.php">Available Pets</a>
<a href="my_requests.php">My Requests</a>
<a href="profile.php">Profile</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<section class="hero">
<div class="container">

<div class="card">

<h2>Welcome,
<?php echo $_SESSION['fullname']; ?> 👋</h2>

<p>

Manage your adoption requests from this dashboard.

</p>

</div>

<div class="pet-grid">

<div class="card">

<h3>Total Requests</h3>

<h1><?php echo $request['total']; ?></h1>

</div>

<div class="card">

<h3>Approved</h3>

<h1><?php echo $approved['total']; ?></h1>

</div>

<div class="card">

<h3>Pending</h3>

<h1><?php echo $pending['total']; ?></h1>

</div>

</div>
</section>
<br>

<br>

<div class="browse-type">

<p class="section-label">Browse by Type 📝</p>

<h2>Choose Your Pet </h2>

<div class="type-grid">

<?php foreach($speciesList as $key => $info): ?>

<a
class="type-card <?php echo $info['class']; ?>"
href="../available_pets.php?species=<?php echo urlencode($key); ?>">

<span class="icon"><?php echo $info['icon']; ?></span>

<h3><?php echo $info['label']; ?></h3>

<p><?php echo $speciesCounts[$key]; ?> Available</p>

<span class="arrow">&#8594;</span>

</a>

<?php endforeach; ?>

</div>

</div>

<br>

<div class="card">

<h2>Quick Actions</h2>

<br>

<a class="btn" href="../available_pets.php">

🐶 Browse Available Pets

</a>

&nbsp;

<a class="btn btn-green" href="my_requests.php">

📋 View My Requests

</a>

&nbsp;

<a class="btn btn-orange" href="profile.php">

👤 Edit Profile

</a>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>