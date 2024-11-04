<?php
include 'db.php'; // Include your database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle user activation/deactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = $_GET['id'];
    $currentStatus = $_GET['action'] === 'activate' ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
    $stmt->bind_param("ii", $currentStatus, $userId);
    $stmt->execute();
    $stmt->close();
}

// Fetch users from the database
$sql = "SELECT id, username, user_type, status FROM users";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #1abc9c;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .logout {
            margin-top: 20px;
            background-color: #e74c3c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h2>User Management</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>User Type</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            $statusText = $user['status'] ? 'Active' : 'Inactive';
            $action = $user['status'] ? 'Deactivate' : 'Activate';
            $actionLink = $user['status'] ? 'deactivate' : 'activate';
            echo "<tr>
                    <td>{$user['id']}</td>
                    <td>{$user['username']}</td>
                    <td>{$user['user_type']}</td>
                    <td>$statusText</td>
                    <td><a href=\"?action=$actionLink&id={$user['id']}\">$action</a></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No users found.</td></tr>";
    }
    ?>
</table>
<br>
<br>
<a class="logout" href="admin_dashboard.php">Logout</a>
<br>

</body>
</html>

<?php
$conn->close();
?>
