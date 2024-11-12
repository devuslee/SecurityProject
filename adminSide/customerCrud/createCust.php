<?php
session_start(); // Ensure session is started
?>
<?php include '../inc/dashHeader.php'; ?>
<?php
// Include config file
require_once "../config.php";


// Define variables and initialize them
$member_id = $member_name = $points = $account_id = "";
$member_id_err = $member_name_err = $points_err = "";
$input_account_id = $account_iderr = $account_id = "";
$input_email = $email_err = $email = "";
$input_register_date = $register_date_err = $register_date = "";
$input_phone_number = $phone_number_err = $phone_number = "";
$input_password = $password_err = $password = "";

// Function to get the next available account ID
function getNextAvailableAccountID($conn) {
    $sql = "SELECT MAX(account_id) as max_account_id FROM Accounts";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $next_account_id = $row['max_account_id'] + 1;
    return $next_account_id;
}

// Function to get the next available Member ID
function getNextAvailableMemberID($conn) {
    $sql = "SELECT MAX(member_id) as max_member_id FROM Memberships";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $next_member_id = $row['max_member_id'] + 1;
    return $next_member_id;
}




// Get the next available Member ID
$next_member_id = getNextAvailableMemberID($link);

// Get the next available account ID
$next_account_id = getNextAvailableAccountID($link);


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email to ensure it's not already in the database
    $email = trim($_POST["email"]);
    $sql = "SELECT account_id FROM Accounts WHERE email = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_email);

        // Set the parameter
        $param_email = $email;

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Store result to check if email exists
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $email_err = "This email is already registered.";
            }
        } else {
            echo "Something went wrong. Please try again later.";
        }
        // Close the statement
        mysqli_stmt_close($stmt);
    }
    
    // Proceed to the next page only if there are no errors
    if (empty($email_err)) {
        // If no email error, redirect to the success page
        header("Location: success_createMembership.php");
        exit(); // Terminate the script to prevent further execution
    }
}


?>
<head>
    <meta charset="UTF-8">
    <title>Create New Membership</title>
    <style>
        .wrapper{ width: 1300px; padding-left: 200px; padding-top: 80px; }
        /* Style the select input */
        #account_id {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
        }

        /* Style the default option */
        #account_id option {
            color: #333;
        }

        /* Style the selected option */
        #account_id option:checked {
            background-color: #007bff;
            color: #fff;
        }

        /* Style the select when it's required and empty */
        #account_id:required:invalid {
            color: #999;
            border-color: #f00; /* Red border for validation */
        }

        /* Style the select when it's required and filled */
        #account_id:required:valid {
            border-color: #28a745; /* Green border for validation */
            color: #333;
        }
    </style>
</head>

<div class="wrapper">
    <h3>Create New Membership</h3>
    <p>Please fill in Membership Information</p>

    <form method="POST" action="success_createMembership.php" class="ht-600 w-50">
        
        <div class="form-group">
            <label for="member_id" class="form-label">Member ID:</label>
            <input min="1" type="number" name="member_id" placeholder="1" class="form-control <?php echo $member_id_err ? 'is-invalid' : ''; ?>" id="member_id" required value="<?php echo $next_member_id; ?>" readonly><br>
            <div class="invalid-feedback">
                Please provide a valid member_id.
            </div>
        </div>
        
        <div class="form-group">
            <label for="member_name" class="form-label">Member Name :</label>
            <input type="text" name="member_name" placeholder="Johnny Hatsoff" class="form-control <?php echo $member_name_err ? 'is-invalid' : ''; ?>" id="member_name" required value="<?php echo $member_name; ?>"><br>
            <div class="invalid-feedback">
                Please provide a valid member name.
            </div>
        </div>

        <div class="form-group">
            <label for="points">Points :</label>
            <input type="number" name="points" id="points" placeholder="1234" required class="form-control <?php echo $points_err ? 'is-invalid' : ''; ?>" value="<?php echo $points; ?>"><br>
            <div class="invalid-feedback">
                Please provide valid points.
            </div>
        </div>

        <div class="form-group">
            <label for="account_id" class="form-label">Account ID:</label>
            <input min="1" type="number" name="account_id" placeholder="99" class="form-control <?php echo !$account_idErr ?: 'is-invalid'; ?>" id="account_id" required value="<?php echo $next_account_id; ?>" readonly><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                Please provide a valid account_id.
            </div>
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" placeholder="johnny12@dining.bar.com" 
                class="form-control <?php echo !empty($email_err) ? 'is-invalid' : ''; ?>" 
                id="email" required value="<?php echo htmlspecialchars($email); ?>" 
                onblur="checkEmailAvailability()"><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                <?php echo $email_err; ?> <!-- Display error message if email already exists -->
            </div>
            <small id="emailStatus"></small> <!-- Placeholder for AJAX feedback -->
        </div>
        

        <div class="form-group">
            <label for="register_date">Register Date :</label>
            <input type="date" name="register_date" id="register_date" required class="form-control <?php echo !$register_date_err ?: 'is-invalid';?>" value="<?php echo $register_date; ?>"><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                Please provide a valid register date.
            </div>
        </div>

        <div class="form-group">
            <label for="phone_number" class="form-label">Phone Number:</label>
            <input type="text" name="phone_number" placeholder="+60101231234" 
                class="form-control <?php echo !empty($phone_numberErr) ? 'is-invalid' : ''; ?>" 
                id="phone_number" required value="<?php echo $phone_number; ?>" 
                pattern="^\+60[0-9]{9,10}$"
                id="phone_number" required value="<?php echo $phone_number; ?>"><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                <?php echo $phone_numberErr ?: 'Please provide a valid phone number.'; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password :</label>
            <input type="password" name="password" placeholder="Enter a strong password" id="password" 
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" 
                title="Password must be at least 8 characters long, contain at least one number, one uppercase letter, one lowercase letter, and one special character." 
                required 
                class="form-control <?php echo !$password_err ?: 'is-invalid' ; ?>" 
                value="<?php echo $password; ?>"><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                Please provide a valid password.
            </div>
        </div>
        
        <div class="form-group mb-5">
            <input type="submit" name="submit" class="btn btn-dark" value="Create Membership" id="submitBtn" disabled> <!-- Disable initially -->
        </div>
    </form>
</div>

<script>
    function checkEmailAvailability() {
        var email = document.getElementById("email").value;

        // Create an XMLHttpRequest object
        var xhr = new XMLHttpRequest();

        // Define the request to the server-side script
        xhr.open("POST", "checkEmail.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        // What to do when the response returns
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Get the response text (which is what the PHP will echo)
                var responseText = xhr.responseText;
                document.getElementById("emailStatus").innerHTML = responseText;

                // If email is available, enable the submit button
                if (responseText.trim() === "Email is available.") {
                    document.getElementById("submitBtn").disabled = false;
                } else {
                    document.getElementById("submitBtn").disabled = true; // Keep disabled if email exists
                }
            }
        };

        // Send the request, passing the email value
        xhr.send("email=" + encodeURIComponent(email));
    }
</script>

<?php include '../inc/dashFooter.php'; ?>
