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
$request_id = $_GET['request_id'] ?? 0;

// Ensure this assessment belongs to a request owned by the logged-in user
$sql = "SELECT alr.*
        FROM adoption_likert_responses alr
        INNER JOIN adoption_requests ar ON alr.request_id = ar.request_id
        WHERE alr.request_id = ? AND ar.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $request_id, $user_id);
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

<title>Adoption Assessment</title>

<link rel="stylesheet" href="../style.css">

</head>

<body>

<header>

<h1>🐾 Pet Adoption System 🐾</h1>

<nav>

<a href="../index.php">🏠 Home</a>
<a href="../available_pets.php">✨ Available Pets</a>
<a href="dashboard.php">📊 Dashboard</a>
<a href="profile.php">👤 Profile</a>
<a href="../logout.php">🚪 Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>Adoption Readiness Assessment</h2>

<br>

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

<a href="my_requests.php" class="btn">Back to My Requests</a>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>