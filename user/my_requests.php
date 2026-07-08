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

$sql = "SELECT
            adoption_requests.*,
            pets.pet_name,
            pets.species,
            pets.breed,
            pets.weight,
            pets.image,
            adoption_documents.document_id,
            adoption_documents.id_picture,
            adoption_documents.id_copy
        FROM adoption_requests
        INNER JOIN pets
        ON adoption_requests.pet_id = pets.pet_id
        LEFT JOIN adoption_documents
        ON adoption_requests.request_id = adoption_documents.request_id
        WHERE adoption_requests.user_id = ?
        ORDER BY adoption_requests.request_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>

<head>

<title>My Adoption Requests</title>

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
<a href="dashboard.php">Dashboard</a>
<a href="profile.php">Profile</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>My Adoption Requests</h2>

<br>

<?php
if(isset($_GET['upload_success']))
{
    echo "<div class='alert alert-success'>Your verification documents were uploaded successfully.</div>";
}

if(isset($_GET['upload_error']))
{
    echo "<div class='alert alert-error'>" . htmlspecialchars($_GET['upload_error']) . "</div>";
}
?>

<?php

if(mysqli_num_rows($result)>0)
{

?>

<table>

<tr>

<th>Image</th>
<th>Pet</th>
<th>Species</th>
<th>Breed</th>
<th>Weight</th>
<th>Request Date</th>
<th>Reason for Adoption</th>
<th>Status</th>
<th>Verification Documents</th>
<th>Assessment</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result))
{

    $has_documents = !empty($row['document_id']);

?>

<tr>

<td>

<img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>">

</td>

<td>

<?php echo htmlspecialchars($row['pet_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['species']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['breed']); ?>

</td>

<td>

<?php echo $row['weight'] !== null ? htmlspecialchars($row['weight']) . " kg" : "N/A"; ?>

</td>

<td>

<?php echo $row['request_date']; ?>

</td>

<td>

<?php echo nl2br(htmlspecialchars($row['reason_for_adoption'])); ?>

</td>

<td>

<?php

if($row['status']=="Pending")
{
    echo "<span class='status pending'>Pending</span>";
}
elseif($row['status']=="Approved")
{
    echo "<span class='status approved'>Approved</span>";
}
else
{
    echo "<span class='status rejected'>Rejected</span>";
}

?>

</td>

<td>

<?php if($has_documents): ?>

    <span class="doc-status doc-submitted">Submitted</span>

<?php else: ?>

    <span class="doc-status doc-missing">Not Submitted</span>

<?php endif; ?>

<?php if($row['status'] == "Pending"): ?>

    <br>

    <a class="upload-btn" href="upload_form.php?request_id=<?php echo $row['request_id']; ?>">
        <?php echo $has_documents ? "Update Documents" : "Upload Documents"; ?>
    </a>

<?php endif; ?>

</td>

<td>

<a class="upload-btn" href="view_assessment.php?request_id=<?php echo $row['request_id']; ?>">View Assessment</a>

</td>

</tr>

<?php

}

?>

</table>

<?php

}
else
{

echo "<h3>You haven't submitted any adoption requests yet.</h3>";

echo "<br>";

echo "<a href='../available_pets.php' class='btn'>Browse Pets</a>";

}

?>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>



</body>

</html>