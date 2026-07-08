<?php
session_start();
include("../database.php");

// Check Admin Login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

// General message shown to users when a request is approved
$APPROVAL_MESSAGE = "Congratulations! Your adoption request has been approved.";

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']))
{
    $request_id = intval($_POST['request_id'] ?? 0);
    $action = $_POST['action'];

    if($request_id > 0 && in_array($action, ["approve", "reject"], true))
    {
        if($action == "approve")
        {
            // Always use the fixed general message for approvals — never trust client input for this
            $admin_feedback = $APPROVAL_MESSAGE;

            $reqStmt = mysqli_prepare($conn, "SELECT * FROM adoption_requests WHERE request_id = ?");
            mysqli_stmt_bind_param($reqStmt, "i", $request_id);
            mysqli_stmt_execute($reqStmt);
            $request = mysqli_fetch_assoc(mysqli_stmt_get_result($reqStmt));

            if($request)
            {
                $req_user_id = $request['user_id'];
                $req_pet_id = $request['pet_id'];

                // Approve request + save general message
                $approveStmt = mysqli_prepare($conn, "
                    UPDATE adoption_requests
                    SET status = 'Approved', admin_feedback = ?
                    WHERE request_id = ?
                ");
                mysqli_stmt_bind_param($approveStmt, "si", $admin_feedback, $request_id);
                mysqli_stmt_execute($approveStmt);

                // Mark pet as adopted
                $petStmt = mysqli_prepare($conn, "UPDATE pets SET status = 'Adopted' WHERE pet_id = ?");
                mysqli_stmt_bind_param($petStmt, "i", $req_pet_id);
                mysqli_stmt_execute($petStmt);

                // Save to adoption history
                $histStmt = mysqli_prepare($conn, "INSERT INTO adoption_history (user_id, pet_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($histStmt, "ii", $req_user_id, $req_pet_id);
                mysqli_stmt_execute($histStmt);

                // Reject all other pending requests for this pet
                $rejectOthersStmt = mysqli_prepare($conn, "
                    UPDATE adoption_requests
                    SET status = 'Rejected'
                    WHERE pet_id = ? AND request_id != ? AND status = 'Pending'
                ");
                mysqli_stmt_bind_param($rejectOthersStmt, "ii", $req_pet_id, $request_id);
                mysqli_stmt_execute($rejectOthersStmt);
            }
        }
        elseif($action == "reject")
        {
            $admin_feedback = trim($_POST['admin_feedback'] ?? '');

            // Reason is required for rejections
            if($admin_feedback !== '')
            {
                $rejectStmt = mysqli_prepare($conn, "
                    UPDATE adoption_requests
                    SET status = 'Rejected', admin_feedback = ?
                    WHERE request_id = ?
                ");
                mysqli_stmt_bind_param($rejectStmt, "si", $admin_feedback, $request_id);
                mysqli_stmt_execute($rejectStmt);

                header("Location: adoption_requests.php");
                exit();
            }
            else
            {
                // No reason typed - send back to the reject panel for this same request with an error
                header("Location: adoption_requests.php?reject_mode=" . $request_id . "&reject_error=1#reject-panel");
                exit();
            }
        }
    }

    header("Location: adoption_requests.php");
    exit();
}

// Which row (if any) is currently in reject mode
$reject_mode_id = isset($_GET['reject_mode']) ? intval($_GET['reject_mode']) : 0;
$reject_error = isset($_GET['reject_error']);

$sql = "
SELECT

adoption_requests.*,
users.fullname,
pets.pet_name,
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

ORDER BY request_date DESC
";

$result = mysqli_query($conn,$sql);

$home_photos_by_doc = [];

$photos_result = mysqli_query($conn,"SELECT document_id, photo_path FROM adoption_home_photos");

while($photo_row = mysqli_fetch_assoc($photos_result))
{
    $home_photos_by_doc[$photo_row['document_id']][] = $photo_row['photo_path'];
}

// If a row is in reject mode, fetch its full details for the bottom panel
$reject_target = null;

if($reject_mode_id > 0)
{
    $targetStmt = mysqli_prepare($conn, "
        SELECT adoption_requests.*, users.fullname, pets.pet_name
        FROM adoption_requests
        INNER JOIN users ON adoption_requests.user_id = users.user_id
        INNER JOIN pets ON adoption_requests.pet_id = pets.pet_id
        WHERE adoption_requests.request_id = ?
    ");
    mysqli_stmt_bind_param($targetStmt, "i", $reject_mode_id);
    mysqli_stmt_execute($targetStmt);
    $reject_target = mysqli_fetch_assoc(mysqli_stmt_get_result($targetStmt));
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Adoption Requests</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<div class="navbar">

<div class="logo">

<i class="fa-solid fa-paw"></i>

<h2>Admin Panel</h2>

</div>

<nav>

<a href="dashboard.php">Dashboard</a>
<a href="pet_inventory.php">Pet Inventory</a>
<a href="manage_users.php">Users</a>
<a href="reports.php">Reports</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>Adoption Requests</h2>

<br>

<table>

<tr>

<th>ID</th>
<th>User</th>
<th>Pet</th>
<th>Date</th>
<th>Reason for Adoption</th>
<th>Assessment</th>
<th>Status</th>
<th>Documents</th>
<th>Admin Feedback</th>
<th>Action</th>
</tr>

<?php

while($row=mysqli_fetch_assoc($result))
{
    $has_documents = !empty($row['document_id']);
    $home_photos = $has_documents && isset($home_photos_by_doc[$row['document_id']])
                    ? $home_photos_by_doc[$row['document_id']]
                    : [];
    $rid = $row['request_id'];

?>

<tr>

<td><?php echo $rid; ?></td>

<td><?php echo htmlspecialchars($row['fullname']); ?></td>

<td><?php echo htmlspecialchars($row['pet_name']); ?></td>

<td><?php echo $row['request_date']; ?></td>

<td><?php echo nl2br(htmlspecialchars($row['reason_for_adoption'] ?? '')); ?></td>

<td>
<a class="upload-btn" href="view_assessment_admin.php?request_id=<?php echo $rid; ?>">View Assessment</a>
</td>

<td>

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

</td>

<td>

<?php if($has_documents): ?>

    <span class="doc-status doc-submitted">Submitted</span>
    <br>
    <a class="view-docs-btn" href="view_documents.php?request_id=<?php echo $rid; ?>">
        📁 View Documents
    </a>

<?php else: ?>

    <span class="doc-status doc-missing">Not Submitted</span>

<?php endif; ?>

</td>

<td>

<?php echo !empty($row['admin_feedback']) ? nl2br(htmlspecialchars($row['admin_feedback'])) : "-"; ?>

</td>

<td>

<?php if($row['status']=="Pending"): ?>

    <?php if($reject_mode_id == $rid): ?>

        <!-- This row's rejection is being written below -->
        <em>Writing rejection reason below ⬇</em>
        <br>
        <a href="adoption_requests.php" class="btn">Cancel</a>

    <?php else: ?>

        <!-- Default: Approve submits immediately, Reject jumps to bottom panel -->
        <form method="POST" action="adoption_requests.php" style="display:inline;">
            <input type="hidden" name="request_id" value="<?php echo $rid; ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn btn-green">✅ Approve</button>
        </form>

        <a class="btn btn-red" href="adoption_requests.php?reject_mode=<?php echo $rid; ?>#reject-panel">❌ Reject</a>

    <?php endif; ?>

<?php else: ?>

    <?php echo "-"; ?>

<?php endif; ?>

</td>

</tr>

<?php

}

?>

</table>

<?php if($reject_target): ?>

<div id="reject-panel" class="reject-panel" style="margin-top:30px; padding-top:20px; border-top:2px solid #ccc;">

<h3>Reject Adoption Request #<?php echo $reject_target['request_id']; ?></h3>

<p>
<strong>Applicant:</strong> <?php echo htmlspecialchars($reject_target['fullname']); ?><br>
<strong>Pet:</strong> <?php echo htmlspecialchars($reject_target['pet_name']); ?>
</p>

<p>
<strong>Applicant's stated reason for adoption:</strong><br>
<?php echo nl2br(htmlspecialchars($reject_target['reason_for_adoption'] ?? '')); ?>
</p>

<p>
<a href="view_assessment_admin.php?request_id=<?php echo $reject_target['request_id']; ?>">View full readiness assessment</a>
</p>

<?php if($reject_error): ?>
<div class="alert alert-error">Please enter a reason for rejection.</div>
<?php endif; ?>

<form method="POST" action="adoption_requests.php">

<input type="hidden" name="request_id" value="<?php echo $reject_target['request_id']; ?>">
<input type="hidden" name="action" value="reject">

<label for="admin_feedback"><strong>Reason for rejection:</strong></label>
<br>
<textarea name="admin_feedback" id="admin_feedback" rows="6" style="width:100%;" placeholder="Explain clearly why this adoption request is being rejected. This message will be shown to the applicant."></textarea>

<br><br>

<button type="submit" class="btn btn-red">Confirm Rejection</button>
<a href="adoption_requests.php" class="btn">Cancel</a>

</form>

</div>

<?php endif; ?>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>