<?php
session_start();
include("../database.php");

// Check login
if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

// Only users
if($_SESSION['role'] != "user")
{
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['request_id']))
{
    header("Location: my_requests.php");
    exit();
}

$request_id = intval($_GET['request_id']);

// ---- Verify the request belongs to this user and is still Pending ----
$check_sql = "SELECT
                  adoption_requests.*,
                  pets.pet_name
              FROM adoption_requests
              INNER JOIN pets ON adoption_requests.pet_id = pets.pet_id
              WHERE adoption_requests.request_id='$request_id'
              AND adoption_requests.user_id='$user_id'";

$check_result = mysqli_query($conn, $check_sql);

if(mysqli_num_rows($check_result) == 0)
{
    header("Location: my_requests.php");
    exit();
}

$request = mysqli_fetch_assoc($check_result);

if($request['status'] != "Pending")
{
    header("Location: my_requests.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Upload Verification Documents</title>

<link rel="stylesheet" href="../style.css">

<style>

.back-link{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#2e6fbb;
    font-weight:bold;
}

.upload-form label{
    display:block;
    margin-top:16px;
    font-weight:bold;
}

.upload-form small{
    color:#666;
    display:block;
    margin-bottom:4px;
}

.upload-form input[type="file"]{
    display:block;
    margin-top:5px;
    width:100%;
}

.form-actions{
    margin-top:25px;
    text-align:right;
}

.form-actions a{
    margin-right:8px;
    padding:8px 16px;
    background:#ccc;
    color:#333;
    border-radius:5px;
    text-decoration:none;
    display:inline-block;
}

.form-actions button{
    padding:8px 16px;
    border-radius:5px;
    border:none;
    background:#2e6fbb;
    color:white;
    font-weight:bold;
    cursor:pointer;
}

.alert{
    padding:10px 15px;
    border-radius:5px;
    margin-bottom:15px;
    font-weight:bold;
}

.alert-error{
    background:#f8d7da;
    color:#721c24;
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

<a href="../index.php">Home</a>
<a href="dashboard.php">Dashboard</a>
<a href="../available_pets.php">Available Pets</a>
<a href="my_requests.php">My Requests</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<a class="back-link" href="my_requests.php">&larr; Back to My Adoption Requests</a>

<h2>Upload Verification Documents</h2>

<p>For your request on <strong><?php echo htmlspecialchars($request['pet_name']); ?></strong>, please upload the following requirements so we can verify your application:</p>

<?php if(isset($_GET['upload_error'])): ?>

<div class="alert alert-error"><?php echo htmlspecialchars($_GET['upload_error']); ?></div>

<?php endif; ?>

<form class="upload-form" action="upload_documents.php" method="POST" enctype="multipart/form-data">

<input type="hidden" name="request_id" value="<?php echo $request_id; ?>">

<label>1×1 I.D. Picture</label>
<small>A clear 1x1 picture of yourself (JPG or PNG, max 200MB).</small>
<input type="file" name="id_picture" accept="image/jpeg,image/png" required>

<label>Photocopy of Valid ID</label>
<small>Any government-issued ID (JPG or PNG, max 200MB).</small>
<input type="file" name="id_copy" accept="image/jpeg,image/png" required>

<label>Photos of Your Home</label>
<small>Include both inside and outside shots, especially the space allocated for the pet. You may select multiple images.</small>
<input type="file" name="home_photos[]" accept="image/jpeg,image/png" multiple required>

<div class="form-actions">
<a href="my_requests.php">Cancel</a>
<button type="submit">Submit Documents</button>
</div>

</form>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>