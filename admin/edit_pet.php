<?php
session_start();
include("../database.php");

// Check Admin
if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin")
{
    header("Location: ../login.php");
    exit();
}

// Check ID
if(!isset($_GET['id']))
{
    header("Location: pet_inventory.php");
    exit();
}

$pet_id = intval($_GET['id']);

$result = mysqli_query($conn,"SELECT * FROM pets WHERE pet_id='$pet_id'");

if(mysqli_num_rows($result)==0)
{
    die("Pet not found.");
}

$pet = mysqli_fetch_assoc($result);

$message = "";

if(isset($_POST['update_pet']))
{

    $pet_name = mysqli_real_escape_string($conn,$_POST['pet_name']);
    $species = mysqli_real_escape_string($conn,$_POST['species']);
    $breed = mysqli_real_escape_string($conn,$_POST['breed']);
    $age = mysqli_real_escape_string($conn,$_POST['age']);
    $gender = mysqli_real_escape_string($conn,$_POST['gender']);
    $color = mysqli_real_escape_string($conn,$_POST['color']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $status = mysqli_real_escape_string($conn,$_POST['status']);

    $image = $pet['image'];

    // New Image
    if($_FILES['image']['name']!="")
    {
        $image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];

        move_uploaded_file($tmp,"../uploads/".$image);
    }

    $sql = "UPDATE pets SET

    pet_name='$pet_name',
    species='$species',
    breed='$breed',
    age='$age',
    weight='$weight',
    gender='$gender',
    color='$color',
    description='$description',
    image='$image',
    status='$status'

    WHERE pet_id='$pet_id'";

    if(mysqli_query($conn,$sql))
    {
        $message="<div class='success'>Pet Updated Successfully!</div>";

        $result=mysqli_query($conn,"SELECT * FROM pets WHERE pet_id='$pet_id'");
        $pet=mysqli_fetch_assoc($result);
    }
    else
    {
        $message="<div class='error'>Update Failed.</div>";
    }

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Pet</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<h1>PawConnect: Admin Panel</h1>

<nav>

<a href="dashboard.php">📊 Dashboard</a>
<a href="pet_inventory.php">🐾 Pet Inventory</a>
<a href="../logout.php">🚪 Logout</a>

</nav>

</header>

<div class="container">

<div class="form-box">

<h2>Edit Pet</h2>

<?php echo $message; ?>

<form method="POST" enctype="multipart/form-data">

<label>Pet Name</label>

<input
type="text"
name="pet_name"
value="<?php echo $pet['pet_name']; ?>"
required>

<label>Species</label>

<select name="species">

<option <?php if($pet['species']=="Dog") echo "selected"; ?>>Dog</option>

<option <?php if($pet['species']=="Cat") echo "selected"; ?>>Cat</option>

<option <?php if($pet['species']=="Rabbit") echo "selected"; ?>>Rabbit</option>

<option <?php if($pet['species']=="Bird") echo "selected"; ?>>Bird</option>

</select>

<label>Breed</label>

<input
type="text"
name="breed"
value="<?php echo $pet['breed']; ?>"
required>

<label>Age</label>

<input
type="number"
name="age"
value="<?php echo $pet['age']; ?>"
required>

<label>Weight</label>

<input
type="number"
name="weight"
value="<?php echo $pet['weight']; ?>"
required>

<label>Gender</label>

<select name="gender">

<option <?php if($pet['gender']=="Male") echo "selected"; ?>>Male</option>

<option <?php if($pet['gender']=="Female") echo "selected"; ?>>Female</option>

</select>

<label>Color</label>

<input
type="text"
name="color"
value="<?php echo $pet['color']; ?>"
required>

<label>Description</label>

<textarea
name="description"
rows="5"
required><?php echo $pet['description']; ?></textarea>

<label>Status</label>

<select name="status">

<option <?php if($pet['status']=="Available") echo "selected"; ?>>Available</option>

<option <?php if($pet['status']=="Adopted") echo "selected"; ?>>Adopted</option>

</select>

<br><br>

Current Image

<br><br>

<img
src="../uploads/<?php echo $pet['image']; ?>"
width="200">

<br><br>

<label>Replace Image (Optional)</label>

<input
type="file"
name="image"
accept="image/*">

<br><br>

<button
class="btn"
name="update_pet">

Update Pet

</button>

<a
href="pet_inventory.php"
class="btn btn-red">

Cancel

</a>

</form>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>