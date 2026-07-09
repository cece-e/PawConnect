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

if(!isset($_SESSION['role']) || $_SESSION['role'] != "user") 

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

    <style> 

        .status-popup-btn { 

            background: none; 

            border: none; 

            padding: 0; 

            cursor: pointer; 

            font-weight: bold; 

            text-decoration: underline; 

            font-size: 14px; 

        } 

 

        .status-popup-btn.approved { 

            color: #2e7d32; 

        } 

 

        .status-popup-btn.rejected { 

            color: #c62828; 

        } 

 

        .modal { 

            display: none; 

            position: fixed; 

            z-index: 9999; 

            left: 0; 

            top: 0; 

            width: 100%; 

            height: 100%; 

            overflow: auto; 

            background: rgba(0,0,0,0.6); 

        } 

 

        .modal-content { 

            background: #fff; 

            margin: 8% auto; 

            padding: 25px; 

            border-radius: 12px; 

            width: 90%; 

            max-width: 520px; 

            position: relative; 

            box-shadow: 0 8px 30px rgba(0,0,0,0.2); 

            text-align: center; 

        } 

 

        .close-modal { 

            position: absolute; 

            top: 12px; 

            right: 16px; 

            font-size: 24px; 

            cursor: pointer; 

            color: #333; 

        } 

 

        .modal-icon { 

            font-size: 55px; 

            margin-bottom: 10px; 

        } 

 

        .modal-title { 

            margin-bottom: 10px; 

        } 

 

        .modal-message { 

            margin-top: 10px; 

            line-height: 1.6; 

        } 

 

        .modal-actions { 

            margin-top: 20px; 

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

        <a href="../available_pets.php">Available Pets</a> 

        <a href="dashboard.php">Dashboard</a> 

        <a href="profile.php">Profile</a> 

        <a href="../logout.php">Logout</a> 

    </nav> 

</div> 

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

 

        <?php if(mysqli_num_rows($result) > 0): ?> 

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

 

            <?php while($row = mysqli_fetch_assoc($result)): ?> 

                <?php 

                    $has_documents = !empty($row['document_id']); 

                    $status = $row['status']; 

                    $admin_feedback = $row['admin_feedback'] ?? ''; 

                ?> 

                <tr> 

                    <td> 

                        <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Pet Image"> 

                    </td> 

 

                    <td><?php echo htmlspecialchars($row['pet_name']); ?></td> 

                    <td><?php echo htmlspecialchars($row['species']); ?></td> 

                    <td><?php echo htmlspecialchars($row['breed']); ?></td> 

                    <td><?php echo $row['weight'] !== null ? htmlspecialchars($row['weight']) . " kg" : "N/A"; ?></td> 

                    <td><?php echo htmlspecialchars($row['request_date']); ?></td> 

                    <td><?php echo nl2br(htmlspecialchars($row['reason_for_adoption'])); ?></td> 

 

                    <td> 

                        <?php if($status == "Pending"): ?> 

                            <span class="status pending">Pending</span> 

 

                        <?php elseif($status == "Approved"): ?> 

                            <button 

                                type="button" 

                                class="status-popup-btn approved" 

                                data-pet="<?php echo htmlspecialchars($row['pet_name'], ENT_QUOTES); ?>" 

                                data-feedback="<?php echo htmlspecialchars($admin_feedback, ENT_QUOTES); ?>" 

                                onclick="openApprovedModal(this)" 

                            > 

                                Approved 

                            </button> 

 

                        <?php else: ?> 

                            <button 

                                type="button" 

                                class="status-popup-btn rejected" 

                                data-pet="<?php echo htmlspecialchars($row['pet_name'], ENT_QUOTES); ?>" 

                                data-feedback="<?php echo htmlspecialchars($admin_feedback, ENT_QUOTES); ?>" 

                                onclick="openRejectedModal(this)" 

                            > 

                                Rejected 

                            </button> 

                        <?php endif; ?> 

                    </td> 

 

                    <td> 

                        <?php if($has_documents): ?> 

                            <span class="doc-status doc-submitted">Submitted</span> 

                        <?php else: ?> 

                            <span class="doc-status doc-missing">Not Submitted</span> 

                        <?php endif; ?> 

 

                        <?php if($status == "Pending"): ?> 

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

            <?php endwhile; ?> 

        </table> 

        <?php else: ?> 

            <h3>You haven't submitted any adoption requests yet.</h3> 

            <br> 

            <a href="../available_pets.php" class="btn">Browse Pets</a> 

        <?php endif; ?> 

    </div> 

</div> 

 

<!-- Approved Modal --> 

<div id="approvedModal" class="modal"> 

    <div class="modal-content"> 

        <span class="close-modal" onclick="closeApprovedModal()">&times;</span> 

 

        <div class="modal-icon">🎉</div> 

        <h2 class="modal-title approved">Congratulations!</h2> 

 

        <p class="modal-message"> 

            Your adoption request has been approved. 

        </p> 

 

        <p id="approvedPetText" class="modal-message"></p> 

        <p id="approvedFeedbackText" class="modal-message"></p> 

 

        <div class="modal-actions"> 

            <button class="btn" type="button" onclick="closeApprovedModal()">Close</button> 

        </div> 

    </div> 

</div> 

 

<!-- Rejected Modal --> 

<div id="rejectedModal" class="modal"> 

    <div class="modal-content"> 

        <span class="close-modal" onclick="closeRejectedModal()">&times;</span> 

 

        <div class="modal-icon">❗</div> 

        <h2 class="modal-title rejected">Rejected</h2> 

 

        <p class="modal-message"> 

            Your adoption request was not approved. 

        </p> 

 

        <p id="rejectedPetText" class="modal-message"></p> 

        <p id="rejectedFeedbackText" class="modal-message"></p> 

 

        <div class="modal-actions"> 

            <button class="btn" type="button" onclick="closeRejectedModal()">Close</button> 

        </div> 

    </div> 

</div> 

 

<footer> 

    <p>© PawConnect</p> 

</footer> 

 

<script> 

function openApprovedModal(btn) { 

    const petName = btn.getAttribute("data-pet") || ""; 

    const feedback = btn.getAttribute("data-feedback") || ""; 

 

    document.getElementById("approvedPetText").innerHTML = 

        petName ? "<strong>Pet:</strong> " + petName : ""; 

 

    document.getElementById("approvedFeedbackText").innerHTML = 

        feedback ? "<strong>Message:</strong> " + feedback : "Your request was approved successfully."; 

 

    document.getElementById("approvedModal").style.display = "block"; 

} 

 

function closeApprovedModal() { 

    document.getElementById("approvedModal").style.display = "none"; 

} 

 

function openRejectedModal(btn) { 

    const petName = btn.getAttribute("data-pet") || ""; 

    const feedback = btn.getAttribute("data-feedback") || ""; 

 

    document.getElementById("rejectedPetText").innerHTML = 

        petName ? "<strong>Pet:</strong> " + petName : ""; 

 

    document.getElementById("rejectedFeedbackText").innerHTML = 

        feedback ? "<strong>Reason:</strong> " + feedback : "No reason was provided."; 

 

    document.getElementById("rejectedModal").style.display = "block"; 

} 

 

function closeRejectedModal() { 

    document.getElementById("rejectedModal").style.display = "none"; 

} 

 

window.onclick = function(event) { 

    const approvedModal = document.getElementById("approvedModal"); 

    const rejectedModal = document.getElementById("rejectedModal"); 

 

    if (event.target === approvedModal) closeApprovedModal(); 

    if (event.target === rejectedModal) closeRejectedModal(); 

}; 

</script> 

 

</body> 

</html> 

 

 