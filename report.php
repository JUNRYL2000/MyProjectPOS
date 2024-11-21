<?php
// Database connection
require 'db.php';  // Ensure db.php has the correct connection settings
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>

    <!-- Include Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
   /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f0f2f5;
    color: #333;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px;
}

/* Header Styling */
h1 {
    margin-top: 10%;
    color: #6464af;
    font-size: 50px;
    font-weight: 600;
    margin-bottom: 25px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Button Container */
.button-container {
    display: flex;
    flex-direction: column; /* Stack buttons vertically */
    gap: 20px;
    align-items: center;
    width: 100%; /* Full-width container */
    max-width: 300px; /* Set max width for larger screens */
}

/* Report Button Styling */
.report-button {
    background-color: #6464af;
    color: white;
    text-decoration: none;
    padding: 14px 0;
    width: 100%; /* Full width for consistent sizing */
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center; /* Center text and icons */
    font-size: 18px; /* Increase font size for readability */
    font-weight: 500;
    height: 60px; /* Fixed button height for consistency */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.report-button i {
    margin-right: 10px;
    font-size: 20px;
}

/* Button Specific Colors */
.sales-report {
    background-color: #6464af;
}

.delivery-report {
    background-color:#6464af; /* Darker shade for distinction */
}

.back-menu {
    background-color: #6464af;
}

/* Hover and Active Effects */
.report-button:hover {
    background-color: #5a5aa3; /* Slightly darker shade for hover */
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.sales-report:hover,
.back-menu:hover {
    background-color: #5858a1;
}

.delivery-report:hover {
    background-color: #424275;
}

.report-button:active {
    transform: translateY(0);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    h1 {
        font-size: 24px;
        text-align: center;
    }

    .button-container {
        width: 90%; /* Adjust button container for smaller screens */
    }
}


    </style>
</head>

<body>
    <h1>Sales and Deliveries Reports</h1>

    <div class="button-container">
        <a href="salesreport.php" class="report-button sales-report" aria-label="View Sales Report">
            <i class="fas fa-chart-line"></i> Sales Report
        </a>
        <a href="deliveryreport.php" class="report-button delivery-report" aria-label="View Delivery Report">
            <i class="fas fa-truck"></i> Delivery Report
        </a>
        <a href="admin_dashboard.php" class="report-button back-menu" aria-label="Back to Admin Dashboard">
            <i class="fas fa-arrow-left"></i> Back Menu
        </a>
    </div>
</body>

</html>
