<?php
// Include your database connection code here
require_once '../config.php';
session_start();

// Define the logging function with username and session dates
function logUserAction($user_id, $username, $action, $page_url, $start_session_date, $end_session_date) {
    global $link;

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Insert log with start and end session dates
    $stmt = $link->prepare("INSERT INTO user_logs (user_id, username, action, page_url, ip_address, user_agent, start_session_date, end_session_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $username, $action, $page_url, $ip_address, $user_agent, $start_session_date, $end_session_date);
    $stmt->execute();
    $stmt->close();
}

// Define variables for email and password
$email = $password = "";
$email_err = $password_err = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check input errors before checking authentication
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT * FROM Accounts WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            // Set parameters
            $param_email = $email;

            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Get the result
                $result = mysqli_stmt_get_result($stmt);

                // Check if a matching record was found
                if (mysqli_num_rows($result) == 1) {
                    // Fetch the result row
                    $row = mysqli_fetch_assoc($result);
                    $account_id = $row['account_id']; // Store account ID for logging
                    $username = !empty($row['username']) ? $row['username'] : 'Customer'; 

                    // Check if the account is verified
                    if ($row["is_verified"] == 0) {
                        $email_err = "Your account has not been verified. Please check your email to verify your account.";
                        $start_end_time = date("Y-m-d H:i:s");
                        logUserAction($account_id, 'Customer', 'Login failed - account not verified', 'login.php', $start_end_time, $start_end_time);
                    } else {
                        // Verify the password
                        if (password_verify($password, $row["password"])) {
                            // Set session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["email"] = $email;
                            $_SESSION["account_id"] = $account_id;
                            $_SESSION["username"] = $username;
                            $_SESSION["start_session_date"] = date("Y-m-d H:i:s"); // Track session start time

                            // Log successful login with start session date
                            logUserAction($account_id, $username, 'Login successful', 'login.php', $_SESSION["start_session_date"], null);

                            // Query to get membership details
                            $sql_member = "SELECT * FROM Memberships WHERE account_id = " . $account_id;
                            $result_member = mysqli_query($link, $sql_member);

                            if ($result_member) {
                                $membership_row = mysqli_fetch_assoc($result_member);

                                if ($membership_row) {
                                    $_SESSION["account_id"] = $membership_row["account_id"];
                                    header("location: ../home/home.php"); // Redirect to the home page
                                    exit;
                                } else {
                                    // No membership details found
                                    $password_err = "No membership details found for this account.";
                                }
                            } else {
                                // Error in membership query
                                $password_err = "Error fetching membership details: " . mysqli_error($link);
                            }
                        } else {
                            // Password is incorrect
                            $password_err = "Invalid password. Please try again.";
                            $start_end_time = date("Y-m-d H:i:s");
                            logUserAction($account_id, 'Customer', 'Login failed - invalid password', 'login.php', $start_end_time, $start_end_time);
                        }
                    }
                } else {
                    // No matching records found
                    $email_err = "No account found with this email.";
                    $start_end_time = date("Y-m-d H:i:s");
                    logUserAction(null, 'Customer', 'Login failed - email not found', 'login.php', $start_end_time, $start_end_time);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Close the database connection
$link->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .login-container {
            padding: 50px;
            border-radius: 10px;
            margin: 100px auto;
            max-width: 500px;
        }

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

        .login_wrapper {
            width: 400px;
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-family: 'Montserrat', serif;
        }

        p {
            font-family: 'Montserrat', serif;
        }

        .form-group {
            margin-bottom: 15px;
        }

        ::placeholder {
            font-size: 12px;
        }

        .text-danger {
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
    <div class="login_wrapper">
        <a class="nav-link" href="../home/home.php#hero"> <h1 class="text-center" style="font-family:Copperplate; color:white;"> JOHNNY'S</h1><span class="sr-only"></span></a>

        <div class="wrapper">
            <form action="login.php" method="post">
                <!-- Email Field -->
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter User Email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    <span class="text-danger"><?php echo isset($email_err) ? htmlspecialchars($email_err, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter User Password" required>
                    <span class="text-danger"><?php echo isset($password_err) ? htmlspecialchars($password_err, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>

                <!-- Login Button -->
                <button class="btn btn-dark" style="background-color:black;" type="submit" name="submit" value="Login">Login</button>
            </form>

            <p style="margin-top:1em; color:white;">Don't have an account? <a href="register.php" style="">Proceed to Register</a></p>
        </div>
    </div>
    </div>
</body>
</html>
