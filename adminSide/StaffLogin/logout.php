<?php
// Start the session
session_start();

// Include database connection
require_once '../config.php';

// Log user logout action
function logUserAction($user_id, $action, $page_url) {
    global $link;

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $link->prepare("INSERT INTO user_logs (user_id, action, page_url, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $action, $page_url, $ip_address, $user_agent);
    
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in
if (isset($_SESSION['logged_account_id'])) {
    // Log the logout action
    $user_id = $_SESSION['logged_account_id'];
    logUserAction($user_id, 'Logout', 'logout.php');
}

// Unset session variables and destroy the session
unset($_SESSION['logged_account_id']);
unset($_SESSION['logged_staff_name']);
session_destroy();

// Redirect the user to the home page
header("Location: ../../customerSide/home/home.php"); // Adjust "home.php" to your destination
exit();
?>
