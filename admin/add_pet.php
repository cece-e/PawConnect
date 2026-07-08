<?php
session_start();
include("../database.php");

// Check Admin Login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

$message = "";

if(isset($_POST['add_pet']))
{
    $pet_name = mysqli_real_escape_string($conn,$_POST['pet_name']);
    $species = mysqli_real_escape_string($conn,$_POST['species']);
    $breed = mysqli_real_escape_string($conn,$_POST['breed']);
    $age = mysqli_real_escape_string($conn,$_POST['age']);
    $gender = mysqli_real_escape_string($conn,$_POST['gender']);
    $color = mysqli_real_escape_string($conn,$_POST['color']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);

    // Image Upload
    $image = $_FILES['image']['name'];
    $temp = $_FILES['image']['tmp_name'];

    if($image != "")
    {
        move_uploaded_file($temp,"../uploads/".$image);
    }

    $sql = "INSERT INTO pets
    (pet_name,species,breed,age,gender,color,description,image,status)
    VALUES
    (
    '$pet_name',
    '$species',
    '$breed',
    '$age',
    '$gender',
    '$color',
    '$description',
    '$image',
    'Available'
    )";

    if(mysqli_query($conn,$sql))
    {
        $message = "<div class='success'>Pet added successfully!</div>";
    }
    else
    {
        $message = "<div class='error'>Failed to add pet.</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Pet</title>

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

<a href="dashboard.php">Dashboard</a>
<a href="pet_inventory.php">Pet Inventory</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="auth-container">

<div class="form-box">

<h2>Add New Pet</h2>

<?php echo $message; ?>

<form method="POST" enctype="multipart/form-data">

<label>Pet Name</label>

<input
type="text"
name="pet_name"
required>

<label>Species</label>

<select name="species" required>

<option value="">Select Species</option>
<option>Dog</option>
<option>Cat</option>
<option>Rabbit</option>
<option>Bird</option>

</select>

<label>Breed</label>

<input
type="text"
name="breed"
required>

<label>Age</label>

<input
type="number"
name="age"
min="0"
required>

<label>Gender</label>

<select name="gender" required>

<option>Male</option>
<option>Female</option>

</select>

<label>Color</label>

<input
type="text"
name="color"
required>

<label>Description</label>

<textarea
name="description"
rows="5"
required></textarea>

<label>Pet Image</label>

<input
type="file"
name="image"
accept="image/*"
required>

<br><br>

<button
class="btn"
name="add_pet">

➕Add Pet

</button>

<a
href="pet_inventory.php"
class="btn btn-red">

❌Cancel

</a>

</form>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>