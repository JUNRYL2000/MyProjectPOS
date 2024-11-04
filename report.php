<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "myprojectpos";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delivery Report
function getDeliveryReport($conn) {
    $sql = "
        SELECT dt.TransactionID, dt.Delivery_Date, p.Description, dtd.Quantity, dtd.Cost, dtd.Total_Cost, s.StoreName, dt.OR_No
        FROM deliverytransaction AS dt
        JOIN deliverytransactiondetails AS dtd ON dt.TransactionID = dtd.TransactionID
        JOIN product AS p ON dtd.ProductID = p.ProductID
        JOIN store AS s ON dt.StoreID = s.StoreID
        ORDER BY dt.Delivery_Date DESC
    ";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<h2>Delivery Report</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Transaction ID</th><th>Delivery Date</th><th>Description</th><th>Quantity</th><th>Cost</th><th>Total Cost</th><th>Store Name</th><th>OR No</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['TransactionID']}</td>
                <td>{$row['Delivery_Date']}</td>
                <td>{$row['Description']}</td>
                <td>{$row['Quantity']}</td>
                <td>{$row['Cost']}</td>
                <td>{$row['Total_Cost']}</td>
                <td>{$row['StoreName']}</td>
                <td>{$row['OR_No']}</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "No delivery records found.";
    }
}

// Sales Report
function getSalesReport($conn) {
    $sql = "
        SELECT st.Sales_date, p.Description, std.Quantity, std.Price, std.Total_Amt, sr.Receipt_No
        FROM saletransaction AS st
        JOIN salestransactiondetails AS std ON st.SaleTransactionID = std.SaleTransactionID
        JOIN product AS p ON std.ProductID = p.ProductID
        JOIN salesreceipt AS sr ON st.Receipt_No = sr.Receipt_No
        ORDER BY st.Sales_date DESC
    ";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<h2>Sales Report</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Sales Date</th><th>Description</th><th>Quantity</th><th>Price</th><th>Total Amount</th><th>Receipt No</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['Sales_date']}</td>
                <td>{$row['Description']}</td>
                <td>{$row['Quantity']}</td>
                <td>{$row['Price']}</td>
                <td>{$row['Total_Amt']}</td>
                <td>{$row['Receipt_No']}</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "No sales records found.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Reports</title>
    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>POS Reports</h1>

<!-- Buttons to trigger modals -->
<button id="deliveryBtn">View Delivery Report</button>
<button id="salesBtn">View Sales Report</button>

<!-- Delivery Report Modal -->
<div id="deliveryModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeDelivery">&times;</span>
        <?php getDeliveryReport($conn); ?>
    </div>
</div>

<!-- Sales Report Modal -->
<div id="salesModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeSales">&times;</span>
        <?php getSalesReport($conn); ?>
    </div>
</div>

<script>
    // Get the modals
    var deliveryModal = document.getElementById("deliveryModal");
    var salesModal = document.getElementById("salesModal");

    // Get the buttons that open the modals
    var deliveryBtn = document.getElementById("deliveryBtn");
    var salesBtn = document.getElementById("salesBtn");

    // Get the <span> elements that close the modals
    var closeDelivery = document.getElementById("closeDelivery");
    var closeSales = document.getElementById("closeSales");

    // When the user clicks the button, open the corresponding modal
    deliveryBtn.onclick = function() {
        deliveryModal.style.display = "block";
    }

    salesBtn.onclick = function() {
        salesModal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    closeDelivery.onclick = function() {
        deliveryModal.style.display = "none";
    }

    closeSales.onclick = function() {
        salesModal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == deliveryModal) {
            deliveryModal.style.display = "none";
        }
        if (event.target == salesModal) {
            salesModal.style.display = "none";
        }
    }
</script>

</body>
</html>

<?php
// Close connection
$conn->close();
?>
