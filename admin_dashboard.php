<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Admin Dashboard</title>
    
</head>
<body>
<style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #ff6d00, #ff9201, #ffab41, #ff6d00);
            height: 100vh; /* Full screen height */
            margin: 0;
           
        }
        .menu a {
            color: white;
            text-decoration: none;
            padding: 20px;
            display: flex;
            align-items: center;
            border-radius: 7px;
            transition: background 0.3s, transform 0.2s; /* Smooth transitions */
            margin-top: 20px;
           
        }
        .menu a:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Light hover effect */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }
        .menu i {
            margin-right: 10px; /* Space between icon and text */
        }
        </style>

<h1>Welcome to the Admin Dashboard</h1>
<nav>
    <ul>
    <div class="menu">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="sales.php"><i class="fas fa-shopping-cart"></i>Sales Management</a>
        <a href="delivery.php"><i class="fas fa-truck"></i>Delivery Management</a>
        <a href="product_management.php"><i class="fas fa-box"></i>Inventory Management</a>
        <a href="store_management.php"><i class="fas fa-store"></i>Store Management</a>
        <a href="customer.php"><i class="fas fa-users"></i>Customer Management</a>
        <a href="user.php"><i class="fas fa-user-cog"></i>User Management</a>
        <a href="report.php"><i class="fas fa-file-alt"></i>Reports</a>
        <a href="login.php"><i class="fas fa-sign-out-alt"></i>Log Out</a>
    </div>
    </ul>
</nav>

</body>
</html>
