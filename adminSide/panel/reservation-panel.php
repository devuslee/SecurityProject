<?php
session_start(); // Ensure session is started

$timeout_duration = 300; // 15 minutes

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

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

require_once '../posBackend/checkIfLoggedIn.php';
?>
<?php include '../inc/dashHeader.php'; ?>
    <style>
        .wrapper { width: 1300px; padding-left: 200px; padding-top: 20px; }
        .highlight-today { background-color:   #FCF4A3; font-weight: bold; } /* Highlight today's reservations */
    </style>
<div class="wrapper">
    <div class="container-fluid pt-5 pl-600">
        <div class="row">
            <div class="m-50">
                <div class="mt-5 mb-3">
                    <h2 class="pull-left">Reservation Details</h2>
                    <a href="../reservationsCrud/createReservation.php" class="btn btn-outline-dark"><i class="fa fa-plus"></i> Add Reservation</a>
                </div>
                <div class="mb-3">
                    <form method="POST" action="#">
                        <div class="row">
                            <div class="col-md-6">
                                <input required type="text" id="search" name="search" class="form-control" placeholder="Enter Reservation ID, Customer Name, Reservation Date (2023-09)">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark">Search</button>
                            </div>
                            <div class="col" style="text-align: right;">
                                <a href="reservation-panel.php" class="btn btn-light">Show All</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <?php
                // Include config file
                require_once "../config.php";

                // Get today's date (in Malaysia time)
                $today = date('Y-m-d');

                // Default query to fetch all reservations
                $sql = "SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC;";

                // Search logic
                if (isset($_POST['search']) && !empty($_POST['search'])) {
                    $search = $_POST['search'];
                    $sql = "SELECT * FROM reservations WHERE reservation_date LIKE '%$search%' OR reservation_id LIKE '%$search%' OR customer_name LIKE '%$search%'";
                }

                // Execute the query
                if ($result = mysqli_query($link, $sql)) {
                    // Arrays to hold upcoming and past reservations
                    $upcomingReservations = [];
                    $pastReservations = [];

                    // Loop through all reservations and categorize them
                    while ($row = mysqli_fetch_array($result)) {
                        $reservationDate = $row['reservation_date'];

                        // Compare reservation date to today
                        if ($reservationDate < $today) {
                            $pastReservations[] = $row;
                        } else {
                            $upcomingReservations[] = $row;
                        }
                    }

                    // Function to display reservation table
                    function displayReservationTable($reservations, $today = null, $isUpcoming = false) {
                        if (count($reservations) > 0) {
                            echo '<table class="table table-bordered table-striped">';
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>Reservation ID</th>";
                            echo "<th>Customer Name</th>";
                            echo "<th>Table ID</th>";
                            echo "<th>Reservation Time</th>";
                            echo "<th>Reservation Date</th>";
                            echo "<th>Head Count</th>";
                            echo "<th>Special Request</th>";
                            echo "<th>Delete</th>";
                            echo "<th>Receipt</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            
                            foreach ($reservations as $row) {
                                $highlightClass = '';

                                // Highlight today's reservations
                                if ($isUpcoming && $row['reservation_date'] == $today) {
                                    $highlightClass = 'highlight-today';
                                }

                                echo "<tr class='$highlightClass'>";
                                echo "<td>" . $row['reservation_id'] . "</td>";
                                echo "<td>" . $row['customer_name'] . "</td>";
                                echo "<td>" . $row['table_id'] . "</td>";
                                echo "<td>" . $row['reservation_time'] . "</td>";
                                echo "<td>" . $row['reservation_date'] . "</td>";
                                echo "<td>" . $row['head_count'] . "</td>";
                                echo "<td>" . $row['special_request'] . "</td>";
                                echo "<td>";
                                echo '<a href="../reservationsCrud/deleteReservationVerify.php?id='. $row['reservation_id'] .'" title="Delete Record" data-toggle="tooltip" '
                                       . 'onclick="return confirm(\'Admin permission Required!\n\nAre you sure you want to delete this Reservation?\n\nThis will alter other modules related to this Reservation!\n\')"><span class="fa fa-trash text-black"></span></a>';
                                echo "</td>";
                                echo "<td>";
                                echo '<a href="../reservationsCrud/reservationReceipt.php?reservation_id='. $row['reservation_id'] .'" title="Receipt" data-toggle="tooltip"><span class="fa fa-receipt text-black"></span></a>';
                                echo "</td>";
                                echo "</tr>";
                            }

                            echo "</tbody>";
                            echo "</table>";
                        } else {
                            echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                        }
                    }

                    // Display Upcoming Reservations
                    echo "<h3>Upcoming Reservations</h3>";
                    displayReservationTable($upcomingReservations, $today, true);

                    // Display Past Reservations
                    echo "<h3>Past Reservations</h3>";
                    displayReservationTable($pastReservations);

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
