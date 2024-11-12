<?php
// Include database connection
require_once 'C:\xampp\htdocs\SecurityProject\adminSide\config.php';

function getLogs($user_id = null, $start_date = null, $end_date = null) {
    global $link;

    // Base query
    $query = "SELECT * FROM user_logs WHERE 1=1";
    
    // Parameters array for binding
    $params = [];
    $types = "";

    // Add user filter if provided
    if (!empty($user_id)) {
        $query .= " AND user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }

    // Add date filter if provided
    if (!empty($start_date) && !empty($end_date)) {
        $query .= " AND DATE(created_at) BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }

    // Prepare statement
    $stmt = $link->prepare($query);

    // Check if statement preparation was successful
    if ($stmt === false) {
        die("Error preparing query: " . $link->error);
    }

    // Bind parameters dynamically
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }

    // Execute the query and check for errors
    if (!$stmt->execute()) {
        die("Error executing query: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Fetch results
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $stmt->close();
    return $logs;
}

// Fetch logs based on filter inputs
$user_id = $_GET['user_id'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$logs = getLogs($user_id, $start_date, $end_date);
?>

<!-- Styling and Scripts -->
<link href="../css/pos.css" rel="stylesheet" />
<?php include '../inc/dashHeader.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Logs</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin-top: 50px;
        }
        .form-control, .btn {
            margin-bottom: 15px;
        }
        .table {
            margin-top: 20px;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        h2 {
            font-weight: bold;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">User Interaction Logs</h2>
        
        <!-- Filter Form -->
        <form method="GET" action="display_logs.php" class="mb-4">
            <div class="form-row align-items-end">
                <!-- User ID Filter -->
                <div class="form-group col-md-4">
                    <label for="user_id">User ID</label>
                    <input type="number" name="user_id" id="user_id" class="form-control" placeholder="Enter User ID" value="<?php echo htmlspecialchars($_GET['user_id'] ?? '') ?>">
                </div>

                <!-- Start Date Filter -->
                <div class="form-group col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? '') ?>">
                </div>

                <!-- End Date Filter -->
                <div class="form-group col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? '') ?>">
                </div>

                <!-- Filter Button -->
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>

        <!-- Logs Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Action</th>
                    <th>Page URL</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['id']); ?></td>
                            <td><?php echo htmlspecialchars($log['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['page_url']); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($log['user_agent']); ?></td>
                            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
