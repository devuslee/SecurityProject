<?php
// reservation.php
require_once '../config.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the values from the form
    $customer_name = $_POST["customer_name"];
    $table_id = intval($_POST["table_id"]);
    $reservation_time = $_POST["reservation_time"];
    $reservation_date = $_POST["reservation_date"];
    $special_request = $_POST["special_request"];
    // Backend validation: Check if reservation date is in the past
    $current_date = date("Y-m-d");
    if (new DateTime($reservation_date) < new DateTime($current_date)) {
        // Redirect back to the reservation page with an error message
        header("Location: reservePage.php?error=invalid_date");
        exit();
    }
    // Fetch table capacity
    $select_query_capacity = "SELECT capacity FROM restaurant_tables WHERE table_id='$table_id';";
    $results_capacity = mysqli_query($link, $select_query_capacity);

    if ($results_capacity) {
        $row = mysqli_fetch_assoc($results_capacity);
        $head_count = $row['capacity'];

        // Generate a unique reservation ID
        $reservation_id = intval($reservation_time) . intval($reservation_date) . intval($table_id);

        // Prepare the SQL query for insertion
        $insert_query1 = "INSERT INTO Reservations (reservation_id, customer_name, table_id, reservation_time, reservation_date, head_count, special_request) 
                          VALUES ('$reservation_id', '$customer_name', '$table_id', '$reservation_time', '$reservation_date', '$head_count', '$special_request');";
        $insert_query2 = "INSERT INTO Table_Availability (availability_id, table_id, reservation_date, reservation_time, status) 
                          VALUES ('$reservation_id', '$table_id', '$reservation_date', '$reservation_time',  'no');";

        // Execute the queries and check for errors
        if (mysqli_query($link, $insert_query1) && mysqli_query($link, $insert_query2)) {
            // Success, redirect to success page
            $_SESSION['customer_name'] = $customer_name;
            header("Location: reservePage.php?reservation=success&reservation_id=$reservation_id");
        } else {
            // Handle SQL errors
            echo "Error inserting reservation: " . mysqli_error($link);
        }
    } else {
        // Handle the case where fetching table capacity failed
        echo "Error fetching table capacity: " . mysqli_error($link);
    }
}
?>