<?php
session_start(); // Ensure session is started

$timeout_duration = 300; // 15 minutes

// Check if the user is logged in
if (isset($_SESSION['logged_account_id'])) {
    // Check if the last activity time is set
    if (isset($_SESSION['last_activity'])) {
        // Calculate the session's lifetime
        $session_life = time() - $_SESSION['last_activity'];
        
        // If the session has expired, destroy the session and redirect to login
        if ($session_life > $timeout_duration) {
            session_unset(); // Unset all session variables
            session_destroy(); // Destroy the session
            header("Location: ../sessionTimedOut.php");
            exit;
        }
    }
    // Update the last activity time
    $_SESSION['last_activity'] = time(); // Update last activity time to current time
} else {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit;
}


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    // If the user is not a manager, redirect them to the home page or an "unauthorized" page
    header("Location: ../unauthorized.php");
    exit();
}

require_once '../posBackend/checkIfLoggedIn.php';
?>
<?php include '../inc/dashHeader.php'; ?>
    <style>
        .wrapper{ width: 1300px; padding-left: 200px; padding-top: 20px  }
    </style>

<div class="wrapper">
    <div class="container-fluid pt-5 pl-600">
        <div class="row">
            <div class="m-50">
                <div class="mt-5 mb-3">
                    <h2 class="pull-left">Account Details</h2>
                    <a href="../staffCrud/createStaff.php" class="btn btn-outline-dark"><i class="fa fa-plus"></i> Add Staff</a>
                    <a href="../customerCrud/createCust.php" class="btn btn-outline-dark"><i class="fa fa-plus"></i> Add Memberships</a>
                </div>
                
                <div class="mb-3">
                    <form method="POST" action="#">
                        <div class="row">
                            <div class="col-md-6">
                                <input required type="text" id="search" name="search" class="form-control" placeholder="Enter Account ID, Email">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark">Search</button>
                            </div>
                            <div class="col" style="text-align: right;" >
                                <a href="account-panel.php" class="btn btn-light">Show All</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <?php
                // Include config file
                require_once "../config.php";

                if (isset($_POST['search'])) {
                    if (!empty($_POST['search'])) {
                        $search = $_POST['search'];

                        $sql = "SELECT *
                                FROM Accounts
                                WHERE email LIKE '%$search%' OR account_id LIKE '%$search%'
                                ORDER BY account_id;";
                    } else {
                        // Default query to fetch all accounts
                        $sql = "SELECT *
                                FROM Accounts
                                ORDER BY account_id;";
                    }
                } else {
                    // Default query to fetch all accounts
                    $sql = "SELECT *
                            FROM Accounts
                            ORDER BY account_id;";
                }

                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                        echo '<table class="table table-bordered table-striped">';
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Account ID</th>";
                        echo "<th>Email</th>";
                        echo "<th>Register Date</th>";
                        echo "<th>Phone Number</th>";
                        // echo "<th>Password</th>";
                        //echo "<th>Account Type</th>"; // Display account type
                       // echo "<th>Delete</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['account_id'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['register_date'] . "</td>";
                            echo "<td>" . $row['phone_number'] . "</td>";
                            // echo "<td>" . $row['password'] . "</td>";
                            //echo "<td>" . ucfirst($row['account_type']) . "</td>"; // Display account type
                          //  echo "<td>";
                          //  $deleteSQL = "DELETE FROM Accounts WHERE account_id = '" . $row['account_id'] . "';";
                           // echo '<a href="../accountCrud/deleteAccountVerify.php?id=' . $row['account_id'] . '" title="Delete Record" data-toggle="tooltip" '
                           //         . 'onclick="return confirm(\'Admin permission Required!\n\nAre you sure you want to delete this Account?\n\nThis will alter other modules related to this Account!\n\')"><span class="fa fa-trash text-black"></span></a>';
                           // echo "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                        // Free result set
                        mysqli_free_result($result);
                    } else {
                        echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close connection
                mysqli_close($link);
                ?>
            </div>
        </div>
    </div>
</div>

<?php include '../inc/dashFooter.php'; ?>
