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
    /* General Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        background-color: white;
        color: #6464AF;
    }

    h1 {
        text-align: center;
        font-size: 2em;
        margin: 20px 0;
        color: #6464AF;
    }

    /* Navigation Styles */
    nav {
        background-color: #6464AF;
        padding: 20px;
        display: flex;
        justify-content: space-between; /* Push menu to the left and text to the right */
        align-items: center; /* Vertically center all elements in the nav */
    }

    .menu {
        display: flex;
        flex-direction: column; /* Stack the menu items vertically */
       
    }

    .menu a {
        color: white;
        width: 100%;
        display: block;
        text-decoration: none;
        padding: 20px;
        margin: 5px 10px;
        font-size: 1.4em;
        border-radius: 5px;
        background-color: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
    }

    .menu a:hover {
        background-color: white;
        color: #6464AF;
        font-weight: bold;
    }

    /* Menu Icons */
    .menu a i {
        margin-right: 8px;
    }

    /* Right-aligned text */
    .text {
        margin-right: 15%;
        color: white; /* Adjust color as needed */
        font-size: 4.5em;
        font-weight: bold;
        text-align: right; /* Align text to the right */
    }

    .text .typing {
        color: white; /* Typing animation color */
    }

    /* Responsive */
    @media (max-width: 768px) {
        nav {
            text-align: center;
            flex-direction: column; /* Stack the nav content vertically on small screens */
        }

        .menu {
            flex-direction: row; /* Menu items will align horizontally */
            justify-content: center;
        }

        .text {
            margin-top: 20px; /* Add space if necessary */
        }
    }

</style>

<h1>Welcome to the Admin Dashboard</h1>

<nav>
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
    <div class="text">
        <span class="typing"></span>
    </div>
</nav>


<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.12/typed.min.js"></script>
<script>
    var typing = new Typed(".typing", {
        strings: ["Hi, Have A Good Day!"],
        typeSpeed: 150,
        backSpeed: 60,
        loop: true
    });
</script>

</body>
</html>
