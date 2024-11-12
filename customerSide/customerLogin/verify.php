<?php
// Include database connection
require_once '../config.php';
session_start();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the verification token from the form submission
    $verification_token = $_POST['verification_code'] ?? '';

    // Check if token is provided
    if (empty($verification_token)) {
        die("Verification token is missing.");
    }

    // Prepare and execute the SELECT statement to find the account with the given token
    $sql = "SELECT account_id FROM Accounts WHERE verification_token = ? AND is_verified = 0";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("s", $verification_token);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Get account ID
            $row = $result->fetch_assoc();
            $account_id = $row['account_id'];

            // Free the result set before continuing
            $result->free();

            // Update the account to set is_verified to 1
            $update_sql = "UPDATE Accounts SET is_verified = 1 WHERE account_id = ?";
            $update_stmt = $link->prepare($update_sql);
            $update_stmt->bind_param("i", $account_id);

            if ($update_stmt->execute()) {
                echo "Your account has been successfully verified.";
                header("Location: login.php");
                exit;
            } else {
                echo "Error updating account status: " . $link->error;
            }

            // Close the update statement
            $update_stmt->close();
        } else {
            echo "Invalid or expired verification token.";
        }
    } else {
        echo "Error executing query: " . $link->error;
    }

    // Close the connection
    $stmt->close();
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Account</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: black;
            background-image: url('../image/loginBackground.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white;
        }
        .verify-container {
            padding: 50px;
            border-radius: 10px;
            margin: 100px auto;
            max-width: 500px;
            background-color: rgba(0, 0, 0, 0.7); /* semi-transparent background */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.5);
        }
        h2 {
            text-align: center;
            font-family: 'Montserrat', serif;
            color: #FFFFFF;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-custom {
            background-color: #28a745;
            border: none;
            color: white;
            width: 100%;
        }
        .text-danger {
            font-size: 13px;
        }
        ::placeholder {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <h2>Email Verification</h2>
        <form action="verify.php" method="post">
            <div class="form-group">
                <label for="verification_code">Enter Verification Code</label>
                <input type="text" name="verification_code" id="verification_code" class="form-control" placeholder="Enter your verification code" required>
            </div>
            <button type="submit" class="btn btn-custom">Verify</button>
        </form>
    </div>
</body>
</html>
