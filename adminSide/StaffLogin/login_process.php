<?php
session_start(); // Ensure session is started

require_once "../config.php";

// Log user login attempt
function logUserAction($user_id, $action, $page_url) {
    global $link;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $link->prepare("INSERT INTO user_logs (user_id, action, page_url, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $action, $page_url, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User-provided input
    $provided_account_id = $_POST['account_id'];
    $provided_password = $_POST['password'];

    // Query to fetch staff record based on provided account_id
    $query = "SELECT * FROM Accounts WHERE account_id = '$provided_account_id'";
    $result = $link->query($query);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        if ($provided_password === $stored_password) {
            // Password matches, login successful

            // Check if the account_id exists in the Staffs table
            $staff_query = "SELECT * FROM Staffs WHERE account_id = '$provided_account_id'";
            $staff_result = $link->query($staff_query);

            if ($staff_result->num_rows === 1) {
                $staff_row = $staff_result->fetch_assoc();
                $logged_staff_name = $staff_row['staff_name']; // Get staff_name

                // Log the successful login
                logUserAction($provided_account_id, 'Login successful', 'login.php');

                // After successful login, store staff_name in session
                $_SESSION['logged_account_id'] = $provided_account_id;
                $_SESSION['logged_staff_name'] = $logged_staff_name;
                
                // Redirect to the pos panel upon successful login
                header("Location: ../panel/pos-panel.php");
                exit;
            } else {
                // Staff ID not found in Staffs table
                $message = "Staff ID not found.<br>Please try again to choose a correct Staff ID.";
                $iconClass = "fa-times-circle";
                $cardClass = "alert-danger";
                $bgColor = "#FFA7A7"; // Custom background color for error
                $direction = "login.php"; // Fail, go back to login

                // Log the failed login
                logUserAction($provided_account_id, 'Login failed - Staff ID not found', 'login.php');
            }      
            
        } else {
            // Password is incorrect
            $message = "Incorrect password.<br>Please try again to type your password.";
            $iconClass = "fa-times-circle";
            $cardClass = "alert-danger";
            $bgColor = "#FFA7A7"; // Custom background color for error
            $direction = "login.php"; // Fail back to login

            // Log the failed login
            logUserAction($provided_account_id, 'Login failed - Incorrect password', 'login.php');
        }
    } else {
        // Account ID not found
        $message = "Staff ID not found.<br>Please try again to choose a correct Staff ID.";
        $iconClass = "fa-times-circle";
        $cardClass = "alert-danger";
        $bgColor = "#FFA7A7";
        $direction = "login.php"; // Fail back to login

        // Log the failed login
        logUserAction($provided_account_id, 'Login failed - Account ID not found', 'login.php');
    }
}

// Close the database connection
$link->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        body {
            text-align: center;
            padding: 40px 0;
            background: #EBF0F5;
        }
        h1 {
            color: #88B04B;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-weight: 900;
            font-size: 40px;
            margin-bottom: 10px;
        }
        p {
            color: #404F5E;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-size: 20px;
            margin: 0;
        }
        i.checkmark {
            color: #9ABC66;
            font-size: 100px;
            line-height: 200px;
            margin-left: -15px;
        }
        .card {
            background: white;
            padding: 60px;
            border-radius: 4px;
            box-shadow: 0 2px 3px #C8D0D8;
            display: inline-block;
            margin: 0 auto;
        }
        .alert-success {
            background-color: <?php echo $bgColor; ?>;
        }
        .alert-danger {
            background-color: #FFA7A7;
        }
        .alert-danger i {
            color: #F25454;
        }
        .custom-x {
            color: #F25454;
            font-size: 100px;
            line-height: 200px;
        }
    </style>
</head>
<body>
    <div class="card <?php echo $cardClass; ?>" style="display: none;">
        <div style="border-radius: 200px; height: 200px; width: 200px; background: #F8FAF5; margin: 0 auto;">
            <?php if ($iconClass === 'fa-check-circle'): ?>
                <i class="checkmark">✓</i>
            <?php else: ?>
                <i class="custom-x" style="font-size: 100px; line-height: 200px;">✘</i>
            <?php endif; ?>
        </div>
        <h1><?php echo ($cardClass === 'alert-success') ? 'Success' : 'Error'; ?></h1>
        <p><?php echo $message; ?></p>
    </div>

    <div style="text-align: center; margin-top: 20px;">Redirecting back in <span id="countdown">3</span></div>

    <script>
        var direction = "<?php echo $direction; ?>";
        
        function showPopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "block";

            var i = 3;
            var countdownElement = document.getElementById("countdown");
            var countdownInterval = setInterval(function() {
                i--;
                countdownElement.textContent = i;
                if (i <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = direction;
                }
            }, 1000);
        }

        window.onload = showPopup;
        
        function hidePopup() {
            var messageCard = document.querySelector(".card");
            messageCard.style.display = "none";
            setTimeout(function () {
                window.location.href = direction;
            }, 3000);
        }
        
        setTimeout(hidePopup, 3000);
    </script>
</body>
</html>
