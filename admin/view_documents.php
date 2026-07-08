<?php
session_start();
include("../database.php");

// Check Admin Login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['request_id']))
{
    header("Location: adoption_requests.php");
    exit();
}

$request_id = intval($_GET['request_id']);

if(isset($_GET['approve']))
{
    $approve_id = intval($_GET['approve']);

    $request = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT *
    FROM adoption_requests
    WHERE request_id='$approve_id'
    "));

    if($request)
    {
        $user_id = $request['user_id'];
        $pet_id = $request['pet_id'];

        mysqli_query($conn,"
        UPDATE adoption_requests
        SET status='Approved'
        WHERE request_id='$approve_id'
        ");

        mysqli_query($conn,"
        UPDATE pets
        SET status='Adopted'
        WHERE pet_id='$pet_id'
        ");

        mysqli_query($conn,"
        INSERT INTO adoption_history(user_id,pet_id)
        VALUES('$user_id','$pet_id')
        ");

        mysqli_query($conn,"
        UPDATE adoption_requests
        SET status='Rejected'
        WHERE pet_id='$pet_id'
        AND request_id!='$approve_id'
        AND status='Pending'
        ");
    }

    header("Location: adoption_requests.php");
    exit();
}

if(isset($_GET['reject']))
{
    $reject_id = intval($_GET['reject']);

    mysqli_query($conn,"
    UPDATE adoption_requests
    SET status='Rejected'
    WHERE request_id='$reject_id'
    ");

    header("Location: adoption_requests.php");
    exit();
}


$sql = "
SELECT

adoption_requests.*,
users.fullname,
pets.pet_name,
pets.species,
pets.breed,
adoption_documents.document_id,
adoption_documents.id_picture,
adoption_documents.id_copy

FROM adoption_requests

INNER JOIN users
ON adoption_requests.user_id = users.user_id

INNER JOIN pets
ON adoption_requests.pet_id = pets.pet_id

LEFT JOIN adoption_documents
ON adoption_requests.request_id = adoption_documents.request_id

WHERE adoption_requests.request_id='$request_id'
";

$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) == 0)
{
    header("Location: adoption_requests.php");
    exit();
}

$row = mysqli_fetch_assoc($result);
$has_documents = !empty($row['document_id']);

$home_photos = [];
if($has_documents)
{
    $document_id = $row['document_id'];
    $photos_result = mysqli_query($conn,"SELECT photo_path FROM adoption_home_photos WHERE document_id='$document_id'");

    while($photo_row = mysqli_fetch_assoc($photos_result))
    {
        $home_photos[] = $photo_row['photo_path'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Verification Documents — Request #<?php echo $row['request_id']; ?></title>

<link rel="stylesheet" href="../style.css">

<style>

.pending{
background:orange;
color:white;
padding:6px 12px;
border-radius:5px;
}

.approved{
background:green;
color:white;
padding:6px 12px;
border-radius:5px;
}

.rejected{
background:red;
color:white;
padding:6px 12px;
border-radius:5px;
}

.back-link{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#2e6fbb;
    font-weight:bold;
}

.request-summary{
    margin-bottom:20px;
    padding-bottom:15px;
    border-bottom:1px solid #ddd;
}

.request-summary p{
    margin:4px 0;
}

.doc-section{
    margin-top:22px;
}

.doc-section h4{
    margin-bottom:10px;
    border-bottom:1px solid #ddd;
    padding-bottom:4px;
}

.doc-images{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}

.doc-images a img{
    width:150px;
    height:150px;
    object-fit:cover;
    border-radius:5px;
    border:1px solid #ccc;
}

.no-docs-msg{
    color:#b22222;
    font-weight:bold;
}

.verdict-area{
    margin-top:30px;
    padding-top:20px;
    border-top:2px solid #eee;
    text-align:right;
}

.verdict-area a{
    margin-left:8px;
}

.already-decided{
    margin-top:30px;
    padding-top:20px;
    border-top:2px solid #eee;
}

</style>

</head>

<body>

<header>

<h1>Admin Panel</h1>

<nav>

<a href="dashboard.php">📊 Dashboard</a>
<a href="pet_inventory.php">🐾 Pet Inventory</a>
<a href="adoption_requests.php">📋 Requests</a>
<a href="manage_users.php">👤 Users</a>
<a href="reports.php">📊 Reports</a>
<a href="../logout.php">🚪 Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<a class="back-link" href="adoption_requests.php">&larr; Back to Adoption Requests</a>

<h2>Verification Documents</h2>

<div class="request-summary">

<p><strong>Request #:</strong> <?php echo $row['request_id']; ?></p>
<p><strong>Applicant:</strong> <?php echo htmlspecialchars($row['fullname']); ?></p>
<p><strong>Pet:</strong> <?php echo htmlspecialchars($row['pet_name']); ?> (<?php echo htmlspecialchars($row['species']); ?>, <?php echo htmlspecialchars($row['breed']); ?>)</p>
<p><strong>Request Date:</strong> <?php echo $row['request_date']; ?></p>
<p>
<strong>Status:</strong>
<?php

if($row['status']=="Pending")
{
    echo "<span class='pending'>Pending</span>";
}
elseif($row['status']=="Approved")
{
    echo "<span class='approved'>Approved</span>";
}
else
{
    echo "<span class='rejected'>Rejected</span>";
}

?>
</p>

</div>

<?php if($has_documents): ?>

<div class="doc-section">
<h4>1×1 I.D. Picture</h4>
<div class="doc-images">
<a href="../uploads/documents/<?php echo htmlspecialchars($row['id_picture']); ?>" target="_blank">
<img src="../uploads/documents/<?php echo htmlspecialchars($row['id_picture']); ?>">
</a>
</div>
</div>

<div class="doc-section">
<h4>Photocopy of Valid ID</h4>
<div class="doc-images">
<a href="../uploads/documents/<?php echo htmlspecialchars($row['id_copy']); ?>" target="_blank">
<img src="../uploads/documents/<?php echo htmlspecialchars($row['id_copy']); ?>">
</a>
</div>
</div>

<div class="doc-section">
<h4>Home Photos</h4>
<div class="doc-images">

<?php if(count($home_photos) > 0): ?>

    <?php foreach($home_photos as $photo): ?>

    <a href="../uploads/documents/<?php echo htmlspecialchars($photo); ?>" target="_blank">
    <img src="../uploads/documents/<?php echo htmlspecialchars($photo); ?>">
    </a>

    <?php endforeach; ?>

<?php else: ?>

    <p>No home photos found.</p>

<?php endif; ?>

</div>
</div>

<?php else: ?>

<p class="no-docs-msg">This applicant has not submitted verification documents yet.</p>

<?php endif; ?>

<?php if($row['status'] == "Pending"): ?>

<div class="verdict-area">

<a
class="btn btn-green"
href="view_documents.php?request_id=<?php echo $row['request_id']; ?>&approve=<?php echo $row['request_id']; ?>">
Approve
</a>

<a
class="btn btn-red"
href="view_documents.php?request_id=<?php echo $row['request_id']; ?>&reject=<?php echo $row['request_id']; ?>">
Reject
</a>

</div>

<?php else: ?>

<div class="already-decided">
<p>This request has already been marked as <strong><?php echo $row['status']; ?></strong>.</p>
</div>

<?php endif; ?>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>