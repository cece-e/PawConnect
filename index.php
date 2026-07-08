<?php
session_start();
include("database.php");

$totalPets = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM pets"))['total'];
$availablePets = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM pets WHERE status='Available'"))['total'];
$adoptedPets = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM pets WHERE status='Adopted'"))['total'];
$featuredPets = mysqli_query($conn,"SELECT * FROM pets LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AdoptMe | Pet Adoption System</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="style.css">


</head>

<body>

<!-- ================= NAVBAR ================= -->

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

<a href="#contact">Contact</a>

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

<!-- ================= HERO ================= -->

<section class="hero">

<div class="hero-text">

<span class="hero-tag">

🐾 Give a Pet a Forever Home

</span>

<h1>

Find Your Perfect

<span>Furry Friend</span>

</h1>

<p>

Browse loving pets that are waiting for someone to adopt them.
Your future best friend is only one click away.

</p>

<div class="hero-buttons">

<a href="available_pets.php" class="primary-btn">

Browse Pets

</a>

<?php

if(!isset($_SESSION['user_id']))
{

?>

<a href="register.php" class="secondary-btn">

Get Started

</a>

<?php

}

?>

</div>

</div>

</section>

<!-- ================= STATISTICS ================= -->

<section class="statistics">

<div class="stat-box">

<i class="fa-solid fa-paw"></i>

<h2><?php echo $totalPets; ?></h2>

<p>Total Pets</p>

</div>

<div class="stat-box">

<i class="fa-solid fa-heart"></i>

<h2><?php echo $availablePets; ?></h2>

<p>Available Pets</p>

</div>

<div class="stat-box">

<i class="fa-solid fa-house"></i>

<h2><?php echo $adoptedPets; ?></h2>

<p>Adopted Pets</p>

</div>

</section>

<!-- ================= FEATURED PETS ================= -->

<section class="featured-section">

<div class="section-title">

<h2>Featured Pets</h2>

<p>

Meet some of our adorable pets currently looking for a loving home.

</p>

</div>

<div class="pet-grid">

<?php

while($pet=mysqli_fetch_assoc($featuredPets))
{

?>

<div class="pet-card">

    <div class="pet-image">

        <img src="uploads/<?php echo $pet['image']; ?>" alt="<?php echo $pet['pet_name']; ?>">

        <span class="pet-status">
            <?php echo $pet['status']; ?>
        </span>

    </div>

    <div class="pet-content">

        <h3><?php echo $pet['pet_name']; ?></h3>

        <div class="pet-info">

            <p>
                <i class="fa-solid fa-paw"></i>
                <?php echo $pet['species']; ?>
            </p>

            <p>
                <i class="fa-solid fa-dna"></i>
                <?php echo $pet['breed']; ?>
            </p>

            <p>
                <i class="fa-solid fa-cake-candles"></i>
                <?php echo $pet['age']; ?> Year(s)
            </p>

            <p>
                <i class="fa-solid fa-circle-info"></i>
                <?php echo $pet['status']; ?>
            </p>

        </div>

        <a href="pet_details.php?id=<?php echo $pet['pet_id']; ?>" class="view-btn">

            View Details

            <i class="fa-solid fa-arrow-right"></i>

        </a>

    </div>

</div>

<?php

}

?>

</div>

</section>

<!-- =========================
        ABOUT SECTION
========================= -->

<section class="about-section">

<div class="about-image">

<img src="why.jpg" alt="Adopt a Pet">

</div>

<div class="about-content">

<h2>

Why Adopt?

</h2>

<p>

Adopting a pet gives an animal a second chance at life.
Every pet deserves a loving family and a safe place to call home.

</p>

<div class="about-list">

<div>

<i class="fa-solid fa-heart"></i>

<span>Save a Life</span>

</div>

<div>

<i class="fa-solid fa-house"></i>

<span>Give Them a Forever Home</span>

</div>

<div>

<i class="fa-solid fa-shield-dog"></i>

<span>Support Animal Welfare</span>

</div>

<div>

<i class="fa-solid fa-users"></i>

<span>Build a Loving Family</span>

</div>

</div>

</div>

</section>

<!-- =========================
      HOW IT WORKS
========================= -->

<section class="steps">

<h2>

How Adoption Works

</h2>

<div class="step-container">

<div class="step-box">

<i class="fa-solid fa-magnifying-glass"></i>

<h3>Browse Pets</h3>

<p>

Look through our available pets and choose the one that's right for you.

</p>

</div>

<div class="step-box">

<i class="fa-solid fa-file-signature"></i>

<h3>Apply</h3>

<p>

Create an account and submit your adoption request.

</p>

</div>

<div class="step-box">

<i class="fa-solid fa-user-check"></i>

<h3>Approval</h3>

<p>

Our administrators review your application carefully.

</p>

</div>

<div class="step-box">

<i class="fa-solid fa-paw"></i>

<h3>Take Home</h3>

<p>

Welcome your newest family member home.

</p>

</div>

</div>

</section>

<!-- =========================
        CALL TO ACTION
========================= -->

<section class="cta">

<h2>

Ready to Meet Your New Best Friend?

</h2>

<p>

Browse available pets and start your adoption journey today.

</p>

<a href="available_pets.php" class="primary-btn">

Browse Pets

</a>

</section>

<!-- =========================
        FOOTER
========================= -->

<footer>

<div class="footer-content">

<div>

<h2>🐾 AdoptMe</h2>

<p>

Connecting loving families with pets waiting for their forever homes.

</p>

</div>

<div>

<h3>Quick Links</h3>

<a href="index.php">Home</a>

<a href="available_pets.php">Available Pets</a>

<a href="login.php">Login</a>

<a href="register.php">Register</a>

</div>

<div>

<h3 id = "contact">Contact</h3>

<p>📍 Philippines</p>

<p>📧 adoptme@email.com</p>

<p>📞 +63 912 345 6789</p>

</div>

</div>

<div class="copyright">

<p>

© 2026 AdoptMe | Pet Adoption Management System

</p>

</div>

</footer>

</body>

</html>