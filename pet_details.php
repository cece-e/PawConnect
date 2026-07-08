<?php
session_start();
include("database.php");

// Check if pet ID exists
if(!isset($_GET['id']))
{
    header("Location: available_pets.php");
    exit();
}

$pet_id = intval($_GET['id']);

$sql = "SELECT * FROM pets WHERE pet_id='$pet_id'";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==0)
{
    echo "Pet not found.";
    exit();
}

$pet = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>

<title>Pet Details</title>

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

</header>

<div class="container">

<div class="card">

<div class="pet-details">

<img src="uploads/<?php echo $pet['image']; ?>">

<div class="info">

<h2><?php echo $pet['pet_name']; ?></h2>

<p><strong>Species:</strong> <?php echo $pet['species']; ?></p>

<p><strong>Breed:</strong> <?php echo $pet['breed']; ?></p>

<p><strong>Age:</strong> <?php echo $pet['age']; ?> year(s)</p>

<p><b>Weight:</b> <?php echo $pet['weight']; ?></p>

<p><strong>Gender:</strong> <?php echo $pet['gender']; ?></p>

<p><strong>Color:</strong> <?php echo $pet['color']; ?></p>

<p><strong>Status:</strong> <?php echo $pet['status']; ?></p>

<p>

<strong>📄 Description:</strong>

<br><br>

<?php echo $pet['description']; ?>

</p>

<br>

<?php

if(isset($_SESSION['user_id']))
{

if($_SESSION['role']=="user")
{

?>

<a class="btn"
href="user/adopt.php?id=<?php echo $pet['pet_id']; ?>">

❤️ Adopt This Pet

</a>

<?php

}
else
{

echo "<button class='btn btn-orange'>Admin Account</button>";

}

}
else
{

?>

<a class="btn"
href="login.php">

Login to Adopt

</a>

<?php

}

?>

</div>

</div>

</div>

</div>

<footer>

<p>© 2026 Pet Adoption System</p>

</footer>

</body>

</html>