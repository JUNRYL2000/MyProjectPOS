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

// Query to fetch grouped sales report data by date
$query = "
    SELECT 
        p.ProductID,
        p.Description AS ProductDescription,
        s.Price,
        SUM(s.Quantity) AS Quantity,
        SUM(s.Price * s.Quantity) AS Total_Amt,
        GROUP_CONCAT(DISTINCT u.username ORDER BY u.username ASC SEPARATOR ', ') AS UserName,
        DATE(t.Sales_date) AS Sales_date
    FROM
        saletransaction t
    JOIN 
        salestransactiondetails s ON t.SaleTransactionID = s.SaleTransactionID
    JOIN
        product p ON s.ProductID = p.ProductID
    JOIN 
        users u ON t.id = u.id
    ";
    
    // Add date filter if provided
    if (!empty($date_start) && !empty($date_end)) {
    $query .= " WHERE t.Sales_date BETWEEN '$date_start' AND '$date_end'";
    } else {
    $query .= " WHERE 1=1";
    }
    // Add user filter if provided
    if (!empty($user_id)) {
    $query .= " AND u.id = '$user_id'";
    }
    $query .= "
    GROUP BY 
        p.ProductID, p.Description, s.Price, Sales_date
    ORDER BY 
        Sales_date, p.ProductID, s.Price
    ";

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
    /* General Styles */
    body {
    font-family: Arial, sans-serif;
    background-color:#8484cd;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    }

    h1 {
    color: #6464af;
    text-align: center;
    font-size: 30px;
    margin-bottom: 20px;
    font-weight: bold;

    }

    /* Report Container */
    .report-container {
    width: 90%;
    max-width: 900px;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Filters Form */
    .container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
    }
    .container label {
    font-weight: bold;
    color: #6464af;
    font-size: 22px;
    }

    .container input[type="date"],
    .container select {
    padding: 8px;
    border-radius: 5px;
    border: 3px solid #ddd;
    font-size: 16px;
    width: 31.4%;
    max-width: 300px;
    }

    .container button {
    padding: 10px 20px;
    background-color: #6464af;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    max-width: 150px;
    align-self: flex-start;
    margin-top: 10px;
    }

    .container button:hover {
    background-color: #5a5aa3;
    }

    .container .back-menu {
    background-color:#6464af;
    text-decoration: none;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    }

    .container .back-menu:hover {
    background-color: #424275;
    }

    /* Sales Report Table */
    .container1 table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    }

    .container1 table, .container1 th, .container1 td {
    border: 1px solid black;
    }

    .container1 th, .container1 td {
    padding: 10px;
    text-align: center;
    }

    .container1 th {
    background-color:#8484cd;
    color: black;
    font-weight: bold;
    }

    .container1 td {
    background-color: #f9f9f9;
    }

    .container1 tr:nth-child(even) {
    background-color: #f2f2f2;
    }

    /* Summary Section */
    .summary {
    margin-top: 20px;
    font-weight: bold;
    }

    .summary p {
    margin: 5px 0;
    font-size: 18px;
    color: #333;
    }

    .summary p u {
    color: black;
    }

    /* Print Button */
    #printButton {
    padding: 10px 20px;
    background-color: #6464af;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
    }

    #printButton:hover {
    background-color: #5a5aa3;
    }

    /* Print Styles */
    @media print {
    #printButton {
        display: none;
    }
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
<div class="report-container">
    <h1>Sales Record</h1>

    <!-- Report Filters Form -->
    <div class="container">
        <form method="POST">
            <label for="date_start">Start Date :</label>
            <input type="date" name="date_start" value="<?php echo htmlspecialchars($date_start); ?>">
            <label style="margin-left: 4.5%;" for="date_end">End Date :</label>
            <input type="date" name="date_end" value="<?php echo htmlspecialchars($date_end); ?>"><br>
            <label for="user_id">User :</label>
            <select style="margin-left: 6.2%; width: 100%; margin-top: 10px;" name="user_id">
                <option value="">All Users</option>
                <?php
                    // Fetch users for the dropdown
                    $user_query = "SELECT id, username FROM users";
                    $user_result = mysqli_query($conn, $user_query);
                    while ($user_row = mysqli_fetch_assoc($user_result)) {
                    echo "<option value='{$user_row['id']}'";
                    if ($user_row['id'] == $user_id) echo " selected";
                    echo ">{$user_row['username']}</option>";
                    }
                ?>
            </select>
            <br>
            <br>
            <button type="submit">View Record</button>
            <a href="report.php" class="report-button back-menu">Back</a>
        </form>
    </div>

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

    <!-- Sales Report Table -->
    <div class="container1">
        <h1 style="text-align: center;">Sales Report</h1>
        <table>
            <tr>
                <th>Product ID</th>
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

        <!-- Summary Section -->
        <div class="summary">
            <p >Total Sales: <u> ₱ <?php echo number_format($total_sales, 2); ?> </u></p>
            <p>Total Transactions:   <u> <?php echo $total_transactions; ?> Transactions </u></p>
            <p>Total Products Sold:  <u> <?php echo $total_products_sold; ?> Sold </u></p>
            <button id="printButton" onclick="printSaleReport()">Print Sales Report</button>
        </div>
    </div>
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
</body>
</html>
