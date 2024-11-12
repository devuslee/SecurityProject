<?php
session_start(); // Ensure session is started
?>
<?php include '../inc/dashHeader.php'; ?>
<?php
// Include config file
require_once "../config.php";

// Initialize variables for user input fields
$staff_id = $account_id = $email = $register_date = $phone_number = $password = "";
$staff_id_err = $account_id_err = $email_err = $register_date_err = $phone_number_err = $password_err = "";

// Processing form data when form is submitted
if (isset($_POST['submit'])) {
    if (empty($_POST['staff_id'])) {
        $staff_id_err = 'ID is required';
    } else {
        $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}

// Function to get the next available account ID
function getNextAvailableAccountID($conn) {
    $sql = "SELECT MAX(account_id) as max_account_id FROM Accounts";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['max_account_id'] + 1;
}

// Function to get the next available Staff ID
function getNextAvailableStaffID($conn) {
    $sql = "SELECT MAX(staff_id) as max_staff_id FROM Staffs";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['max_staff_id'] + 1;
}

// Get the next available Staff ID
$next_staff_id = getNextAvailableStaffID($link);

// Get the next available account ID
$next_account_id = getNextAvailableAccountID($link);
?>
<head>
    <meta charset="UTF-8">
    <title>Create New Staff</title>
    <style>
       .wrapper{ width: 1300px; padding-left: 200px ; padding-top: 80px; }
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
    <h3>Create New Staff</h3>
    <p>Please fill in the Staff Information</p>

    <form method="POST" action="succ_create_staff.php" class="ht-600 w-50">

        <div class="form-group">
            <label for="staff_id" class="form-label">Staff ID:</label>
            <input min="1" type="number" name="staff_id" placeholder="1" class="form-control <?php echo $staff_id_err ? 'is-invalid' : ''; ?>" id="staff_id" required value="<?php echo $next_account_id; ?>" readonly><br>
            <div class="invalid-feedback">
                Please provide a valid staff_id.
            </div>
        </div>

        <div class="form-group">
            <label for="staff_name">Staff Name:</label>
            <input type="text" name="staff_name" placeholder="Johnny Hatsoff" id="staff_name" required 
                class="form-control <?php echo (!empty($staff_name_err)) ? 'is-invalid' : ''; ?>" 
                value="<?php echo isset($staff_name) ? htmlspecialchars($staff_name) : ''; ?>"><br>
            <span class="invalid-feedback"><?php echo $staff_name_err; ?></span>
        </div>

        <div class="form-group mb-3">
            <label for="role">Role:</label>
            <select name="role" id="role" required class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                <option value="" disabled selected>Select Role</option>
                <option value="Waiter" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Waiter') ? 'selected' : ''; ?>>Waiter</option>
                <option value="Chef" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Chef') ? 'selected' : ''; ?>>Chef</option>
                <option value="Manager" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
            </select>
            <span class="invalid-feedback"><?php echo $role_err; ?></span>
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
            <input type="date" name="register_date" id="register_date" required class="form-control <?php echo !$register_date_err ?: 'is-invalid'?>" value="<?php echo htmlspecialchars($register_date); ?>"><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                Please provide a valid register date.
            </div>
        </div>

        <div class="form-group">
            <label for="phone_number" class="form-label">Phone Number:</label>
            <input type="text" name="phone_number" placeholder="+60101231234" 
                class="form-control <?php echo !empty($phone_numberErr) ? 'is-invalid' : ''; ?>" 
                id="phone_number" required value="<?php echo htmlspecialchars($phone_number); ?>" 
                pattern="^\+60[0-9]{9,10}$"><br>
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
                value="<?php echo htmlspecialchars($password); ?>"><br>
            <div id="validationServerFeedback" class="invalid-feedback">
                Please provide a valid password.
            </div>
        </div>
        
        <div class="form-group mb-5">
            <input type="submit" name="submit" class="btn btn-dark" value="Create Staff" id="submitBtn" disabled> <!-- Disable initially -->
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
