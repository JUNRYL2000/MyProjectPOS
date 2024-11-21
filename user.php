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
    if ($_GET['action'] === 'delete') {
        // Delete the user from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        // Activate or deactivate the user
        $currentStatus = $_GET['action'] === 'activate' ? 1 : 0;
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param("ii", $currentStatus, $userId);
        $stmt->execute();
        $stmt->close();
    }
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
      /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f4f4f9;
    color: #333;
    font-size: 16px;
    line-height: 1.5;
    padding: 20px;
}

h2 {
    color: #6464AF;
    text-align: center;
    margin-bottom: 20px;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #6464AF;
    color: white;
}

td {
    background-color: #fff;
}

/* Action Links */
a {
    color: #4A90E2;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    color: #333;
    text-decoration: underline;
}

/* Logout Button */
.logout {
    display: inline-block;
    background-color: #6464AF;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    margin-top: 20px;
}

.logout:hover {
    background-color: #4A90E2;
}

/* Table Row Hover Effect */
tr:hover {
    background-color: #f2f2f2;
}

/* Responsive Design */
@media (max-width: 768px) {
    table {
        font-size: 14px;
    }

    th, td {
        padding: 8px;
    }

    .logout {
        font-size: 14px;
        padding: 8px 12px;
    }
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
                    <td>
                        <a href=\"?action=$actionLink&id={$user['id']}\">$action</a> |
                        <a href=\"?action=delete&id={$user['id']}\" onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a>
                    </td>
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
