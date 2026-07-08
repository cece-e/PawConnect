<?php
session_start();
include("../database.php");

// Check login
if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

// Only normal users
if($_SESSION['role'] != "user")
{
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

$errors = [];

// ============================================================
// Determine pet_id from either GET (first load) or POST (resubmit on error)
// ============================================================
$pet_id = intval($_POST['pet_id'] ?? $_GET['id'] ?? 0);

if($pet_id <= 0)
{
    header("Location: ../available_pets.php");
    exit();
}

// Check if pet exists
$petStmt = mysqli_prepare($conn, "SELECT * FROM pets WHERE pet_id = ?");
mysqli_stmt_bind_param($petStmt, "i", $pet_id);
mysqli_stmt_execute($petStmt);
$petResult = mysqli_stmt_get_result($petStmt);

if(mysqli_num_rows($petResult) == 0)
{
    die("Pet not found.");
}

$pet = mysqli_fetch_assoc($petResult);

// Check if already adopted
if($pet['status'] == "Adopted")
{
    die("This pet has already been adopted.");
}

// Check duplicate request
$checkStmt = mysqli_prepare($conn, "
    SELECT * FROM adoption_requests
    WHERE user_id = ? AND pet_id = ? AND status = 'Pending'
");
mysqli_stmt_bind_param($checkStmt, "ii", $user_id, $pet_id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if(mysqli_num_rows($checkResult) > 0)
{
    echo "<script>
    alert('You already have a pending request for this pet.');
    window.location='../available_pets.php';
    </script>";
    exit();
}

// ============================================================
// Handle form submission
// ============================================================
if($_SERVER['REQUEST_METHOD'] == "POST")
{
    $reason = trim($_POST['reason_for_adoption'] ?? '');

    if(strlen($reason) < 30)
    {
        $errors[] = "Please explain why you want to adopt this pet (at least 30 characters).";
    }

    $likert_values = [];

    foreach(array_keys($likert_questions) as $key)
    {
        $val = $_POST[$key] ?? null;

        if(!in_array($val, ["1","2","3","4","5"], true))
        {
            $errors[] = "Please answer all assessment questions.";
            break;
        }

        $likert_values[$key] = (int)$val;
    }

    if(empty($errors))
    {
        mysqli_begin_transaction($conn);

        try
        {
            // Insert adoption request
            $insertStmt = mysqli_prepare($conn, "
                INSERT INTO adoption_requests (user_id, pet_id, reason_for_adoption, status, request_date)
                VALUES (?, ?, ?, 'Pending', NOW())
            ");
            mysqli_stmt_bind_param($insertStmt, "iis", $user_id, $pet_id, $reason);
            mysqli_stmt_execute($insertStmt);

            $request_id = mysqli_insert_id($conn);

            // Insert Likert responses
            $likertStmt = mysqli_prepare($conn, "
                INSERT INTO adoption_likert_responses
                (request_id, q1_right_reasons, q2_household_support, q3_financial_prep,
                 q4_time_availability, q5_safe_environment, q6_commitment_change,
                 q7_behavior_patience, q8_emergency_plan, q9_spay_neuter,
                 q10_unknown_history, q11_lifelong_care, q12_policy_compliance)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param(
                $likertStmt, "iiiiiiiiiiiii",
                $request_id,
                $likert_values["q1_right_reasons"],
                $likert_values["q2_household_support"],
                $likert_values["q3_financial_prep"],
                $likert_values["q4_time_availability"],
                $likert_values["q5_safe_environment"],
                $likert_values["q6_commitment_change"],
                $likert_values["q7_behavior_patience"],
                $likert_values["q8_emergency_plan"],
                $likert_values["q9_spay_neuter"],
                $likert_values["q10_unknown_history"],
                $likert_values["q11_lifelong_care"],
                $likert_values["q12_policy_compliance"]
            );
            mysqli_stmt_execute($likertStmt);

            mysqli_commit($conn);

            echo "<script>
            alert('Adoption request submitted successfully!');
            window.location='my_requests.php';
            </script>";
            exit();
        }
        catch(mysqli_sql_exception $e)
        {
            mysqli_rollback($conn);
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>PawConnect: Adopt <?php echo htmlspecialchars($pet['pet_name']); ?></title>

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
<a href="my_requests.php">My Requests</a>
<a href="profile.php">Profile</a>
<a href="../logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>Adopt <?php echo htmlspecialchars($pet['pet_name']); ?></h2>

<p>
<strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); ?><br>
<strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?>
</p>

<?php if(!empty($errors)): ?>
<div class="alert alert-error">
<ul>
<?php foreach($errors as $error): ?>
    <li><?php echo htmlspecialchars($error); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="POST" action="">

<input type="hidden" name="pet_id" value="<?php echo $pet_id; ?>">

<label for="reason_for_adoption">Why do you want to adopt this pet? <span style="color:red">*</span></label>
<p>Reason must have 30 characters minimum. </p>
<br>
<textarea name="reason_for_adoption" id="reason_for_adoption" rows="5" required minlength="30" style="width:100%;"><?php echo isset($_POST['reason_for_adoption']) ? htmlspecialchars($_POST['reason_for_adoption']) : ''; ?></textarea>

<br><br>

<h3>Adoption Readiness Assessment</h3>
<p>Please rate how much you agree with each statement.</p>

<table class="likert-table">
<tr>
    <th>Statement</th>
    <?php foreach($likert_scale as $label): ?>
        <th><?php echo htmlspecialchars($label); ?></th>
    <?php endforeach; ?>
</tr>

<?php foreach($likert_questions as $key => $question): ?>
<tr>
    <td><?php echo htmlspecialchars($question); ?></td>
    <?php foreach($likert_scale as $value => $label): ?>
        <td style="text-align:center;">
            <input type="radio" name="<?php echo $key; ?>" value="<?php echo $value; ?>"
                <?php echo (isset($_POST[$key]) && $_POST[$key] == $value) ? "checked" : ""; ?>
                required>
        </td>
    <?php endforeach; ?>
</tr>
<?php endforeach; ?>

</table>

<br>

<button type="submit" class="btn">Submit Adoption Request</button>
<a href="../available_pets.php" class="btn">Cancel</a>

</form>

</div>

</div>

<footer>

<p>© PawConnect</p>

</footer>

</body>

</html>