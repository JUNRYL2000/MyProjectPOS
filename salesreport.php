<?php
// Database connection
require 'db.php';  // Ensure db.php has the correct connection settings
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables for filters
$date_start = isset($_POST['date_start']) ? $_POST['date_start'] : '';
$date_end = isset($_POST['date_end']) ? $_POST['date_end'] : '';
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

// Query to fetch sales report data
$query = "
    SELECT 
        p.ProductID,
        p.Description AS ProductDescription,
        p.Price,
        std.Quantity,
        std.Total_Amt,
        u.username AS UserName,
        st.Sales_date
    FROM saletransaction st
    INNER JOIN salestransactiondetails std ON st.SaleTransactionID = std.SaleTransactionID
    INNER JOIN product p ON std.ProductID = p.ProductID
    INNER JOIN users u ON st.id = u.id
    WHERE 1 = 1
";

// Add date filter if provided
if (!empty($date_start) && !empty($date_end)) {
    $query .= " AND st.Sales_date BETWEEN '$date_start' AND '$date_end'";
}

// Add user filter if provided
if (!empty($user_id)) {
    $query .= " AND u.id = '$user_id'";
}

// Execute the query
$result = mysqli_query($conn, $query);

// Initialize variables for the summary section
$total_sales = 0;
$total_transactions = 0;
$total_products_sold = 0;
?>

<!DOCTYPE html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            height: 100vh; /* Full screen height */  
            display: fixed; 
            background: linear-gradient(to right, #ff6d00, #ff9201, #ffab41, #ff6d00);
        }
        .report-container {
            width: 80%;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: black;
        }
        .summary {
            margin-top: 20px;
            font-weight: bold;
        }
        .container{
            width:70%;
            background-color: lightgray;
            height: 10em;
            padding: 10px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            
          
        }
        .container1{
            background-color: lightgray;
            width: 100%;
            padding: 10px;
            border-top-right-radius: 10px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
          
        }
        table {
        margin: 0 auto;
        border-collapse: collapse;
        width: 97%; /* Adjust width as needed */
        background-color: white;
        }
        th, td {
        padding: 10px;
        border: 1px solid black;
        text-align: left;
        }

    </style>
</head>

<body>
<div class="report-container">
    <h1>Sales Record</h1>

    <!-- Report Filters Form -->
    <div class="container">
        <form method="POST">
        <label style="font-size; 30em;
                      font-weight: bold; 
                      padding: 7px; 
                      margin-top: 12px; font-size: 22px;" for="date_start">Start Date :</label>
        <input style="font-size: 150%;
                      border-radius: 5px;
                      border-color: white;    
                      font-weight: bold; 
                      width: 30%; 
                      height: 5vh; 
                      margin-top: 12px;" type="date" name="date_start" value="<?php echo htmlspecialchars($date_start); ?>">

       
        <label style="font-size; 30em;  
                      font-weight: bold; 
                      padding: 10px;
                      margin-left: 20px; 
                      font-size: 22px;" for="date_end">End Date :</label>
        <input style="font-size: 150%;
                      border-radius: 5px;   
                      font-weight: bold;
                      border-color: white; 
                      width: 30%; 
                      height: 5vh;" type="date" name="date_end" value="<?php echo htmlspecialchars($date_end); ?>">
        <br>

        <label style="font-size; 30em; 
                      font-weight: bold; 
                      padding: 10px; 
                      margin-top: 20px; font-size: 22px;" for="user_id">User :</label>
        <select style="margin-top: 20px;
                       border-radius: 5px;
                       border-color: white;  
                       width: 30%; 
                       height: 5vh; 
                       margin-left: 50px;
                       font-size: 150%;
                        font-weight: bold;"  name="user_id">
            <option style="font-size: 100%; 
                           font-weight: bold;" value="">All Users</option>
            <?php
            // Fetch users for the dropdown
            $user_query = "SELECT id, username FROM users";
            $user_result = mysqli_query($conn, $user_query);
            while ($user_row = mysqli_fetch_assoc($user_result)) {
                echo "<option value='{$user_row['id']}'";
                if ($user_row['id'] == $user_id) echo " selected";
                echo " style='font-size: 100%;  font-weight: bold;'>{$user_row['username']}</option>";
            }
            ?>
         </select>   
         <br>   
         <br>
<style>
button, .report-button {
    font-size: 18px;
    padding: 10px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
    margin-left: 14%;
    font-weight: bold;
    text-align: center;
}
/* View Button */
button[type="submit"] {
    background-color: #4CAF50; /* Green background */
    color: white;
    width: 15%;
    height: 2em;
    margin-left: 15%;
   
}
button[type="submit"]:hover {
    background-color: #45a049; /* Darker green on hover */
}
/* Back Link Button */
.report-button.back-menu {
    display: inline-block;
    text-decoration: none;
    background-color: #2196F3; /* Blue background */
    color: white;
    margin-left: 15px;
    width: 13%;
    height: 1em;
  
   
}
.report-button.back-menu:hover {
    background-color: #0b7dda; /* Darker blue on hover */
}
</style>

<button  type="submit">View</button>        
    <a href="report.php" class="report-button back-menu">Back</a>
  </form>
</div>


<div>
<!-- Sales Report Table -->
<style>
    @media print {
    #printButton {
    display: none;}}  
    /* Hide other content when printing */
     @media print {
    body * {
    visibility: hidden;
    }
    .container1, .container1 * {
    visibility: visible;
    }
    .container1 {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    }
    }
</style>

</head>
<body>

<div class="container1">
    <h1 style="text-align: center;">Sales Report</h1>
    <table>
        <tr>
            <th>ProdID</th>
            <th>Product Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total Amount</th>
            <th>User Name</th>
            <th>Sales Date</th>
        </tr>
        
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['ProductID']}</td>";
                echo "<td>{$row['ProductDescription']}</td>";
                echo "<td>₱ " . number_format($row['Price'], 2) . "</td>";
                echo "<td>{$row['Quantity']}</td>";
                echo "<td>₱ " . number_format($row['Total_Amt'], 2) . "</td>";
                echo "<td>{$row['UserName']}</td>";
                echo "<td>{$row['Sales_date']}</td>";
                echo "</tr>";

                // Update summary variables
                $total_sales += $row['Total_Amt'];
                $total_transactions++;
                $total_products_sold += $row['Quantity'];
            }
        } else {
            echo "<tr><td colspan='7'>No data found</td></tr>";
        }
        ?>
        
    </table>

    <style>
    </style>
    <!-- Summary Section -->
    <div class="summary">
        <p style="margin-left: 10%; font-size: 25px">Total Sales: ₱ <?php echo number_format($total_sales, 2); ?></p>
        <p style="margin-left: 10%; font-size: 25px">Total Transactions: <?php echo $total_transactions; ?></p>
        <p style="margin-left: 10%; font-size: 25px; margin-bottom: 7px;">Total Products Sold: <?php echo $total_products_sold; ?></p>
    </div>
    <br><br>
 

</body>
</html>
<button style="width: 12%; 
               height: 55px; 
               background-color: #3498db;
               border-radius: 5px;
               margin-left: 80%;
               font-weight: bold;
               margin-bottom: 20px;" id="printButton" onclick="printSaleReport()">Print Sale Report</button>
</div>

<script>
    function printSaleReport() {
        window.print();
    }
</script>

<?php
// Close the database connection
mysqli_close($conn);
?>