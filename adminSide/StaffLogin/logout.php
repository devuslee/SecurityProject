<?php
// Start the session
session_start();

// Include database connection
require_once '../config.php';

// Log user action with start and end session details
function logUserAction($user_id, $username, $action, $page_url, $start_session_date = null, $end_session_date = null) {
    global $link;

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $start_session_date = $start_session_date ?? date("Y-m-d H:i:s");

    // Insert log with optional end session date for logout
    $stmt = $link->prepare("INSERT INTO user_logs (user_id, username, action, page_url, ip_address, user_agent, start_session_date, end_session_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $username, $action, $page_url, $ip_address, $user_agent, $start_session_date, $end_session_date);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in
if (isset($_SESSION['logged_account_id'])) {
    $user_id = $_SESSION['logged_account_id'];
    $username = $_SESSION['logged_staff_name']; // Assuming 'logged_staff_name' stores the username

    // Log the logout action with end session timestamp
    logUserAction($user_id, $username, 'Logout', 'logout.php', $_SESSION['start_session_date'], date("Y-m-d H:i:s"));
}

// Unset all session variables and destroy the session
$_SESSION = array(); // Clear all session variables
session_destroy();

// Redirect the user to the home page
header("Location: ../../customerSide/home/home.php"); // Adjust "home.php" to your destination
exit();
?>