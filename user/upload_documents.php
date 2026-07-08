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

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    header("Location: my_requests.php");
    exit();
}

$request_id = intval($_POST['request_id']);

// ---- Verify the request actually belongs to this user ----
$check_sql = "SELECT * FROM adoption_requests WHERE request_id='$request_id' AND user_id='$user_id'";
$check_result = mysqli_query($conn, $check_sql);

if(mysqli_num_rows($check_result) == 0)
{
    die("Invalid request or you do not have permission to upload documents for this request.");
}

// ---- Basic validation setup ----
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
$max_size = 200 * 1024 * 1024; // 5MB per file
$upload_dir = "../uploads/documents/";

if(!is_dir($upload_dir))
{
    mkdir($upload_dir, 0755, true);
}

function save_uploaded_file($file, $upload_dir, $allowed_types, $max_size, $prefix)
{
    if($file['error'] !== UPLOAD_ERR_OK)
    {
        return [false, "Upload error for $prefix."];
    }

    if(!in_array($file['type'], $allowed_types))
    {
        return [false, "$prefix must be a JPG or PNG image."];
    }

    if($file['size'] > $max_size)
    {
        return [false, "$prefix must be smaller than 200MB."];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . "_" . uniqid() . "_" . time() . "." . $ext;
    $destination = $upload_dir . $filename;

    if(!move_uploaded_file($file['tmp_name'], $destination))
    {
        return [false, "Failed to save $prefix."];
    }

    return [true, $filename];
}

$errors = [];

// ---- 1x1 ID Picture ----
if(!isset($_FILES['id_picture']) || $_FILES['id_picture']['error'] === UPLOAD_ERR_NO_FILE)
{
    $errors[] = "1x1 ID Picture is required.";
}
else
{
    list($ok, $result) = save_uploaded_file($_FILES['id_picture'], $upload_dir, $allowed_types, $max_size, "id_picture");
    if($ok) { $id_picture_filename = $result; } else { $errors[] = $result; }
}

// ---- Photocopy of valid ID ----
if(!isset($_FILES['id_copy']) || $_FILES['id_copy']['error'] === UPLOAD_ERR_NO_FILE)
{
    $errors[] = "Photocopy of a valid ID is required.";
}
else
{
    list($ok, $result) = save_uploaded_file($_FILES['id_copy'], $upload_dir, $allowed_types, $max_size, "id_copy");
    if($ok) { $id_copy_filename = $result; } else { $errors[] = $result; }
}

// ---- Home photos (multiple) ----
$home_photo_filenames = [];
if(isset($_FILES['home_photos']))
{
    $total = count($_FILES['home_photos']['name']);

    if($total == 0 || $_FILES['home_photos']['error'][0] === UPLOAD_ERR_NO_FILE)
    {
        $errors[] = "At least one home photo is required.";
    }
    else
    {
        for($i = 0; $i < $total; $i++)
        {
            if($_FILES['home_photos']['error'][$i] !== UPLOAD_ERR_OK)
            {
                continue;
            }

            $single_file = [
                'name' => $_FILES['home_photos']['name'][$i],
                'type' => $_FILES['home_photos']['type'][$i],
                'tmp_name' => $_FILES['home_photos']['tmp_name'][$i],
                'error' => $_FILES['home_photos']['error'][$i],
                'size' => $_FILES['home_photos']['size'][$i],
            ];

            list($ok, $result) = save_uploaded_file($single_file, $upload_dir, $allowed_types, $max_size, "home_photo_$i");

            if($ok)
            {
                $home_photo_filenames[] = $result;
            }
            else
            {
                $errors[] = $result;
            }
        }
    }
}
else
{
    $errors[] = "At least one home photo is required.";
}

// ---- If there are errors, stop here ----
if(count($errors) > 0)
{
    $error_message = implode(" ", $errors);
    header("Location: upload_form.php?request_id=" . $request_id . "&upload_error=" . urlencode($error_message));
    exit();
}

// ---- Save to database ----
// One document set per request: insert if none exists, otherwise update.
$existing_sql = "SELECT document_id FROM adoption_documents WHERE request_id='$request_id'";
$existing_result = mysqli_query($conn, $existing_sql);

$id_picture_filename = mysqli_real_escape_string($conn, $id_picture_filename);
$id_copy_filename = mysqli_real_escape_string($conn, $id_copy_filename);

if(mysqli_num_rows($existing_result) > 0)
{
    $existing_row = mysqli_fetch_assoc($existing_result);
    $document_id = $existing_row['document_id'];

    $update_sql = "UPDATE adoption_documents
                   SET id_picture='$id_picture_filename', id_copy='$id_copy_filename', uploaded_at=NOW()
                   WHERE document_id='$document_id'";
    mysqli_query($conn, $update_sql);

    // Clear previous home photos before adding the new set
    mysqli_query($conn, "DELETE FROM adoption_home_photos WHERE document_id='$document_id'");
}
else
{
    $insert_sql = "INSERT INTO adoption_documents (request_id, user_id, id_picture, id_copy)
                   VALUES ('$request_id', '$user_id', '$id_picture_filename', '$id_copy_filename')";
    mysqli_query($conn, $insert_sql);
    $document_id = mysqli_insert_id($conn);
}

foreach($home_photo_filenames as $photo)
{
    $photo_escaped = mysqli_real_escape_string($conn, $photo);
    $photo_sql = "INSERT INTO adoption_home_photos (document_id, photo_path)
                  VALUES ('$document_id', '$photo_escaped')";
    mysqli_query($conn, $photo_sql);
}

header("Location: my_requests.php?upload_success=1");
exit();
?>