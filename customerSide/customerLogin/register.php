<?php
// Include your database connection code here (not shown in this example).
require_once '../config.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Define variables and initialize them to empty values
$email = $member_name = $password = $phone_number = "";
$email_err = $member_name_err = $password_err = $phone_number_err = "";
$data_privacy_err = "";

// Check if the form was submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email. Ex: johndoe@email.com";
    } else {
        $email = trim($_POST["email"]);
    }

    if (!isset($_POST["data_privacy"])) {
        $data_privacy_err = "You must agree to the data privacy policy.";
    }

    $selectCreatedEmail = "SELECT email from Accounts WHERE email = ?";

    if ($stmt = $link->prepare($selectCreatedEmail)) {
        $stmt->bind_param("s", $_POST['email']);

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email already exists
            $email_err = "This email is already registered.";
        } else {
            $email = trim($_POST["email"]);
        }
        $stmt->close();
    }

    // Validate member name
    if (empty(trim($_POST["member_name"]))) {
        $member_name_err = "Please enter your member name.";
    } else {
        $member_name = trim($_POST["member_name"]);
    }

    // Validate password
    $PASSWORD = trim($_POST["password"]);
    $uppercase = preg_match('@[A-Z]@', $PASSWORD);
    $lowercase = preg_match('@[a-z]@', $PASSWORD);
    $number = preg_match('@[0-9]@', $PASSWORD);
    $specialChars = preg_match('@[^\w]@', $PASSWORD);

    if (empty($PASSWORD)) {
        $password_err = "Please enter a password.";
    } elseif (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($PASSWORD) < 8) {
        $password_err = "Password must be at least 8 characters long, 
        include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } else {
        // Hash the password using bcrypt
        $password = password_hash($PASSWORD, PASSWORD_BCRYPT);
    }

    // Validate phone number
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty(trim($_POST["phone_number"]))) {
            $phone_number_err = "Please enter your phone number.";
        } elseif (!preg_match("/^\+60[0-9]{9,10}$/", trim($_POST["phone_number"]))) {
            // Use regex to ensure that the phone number matches the pattern "+60" followed by 9-10 digits
            $phone_number_err = "Please enter a valid phone number (e.g. +60101231234).";
        } else {
            $phone_number = trim($_POST["phone_number"]);
        }
    }

    // Generate a verification token
    $verification_token = bin2hex(random_bytes(4)); // Generates an 8-character token

    // Check input errors before inserting into the database
    if (empty($email_err) && empty($member_name_err) && empty($password_err) && empty($phone_number_err) && empty($data_privacy_err)) {
        // Start a transaction
        mysqli_begin_transaction($link);

        // Prepare an insert statement for Accounts table with verification token
        $sql_accounts = "INSERT INTO Accounts (email, password, phone_number, register_date, verification_token, is_verified) VALUES (?, ?, ?, NOW(), ?, 0)";

        if ($stmt_accounts = mysqli_prepare($link, $sql_accounts)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt_accounts, "ssss", $param_email, $param_password, $param_phone_number, $verification_token);

            // Set parameters
            $param_email = $email;
            $param_password = $password;
            $param_phone_number = $phone_number;

            // Attempt to execute the prepared statement for Accounts table
            if (mysqli_stmt_execute($stmt_accounts)) {
                $last_account_id = mysqli_insert_id($link);

                $sql_memberships = "INSERT INTO Memberships (member_name, points, account_id) VALUES (?, ?, ?)";
                if ($stmt_memberships = mysqli_prepare($link, $sql_memberships)) {
                    mysqli_stmt_bind_param($stmt_memberships, "sii", $param_member_name, $param_points, $last_account_id);

                    $param_member_name = $member_name;
                    $param_points = 0;

                    if (mysqli_stmt_execute($stmt_memberships)) {
                        mysqli_commit($link);

                        $phpmailer = new PHPMailer();
                        $phpmailer->isSMTP();
                        $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
                        $phpmailer->SMTPAuth = true;
                        $phpmailer->Port = 2525;
                        $phpmailer->Username = '89be80e74f9b17';
                        $phpmailer->Password = '7b93a7e872a9cd';

                        $phpmailer->setFrom('restaurant@yourdomain.com', 'JohhnyRestaurant');
                        $phpmailer->addAddress($email);
                        $phpmailer->isHTML(true);
                        $phpmailer->Subject = 'Email Verification';
                        $phpmailer->Body = "Your verification code is: <b>$verification_token</b>";

                        if ($phpmailer->send()) {
                            $_SESSION['email'] = $email;
                            header("Location: verify.php");
                            exit();
                        } else {
                            echo "Verification email could not be sent. Mailer Error: " . $phpmailer->ErrorInfo;
                        }
                    } else {
                        mysqli_rollback($link);
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    mysqli_stmt_close($stmt_memberships);
                }
            } else {
                mysqli_rollback($link);
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt_accounts);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration Form</title>
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

        .register-container {
            padding: 50px;
            border-radius: 10px;
            margin: 100px auto;
            max-width: 500px;
        }

        .register_wrapper {
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

        .fa-flip {
            animation: fa-flip 3s infinite;
        }

        @keyframes fa-flip {
            0% {
                transform: scale(1) rotateY(0);
            }

            50% {
                transform: scale(1.2) rotateY(180deg);
            }

            100% {
                transform: scale(1) rotateY(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register_wrapper">
            <a class="nav-link" href="../home/home.php#hero">
                <h1 class="text-center" style="font-family:Copperplate; color:white;"> JOHNNY'S</h1>
            </a><br>
            <form action="register.php" method="post">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" placeholder="Enter Email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <span class="text-danger"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Member Name</label>
                    <input type="text" name="member_name" class="form-control" placeholder="Enter Member Name"
                        value="<?php echo isset($_POST['member_name']) ? htmlspecialchars($_POST['member_name']) : ''; ?>">
                    <span class="text-danger"><?php echo $member_name_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter Password">
                    <span class="text-danger"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" placeholder="Enter Phone Number"
                        value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                    <span class="text-danger"><?php echo $phone_number_err; ?></span>
                </div>
                <!-- Data Privacy Checkbox -->
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="data_privacy" name="data_privacy">
                    <label class="form-check-label" for="data_privacy">I agree to the <a href="./privacy_policy.php"
                            target="_blank">Data Privacy Policy</a></label>
                    <span class="text-danger"><?php echo $data_privacy_err; ?></span>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary btn-block" value="Create Account">
                </div>
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
</body>

</html>