<?php
session_start(); // Ensure session is started

require_once "../config.php";

// Check if 'id' is set and not empty
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $table_id = intval($_GET['id']);
} else {
    // Redirect to reservation panel if no valid table ID is provided
    header("Location: ../panel/reservation-panel.php");
    exit(); // Make sure to exit after redirect
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user-provided input to prevent XSS
    $provided_account_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $provided_password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

    // Combine admin ID and password to create a unique string
    $uniqueString = $provided_account_id . $provided_password;

    // Simple hardcoded credential check (replace with a more secure mechanism for production)
    if ($uniqueString == "9999912345") {
        echo 'Correct';
        // Redirect to delete the reservation after successful login
        header("Location: ../reservationsCrud/deleteReservation.php?id=" . $table_id);
        exit(); // Ensure the script stops after the redirect
    } else {
        // If credentials are incorrect, display an alert
        echo '<script>alert("Incorrect ID or Password!")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="../css/verifyAdmin.css" rel="stylesheet" />
</head>
<body>
    <div class="login-container">
        <div class="login_wrapper">
            <div class="wrapper">
                <h2 style="text-align: center;">Admin Login</h2>
                <h5>Admin Credentials needed to Delete Reservation</h5>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="admin_id">Admin ID</label>
                        <input type="number" name="admin_id" class="form-control" placeholder="Enter Admin ID" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter Admin Password" required>
                    </div>

                    <button class="btn btn-light" type="submit" name="submit" value="submit">Delete Reservation</button>
                    <a class="btn btn-danger" href="../panel/reservation-panel.php" >Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
