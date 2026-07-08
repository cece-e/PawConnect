<?php
session_start();
include("database.php");

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

// Get available pets (optionally filtered by species)
if($selected_species != "")
{
    $selected_species_esc = mysqli_real_escape_string($conn,$selected_species);
    $sql = "SELECT * FROM pets WHERE status='Available' AND species='$selected_species_esc' ORDER BY pet_id DESC";
}
else
{
    $sql = "SELECT * FROM pets WHERE status='Available' ORDER BY pet_id DESC";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Available Pets</title>

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
<a href="#">About</a>

<?php

if(isset($_SESSION['user_id']))
{

    if($_SESSION['role']=="admin")
    {
        echo "<a href='admin/dashboard.php'>Dashboard</a>";
    }
    else
    {
        echo "<a href='user/dashboard.php'>Dashboard</a>";
    }

    echo "<a class='register-btn' href='logout.php'>Logout</a>";

}
else
{

    echo "<a href='login.php'>Login</a>";

    echo "<a class='register-btn' href='register.php'>Register</a>";

}

?>

</nav>

</div>

</header>

<section class="hero">

<div class="hero-text">

<span class="hero-tag">

🐾 Give a Pet a Forever Home

</span>

<h1>

Find Your Perfect

</h1>

<span>Furry Friend</span>

<p>

Browse loving pets that are waiting for someone to adopt them.
Your future best friend is only one click away.

</p>

</div>

</section>

<div class="container">

<div class="browse-type">

<p class="section-label">Browse by Type</p>

<h2>Choose Your Pet </h2>

<div class="type-grid">

<?php foreach($speciesList as $key => $info): ?>

<a
class="type-card <?php echo $info['class']; ?><?php echo ($selected_species==$key) ? ' active' : ''; ?>"
href="available_pets.php?species=<?php echo urlencode($key); ?>">

<span class="icon"><?php echo $info['icon']; ?></span>

<h3><?php echo $info['label']; ?></h3>

<p><?php echo $speciesCounts[$key]; ?> Available</p>

<span class="arrow">&#8594;</span>

</a>

<?php endforeach; ?>

</div>

</div>

<h2>Available Pets</h2>

<?php if($selected_species != ""): ?>

<br>

<span class="filter-badge">
Showing: <?php echo $speciesList[$selected_species]['label']; ?>
</span>

<a class="clear-filter" href="available_pets.php">&times; Clear Filter</a>

<?php endif; ?>

<br>

<?php

if(mysqli_num_rows($result) > 0)
{
?>

<div class="pet-grid">

<?php

while($pet = mysqli_fetch_assoc($result))
{

?>

<div class="pet-card">

<img src="uploads/<?php echo $pet['image']; ?>" alt="<?php echo $pet['pet_name']; ?>">

<div class="content">

<h3><?php echo $pet['pet_name']; ?></h3>

<p><strong>Species:</strong> <?php echo $pet['species']; ?></p>

<p><strong>Breed:</strong> <?php echo $pet['breed']; ?></p>

<p><strong>Age:</strong> <?php echo $pet['age']; ?> year(s)</p>

<p><b>Weight:</b> <?php echo $pet['weight']; ?></p>

<p><strong>Gender:</strong> <?php echo $pet['gender']; ?></p>

<p><strong>Status:</strong> <?php echo $pet['status']; ?></p>

<br>

<a class="btn"
href="pet_details.php?id=<?php echo $pet['pet_id']; ?>">

🔎 View Details

</a>

</div>

</div>

<?php

}

?>

</div>

<?php

}
else
{

echo "<div class='card'><h3>No pets available for adoption.</h3></div>";

}

?>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>