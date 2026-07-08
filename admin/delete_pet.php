<?php
session_start();
include("../database.php");

// Check if admin is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

// Check if ID exists
if(!isset($_GET['id']))
{
    header("Location: pet_inventory.php");
    exit();
}

$pet_id = intval($_GET['id']);

// Get image filename
$result = mysqli_query($conn,"SELECT image FROM pets WHERE pet_id='$pet_id'");

if(mysqli_num_rows($result) > 0)
{
    $pet = mysqli_fetch_assoc($result);

    // Delete image file if it exists
    if($pet['image'] != "" && file_exists("../uploads/".$pet['image']))
    {
        unlink("../uploads/".$pet['image']);
    }

    // Delete pet from database
    mysqli_query($conn,"DELETE FROM pets WHERE pet_id='$pet_id'");
}

// Redirect back
header("Location: pet_inventory.php");
exit();
?>