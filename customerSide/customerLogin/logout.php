<?php
// Include the database connection and logging function
require_once '../config.php';
session_start();

// Define the logging function with session dates
function logUserAction($user_id, $username, $action, $page_url, $start_session_date = null, $end_session_date = null) {
    global $link;

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Insert log with start and end session dates
    $stmt = $link->prepare("INSERT INTO user_logs (user_id, username, action, page_url, ip_address, user_agent, start_session_date, end_session_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $username, $action, $page_url, $ip_address, $user_agent, $start_session_date, $end_session_date);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Retrieve the user ID and username from session, setting default values if not found
    $user_id = $_SESSION['account_id'] ?? null;
    $username = $_SESSION['logged_staff_name'] ?? 'Customer'; // Set default username if not provided

    // Get session start date and set the end date to the current time
    $start_session_date = $_SESSION['start_session_date'] ?? null;
    $end_session_date = date("Y-m-d H:i:s"); // Set the end session date as the current time

    // Log the logout action with start and end session dates
    logUserAction($user_id, $username, "Logout", 'logout.php', $start_session_date, $end_session_date);

    // Clear any custom cookies (if any)
    setcookie('cookie_name', '', time() - 3600, '/');

    // Clear session data and destroy the session
    $_SESSION = array();
    session_destroy();

    // Prevent caching
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Set a past date to prevent caching
}

// Redirect to the home page
header("Location: ../home/home.php");
exit;
?>