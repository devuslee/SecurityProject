<?php
// Include the database connection and logging function
require_once '../config.php';
session_start();

// Define the logging function
function logUserAction($user_id, $action, $page_url) {
    global $link;

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $link->prepare("INSERT INTO user_logs (user_id, action, page_url, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $action, $page_url, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is already logged out
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Redirect to home page
    header("Location: ../home/home.php");
    exit;
}

// Log the logout action before destroying the session
$user_id = $_SESSION['account_id'] ?? null; // Adjust to your session variable for user ID
logUserAction($user_id, 'Logout', 'logout.php');

// Unset custom cookies (change cookie_name to the actual name of your custom cookie)
setcookie('cookie_name', '', time() - 3600, '/');

// Clear session data and destroy session
$_SESSION = array();
session_destroy();

// Prevent caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// Redirect to the home page
header("Location: ../home/home.php");
exit;
