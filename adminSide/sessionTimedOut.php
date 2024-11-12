<?php
// Start the session
session_start();

// Destroy the session if it exists
if (isset($_SESSION['logged_account_id'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Timed Out</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        h1 {
            color: #ff0000; /* Red color for emphasis */
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff; /* Bootstrap primary color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
    </style>
</head>
<body>
    <h1>Session Timed Out</h1>
    <p>Your session has expired due to inactivity. Please log in again to continue.</p>
    <a href="StaffLogin/login.php">Go to Login Page</a> <!-- Update the link to your staff login page -->
</body>
</html>
