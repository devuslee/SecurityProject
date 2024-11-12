<?php
// Include your database connection code here (not shown in this example).
require_once '../config.php';
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

    if($stmt = $link->prepare($selectCreatedEmail)){
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

    // Check input errors before inserting into the database
    if (empty($email_err) && empty($member_name_err) && empty($password_err) && empty($phone_number_err) && empty($data_privacy_err)) {
        // Start a transaction
        mysqli_begin_transaction($link);

        // Prepare an insert statement for Accounts table
        $sql_accounts = "INSERT INTO Accounts (email, password, phone_number, register_date) VALUES (?, ?, ?, NOW())";

        if ($stmt_accounts = mysqli_prepare($link, $sql_accounts)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt_accounts, "sss", $param_email, $param_password, $param_phone_number);

            // Set parameters
            $param_email = $email;
            // Store the password as plain text (not recommended for production)
            $param_password = $password;
            $param_phone_number = $phone_number;

            // ...
        }

        // Attempt to execute the prepared statement for Accounts table
        if (mysqli_stmt_execute($stmt_accounts)) {
            // Get the last inserted account_id
            $last_account_id = mysqli_insert_id($link);

            // Prepare an insert statement for Memberships table
            $sql_memberships = "INSERT INTO Memberships (member_name, points, account_id) VALUES (?, ?, ?)";
            if ($stmt_memberships = mysqli_prepare($link, $sql_memberships)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt_memberships, "sii", $param_member_name, $param_points, $last_account_id);

                // Set parameters for Memberships table
                $param_member_name = $member_name;
                $param_points = 0; // You can set an initial value for points

                // Attempt to execute the prepared statement for Memberships table
                if (mysqli_stmt_execute($stmt_memberships)) {
                    // Commit the transaction
                    mysqli_commit($link);

                    // Registration successful, redirect to the login page
                    header("location: register_process.php");
                    exit;
                } else {
                    // Rollback the transaction if there was an error
                    mysqli_rollback($link);
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close the statement for Memberships table
                mysqli_stmt_close($stmt_memberships);
            }
        } else {
            // Rollback the transaction if there was an error
            mysqli_rollback($link);
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close the statement for Accounts table
        mysqli_stmt_close($stmt_accounts);
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
            margin: 0; /* Remove default margin */
            background-color:black;
             background-image: url('../image/loginBackground.jpg'); /* Set the background image path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white;
            }


        
/* Style for the container within login.php */
.register-container {
  padding: 50px; /* Adjust the padding as needed */
  border-radius: 10px; /* Add rounded corners */
  margin: 100px auto; /* Center the container horizontally */
  max-width: 500px; /* Set a maximum width for the container */
}
        .register_wrapper {
            width: 400px; /* Increase the container width */
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
            margin-bottom: 15px; /* Add space between form elements */
        }

        ::placeholder {
            font-size: 12px; /* Adjust the font size as needed */
        }

        /* Add flip animation class to all Font Awesome icons */
        .fa-flip {
            animation: fa-flip 3s infinite;
        }

        /* Keyframes for the flip animation */
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
        <a class="nav-link" href="../home/home.php#hero"> <h1 class="text-center" style="font-family:Copperplate; color:white;"> JOHNNY'S</h1><span class="sr-only"></span></a><br>
       
        <form action="register.php" method="post">
            <!-- Email Field -->
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" class="form-control" placeholder="Enter Email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <span class="text-danger"><?php echo $email_err; ?></span>
            </div>

            <!-- Member Name Field -->
            <div class="form-group">
                <label>Member Name</label>
                <input type="text" name="member_name" class="form-control" placeholder="Enter Member Name"
                       value="<?php echo isset($_POST['member_name']) ? htmlspecialchars($_POST['member_name']) : ''; ?>">
                <span class="text-danger"><?php echo $member_name_err; ?></span>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter Password">
                <span class="text-danger"><?php echo $password_err; ?></span>
            </div>

            <!-- Phone Number Field -->
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" class="form-control" placeholder="Enter Phone Number"
                       value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                <span class="text-danger"><?php echo $phone_number_err; ?></span>
            </div>

            <!-- Data Privacy Checkbox -->
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="data_privacy" name="data_privacy">
                <label class="form-check-label" for="data_privacy">I agree to the <a href="../privacyPolicy.html" target="_blank">Data Privacy Policy</a></label>
                <span class="text-danger"><?php echo $data_privacy_err; ?></span>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <input type="submit" class="btn btn-primary btn-block" value="Create Account">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
    </div>
</body>
</html>
