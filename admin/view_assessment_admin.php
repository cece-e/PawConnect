<?php
session_start();
include("../database.php");

// Check Admin Login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

$request_id = intval($_GET['request_id'] ?? 0);

$sql = "SELECT alr.*, ar.reason_for_adoption, users.fullname, pets.pet_name
        FROM adoption_likert_responses alr
        INNER JOIN adoption_requests ar ON alr.request_id = ar.request_id
        INNER JOIN users ON ar.user_id = users.user_id
        INNER JOIN pets ON ar.pet_id = pets.pet_id
        WHERE alr.request_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $request_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if(!$row)
{
    die("Assessment not found.");
}

$likert_questions = [
    "q1_right_reasons"      => "I am adopting a pet for the right reasons (companionship, lifelong commitment, and responsible ownership).",
    "q2_household_support"  => "Everyone in my household supports adopting a pet.",
    "q3_financial_prep"     => "I am financially prepared to provide food, veterinary care, and other necessities.",
    "q4_time_availability"  => "I have enough time each day to care for and interact with a pet.",
    "q5_safe_environment"   => "My home provides a safe and suitable environment for a pet.",
    "q6_commitment_change"  => "I am prepared to keep this pet even if my living situation changes.",
    "q7_behavior_patience"  => "I am willing to manage common pet behaviors with patience and appropriate training.",
    "q8_emergency_plan"     => "I have a plan for my pet's care during vacations or emergencies.",
    "q9_spay_neuter"        => "I believe spaying/neutering is an important part of responsible pet ownership.",
    "q10_unknown_history"   => "I understand that adopted animals may have unknown medical or behavioral histories.",
    "q11_lifelong_care"     => "I am committed to providing lifelong care and will not rehome my pet without first contacting the adoption organization.",
    "q12_policy_compliance" => "I am willing to comply with the adoption organization's policies, including follow-up welfare checks if required.",
];

$likert_scale = [
    1 => "Strongly Disagree",
    2 => "Disagree",
    3 => "Neutral",
    4 => "Agree",
    5 => "Strongly Agree",
];
?>

<!DOCTYPE html>
<html>

<head>

<title>Adoption Assessment - Admin View</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<h1>Admin Panel</h1>

<nav>

<a href="dashboard.php">📊 Dashboard</a>
<a href="pet_inventory.php">🐾 Pet Inventory</a>
<a href="manage_users.php">👤 Users</a>
<a href="reports.php">📊 Reports</a>
<a href="../logout.php">🚪 Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>Adoption Readiness Assessment</h2>

<p>
<strong>Applicant:</strong> <?php echo htmlspecialchars($row['fullname']); ?><br>
<strong>Pet:</strong> <?php echo htmlspecialchars($row['pet_name']); ?>
</p>

<h3>Reason for Adoption</h3>
<p><?php echo nl2br(htmlspecialchars($row['reason_for_adoption'])); ?></p>

<h3>Likert Responses</h3>

<table>

<tr>
<th>Statement</th>
<th>Response</th>
</tr>

<?php foreach($likert_questions as $key => $question): ?>

<tr>

<td><?php echo htmlspecialchars($question); ?></td>

<td><?php echo htmlspecialchars($likert_scale[$row[$key]]); ?></td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="adoption_requests.php" class="btn">Back to Adoption Requests</a>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>