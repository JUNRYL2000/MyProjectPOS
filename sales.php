<?php 
session_start();
include 'db.php';

// Redirect to dashboard if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Determine the dashboard redirect URL
$dashboard_url = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'cashier_dashboard.php';


// Ensure 'cart' is initialized
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
// Assuming you want to display the items in the cart
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {     
    }
} else {
    echo "No items in the cart.";
}

// Fetch cashier details for the logged-in user
$userIdQuery = "SELECT id FROM users WHERE Username = ?";
$stmt = $conn->prepare($userIdQuery);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$userIdResult = $stmt->get_result();
$userData = $userIdResult->fetch_assoc();
$userId = $userData['id'];
$stmt->close();

// Generate receipt number
$receiptQuery = "SELECT MAX(Receipt_No) as max_receipt FROM saletransaction";
$stmt = $conn->prepare($receiptQuery);
$stmt->execute();
$receiptResult = $stmt->get_result();
$receiptRow = $receiptResult->fetch_assoc();
$stmt->close();
$nextReceiptNo = $receiptRow['max_receipt'] + 1; // Increment receipt number

// Search for products
if (isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM product WHERE Description LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Check available quantity
    $productQuery = "SELECT * FROM product WHERE ProductID = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $product = $productResult->fetch_assoc();
    $stmt->close();

    if ($product) {
        if ($quantity > $product['Stock_Quantity']) {
            echo "<script>alert('Cannot add more than available stock: " . $product['Stock_Quantity'] . "');</script>";
        } else {
            $totalAmount = $product['Price'] * $quantity;

            $_SESSION['cart'][] = [
                'ProductID' => $productId,
                'Quantity' => $quantity,
                'Price' => $product['Price'],
                'Total_Amt' => $totalAmount,
                'Description' => $product['Description']
            ];
        }
    } else {
        echo "<script>alert('Product not found.');</script>";
    }
}

// Submit sale
if (isset($_POST['submit_sale'])) {
    // Use isset() to check if the fields are set
    $salesDate = isset($_POST['sales_date']) ? $_POST['sales_date'] : '';
    $receiptNo = $nextReceiptNo; // Use the generated receipt number
    $receivedAmount = isset($_POST['received_amount']) ? $_POST['received_amount'] : 0;
    $saleTotalAmount = 0;

    // Save customer information
    $customerId = null; // Initialize variable
    $customerName = isset($_POST['customer_name']) ? $_POST['customer_name'] : '';
    $customerAddress = isset($_POST['customer_address']) ? $_POST['customer_address'] : '';

    // Validate customer information
    if (empty($customerName) || empty($customerAddress)) {
        echo "<script>alert('Customer name and address are required.');</script>";
    } else {
        // Check if customer already exists
        $customerQuery = "SELECT CustomerID FROM customer WHERE CustomerName = ? AND Address = ?";
        $stmt = $conn->prepare($customerQuery);
        $stmt->bind_param("ss", $customerName, $customerAddress);
        $stmt->execute();
        $customerResult = $stmt->get_result();

        if ($customerResult->num_rows > 0) {
            // Customer exists, get the CustomerID
            $customerData = $customerResult->fetch_assoc();
            $customerId = $customerData['CustomerID'];
        } else {
            // Insert new customer
            $insertCustomerQuery = "INSERT INTO customer (CustomerName, Address) VALUES (?, ?)";
            $stmt = $conn->prepare($insertCustomerQuery);
            $stmt->bind_param("ss", $customerName, $customerAddress);
            $stmt->execute();
            $customerId = $stmt->insert_id; // Get the inserted CustomerID
            $stmt->close();
        }

        foreach ($_SESSION['cart'] as $item) {
            $saleTotalAmount += $item['Total_Amt'];
        }

        // Validate received amount
        if ($receivedAmount < $saleTotalAmount) {
            echo "<script>alert('Error: Received amount is less than the total sale amount.');</script>";
        } else {
           // Insert transaction if validation passes
        
        $stmt = $conn->prepare("INSERT INTO saletransaction (Sales_date, Receipt_No, id, SaleTotal_amount, CustomerID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $salesDate, $receiptNo, $userId, $saleTotalAmount, $customerId);
        if (!$stmt->execute()) {
        echo "<script>alert('Error inserting into saletransaction: " . $stmt->error . "');</script>";
            }

            $saleTransactionId = $stmt->insert_id; // Get the inserted ID
            $stmt->close();

           // Now insert into the receipt table
            $receiptQuery = "INSERT INTO salesreceipt (Receipt_No, id, DateTime) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($receiptQuery);
            $stmt->bind_param("si", $receiptNo, $userId); // Only bind Receipt_No
            if (!$stmt->execute()) {
            echo "<script>alert('Error inserting into receipt table: " . $stmt->error . "');</script>";
            }
            $stmt->close();

            foreach ($_SESSION['cart'] as $item) {
                // Insert sale details
                $stmt = $conn->prepare("INSERT INTO salestransactiondetails (SaleTransactionID, ProductID, Quantity, Price, Total_Amt) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidd", $saleTransactionId, $item['ProductID'], $item['Quantity'], $item['Price'], $item['Total_Amt']);
                $stmt->execute();
                $stmt->close();
            
                // Update product quantity
                $productQuery = "SELECT Stock_Quantity FROM product WHERE ProductID = ?";
                $stmt = $conn->prepare($productQuery);
                $stmt->bind_param("i", $item['ProductID']);
                $stmt->execute();
                $productResult = $stmt->get_result();
                $product = $productResult->fetch_assoc();
                $stmt->close();
            
                if ($product) {
                    $newQuantity = $product['Stock_Quantity'] - $item['Quantity']; // Make sure to use Stock_Quantity
            
                    // Ensure the new quantity is not negative
                    if ($newQuantity >= 0) {
                        $updateQuery = "UPDATE product SET Stock_Quantity = ? WHERE ProductID = ?"; // Ensure you're using Stock_Quantity here
                        $stmt = $conn->prepare($updateQuery);
                        $stmt->bind_param("ii", $newQuantity, $item['ProductID']);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        echo "<script>alert('Insufficient stock for product: " . htmlspecialchars($item['Description']) . "');</script>";
                        
                    }
                }
            }
            

            $change = $receivedAmount - $saleTotalAmount;

            // Save receipt details to session
            $_SESSION['receipt'] = [
                'store_name' => 'Boning Store',
                'date' => $salesDate,
                'receipt_no' => $receiptNo,
                'items' => $_SESSION['cart'],
                'total_amount' => $saleTotalAmount,
                'received_amount' => $receivedAmount,
                'change' => number_format($change, 2),
                'cashier_name' => $cashier['Username'],
                'customer_name' => $customerName,
                'customer_address' => $customerAddress
            ];

            // Set success message
            $_SESSION['success_message'] = "Sale transaction saved successfully!";
            
            unset($_SESSION['cart']);
            header("Location: sales.php"); // Redirect to receipt page
            exit();
        }
    }
}
// Remove item from cart
if (isset($_POST['remove_item'])) {
    $itemKey = $_POST['item_key'];
    if (isset($_SESSION['cart'][$itemKey])) {
        unset($_SESSION['cart'][$itemKey]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales</title>
<style>
    .container1 {
    width: 55%;
    background: lightgray;
    padding: 10px;
    margin-left: 50px;
    }
    .container2{
    width: 66.2%;
    background: lightgray;
    margin-left: 10px;
    }
    .container3{
    right: 20px;
    top: 20px;
    width: 50%;
    position: absolute;
    padding: 10px;
    background: lightgray;
    width: 25%;
    margin-right: 15px;
    margin-top: 15px;
    
    }
    input[type="text"], input[type="number"], input[type="date"] {
    width: calc(70% - 10px);
    padding: 10px;
    margin: 6px;
    border: 2px solid gray;
    border-radius: 4px;
    font-size: 18px;
    font-weight: bold;
    margin-left: 50px;
    height: 20px;
    }
    input[type="text1"]{
    width: 70%;
    height: 40px;
    font-size: 25px;
    font-weight: bold;
    margin-left: 30px;
    border: 3px solid gray;
    margin-top: 5px;

    }
    .table {
    width: 60%;
    background-color: #fff;
    padding: 20px;
    border-radius: 0px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 2px;
   
    }   
    table {
    width: 93%;
    border-collapse: separate;
    margin: 0;
    margin-left: 3%;
    margin-top: 20px;
    color: black;
    }
    table td {
    padding: 12px;
    text-align: left;
    border-bottom: 5px solid #ddd;
    text-align: center;
    }
    table th {
    background-color:  #ffab41;
    font-size: 25px;
    font-weight: bold;
    text-align: center;
    
    
    }
    table td, input[type="text"]{
    font-size: 27px;
    font-weight: bold;
    color: black;
    }
    .result, input[type="number"], input[type="hidden"]{
    font-size: 20px;
    font-weight: bold;
    color: black;
    margin-left: 50px;
    width: calc(70% - 10px);
    }
    #running_total {
    font-weight: bold;
    font-size: 25px;
    color: #333;
    text-align: left;
    width: 70%; /* Adjusted width for input field */
    background-color: #f4f7fc;
    border: none;
    margin-left: 5px; /* Added space between ₱ and total */
    }
    .run{
    font-size: 30px;
    font-weight: bold;
    color: solid black;
    }
    td span {
    font-weight: bold;
    font-size: 30px;
    color: #333;
    }
    #change_display {
    margin-top: 10px;
    font-weight: bold;
    font-size: 30px;
    color: black;
    }
    button {
    background-color:  #6464AF;
    color: white;
    border: none;
    padding: 10px 12px;
    cursor: pointer;
    border-radius: 4px;
    font-weight: bold;
    font-size: 18px;
    margin-left: 20px;
    }
    button[type="submit1"]{
    background-color:  #6464AF;
    color: white;
    border: none;
    padding: 10px 12px;
    cursor: pointer;
    border-radius: 4px;
    font-weight: bold;
    font-size: 18px;
    margin-left: 40px;
    margin-bottom: 10px;
    width: 100px;
    }
    button:hover {
    background-color:  #ff9201;

    }
    td button {
    background-color: #FF4F41;
    }
    td button:hover {
    background-color: red;
    }
    body {
    font-family: Arial', sans-serif;
    padding: 20px;
    color: #333;
    background: linear-gradient(135deg, #6464af, #8585c7);
    height: 100vh; /* Full screen height */
    margin: 0;
    }
    h1 {
    color: black;
    font-weight: bold;
    font-size: 40px;
    margin-left: 500px;
    margin-top: 20px
    }
    h2, {
    color: black;
    font-weight: bold;
    font-size: 35px;
    margin-left: 500px;
    margin-top: 20px
    }
    h3 {
    color: black;
    font-weight: bold;
    font-size: 35px;
    margin-left: 500px;
    margin-top: 20px
    
    }
    label {
    font-size: 20px;
    color: black;
    font-weight: bold;
    text-align: center;
    margin-top: 20px;
    }
    strong {
    font-weight: bold;
    margin-left: 5%;
    font-size: 30px;
    }
    p {
    font-size: 50px;
    font-weight: bold;
    margin-top: 15px;
    }
</style>
</head>
<body>
<h1 style="margin-left: 30px;">Product Sales</h1>

<script>
    function calculateChange() {
        let runningTotal = parseFloat(document.getElementById('running_total').value.replace(/,/g, ''));
        let receivedAmount = parseFloat(document.querySelector('input[name="received_amount"]').value);

        if (isNaN(receivedAmount) || receivedAmount < 0) {
        document.getElementById('change_display').innerHTML = 'Change: ₱ 0.00';
        return;
        }
        let change = receivedAmount - runningTotal;
        let formattedChange = change.toFixed(2);

        if (change < 0) {
        document.getElementById('change_display').innerHTML = 'Short: ₱ - ' + Math.abs(formattedChange);
        document.getElementById('change_display').style.color = 'red';
        } else {
        document.getElementById('change_display').innerHTML = 'Change: ₱ ' + formattedChange;
        document.getElementById('change_display').style.color = 'green';
        }
    }
</script>

<div class='container1'>   
    <!-- Search form -->
        <form method="POST" action="">
        <input type="text1" name="searchTerm" placeholder="Enter product name">
        <button type="submit1" name="search">Search</button>
        </form>
    
<!-- Display search results -->
<?php if (isset($result)): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['ProductID']; ?></td>
                <td><?= $row['Description']; ?></td>
                <td><?= number_format($row['Price'], 2); ?></td>
                <td><?= $row['Stock_Quantity']; ?></td>
            <td>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?= $row['ProductID']; ?>">
                        <input type="number" name="quantity" min="1" max="<?= $row['Stock_Quantity']; ?>" required>
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                    </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<div class="container2">
<!-- Display cart -->
<?php if (!empty($_SESSION['cart'])): ?>
    <h3> Cart</h3>

    <table border="1">
        <tr>
            <th>Description</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
            <th>Action</th>
        </tr>
        <?php foreach ($_SESSION['cart'] as $key => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['Description']); ?></td>
                <td><?= $item['Quantity']; ?></td>
                <td><?= number_format($item['Price'], 2); ?></td>
                <td><?= number_format($item['Total_Amt'], 2); ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="item_key" value="<?= $key; ?>">
                        <button type="submit" name="remove_item">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Total Amount:</strong>₱ <?= number_format(array_sum(array_column($_SESSION['cart'], 'Total_Amt')), 2); ?></p>    
    <!-- Hidden input to store the total amount -->
    <input type="hidden" id="running_total" value="<?= number_format(array_sum(array_column($_SESSION['cart'], 'Total_Amt')), 2); ?>">
<?php endif; ?>
</div>


<!-- Sale form -->
<div class='container3'>
<form method="POST" action="">
    <label for="sales_date">Sales Date:</label><br>
    <input type="date" name="sales_date" id="sales_date" value="<?php echo date('Y-m-d'); ?>" readonly>
    <br>

    <label for="receipt_no">Receipt Number:</label>
    <input type="text" name="receipt_no" value="<?php echo $nextReceiptNo; ?>" readonly>
    <br>

    <label for="customer_name">Customer Name</label>
    <input type="text" name="customer_name" id="customer_name" required>
    <br>

    <label for="customer_address">Customer Address</label>
    <input type="text" name="customer_address" id="customer_address" required>
    <br>

    <label for="received_amount">Received Amount:</label>
    <br>
    <input type="number" name="received_amount"  min="0" step="0.01" oninput="calculateChange()" required>
    <div id="change_display">Change: ₱ 0.00</div>
    <br>
    <br>
    <button type="submit" name="submit_sale" onclick="showModal()">Submit Sale</button>
    <br>
    <br>
</form>

<a href="<?php echo $dashboard_url;?>"><button>Back to Dashboard</button></a>

</div>

    <?php 
    // Check if the receipt session variable is set
    if (!isset($_SESSION['receipt'])) {
    echo "";
    exit();
    }
    $receipt = $_SESSION['receipt'];
    // Set the cashier's name based on session
    $receipt['cashier_name'] = $_SESSION['username']; // Use the logged-in username
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales</title>
    <style>
        /* Styles for the modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 80px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 20px;
        }
        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }
        .close {
            color: black;
            float: right;
            font-size: 50px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        // Function to open the receipt modal
        function openModal() {
            document.getElementById("receiptModal").style.display = "block";
        }

        // Function to close the receipt modal
        function closeModal() {
            document.getElementById("receiptModal").style.display = "none";
        }

        // Print receipt
        function printReceipt() {
            const printContents = document.getElementById("modal-receipt-content").innerHTML;
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            closeModal();
        }
    </script>
</head>
<body>
    <!-- Modal for receipt -->
    <div id="receiptModal" class="modal">
        <div class="modal-content" id="modal-receipt-content">
            <span class="close" onclick="closeModal()">&times;</span>


            <p  style="font-size: 30px; font-weight: bold; color: #333; text-align: center;">Boning Store</p>
            <h2 style="font-size: 30px; font-weight: bold; color: #333; text-align: center;">Receipt</h2>
            <p  style="font-size: 24px; font-weight: bold; color: #333; text-align: left;"><strong style="font-size: 24px;">Date:</strong> <?php echo $_SESSION['receipt']['date']; ?></p>
            <p  style="font-size: 24px; font-weight: bold; color: #333; text-align: left;"><strong style="font-size: 24px;">Receipt No:</strong> <?php echo $_SESSION['receipt']['receipt_no']; ?></p>
            <p  style="font-size: 24px; font-weight: bold; color: #333; text-align: left;"><strong style="font-size: 24px;">Customer:</strong> <?php echo $_SESSION['receipt']['customer_name']; ?></p>
            <p  style="font-size: 24px; font-weight: bold; color: #333; text-align: left;"><strong style="font-size: 24px;">Address:</strong> <?php echo $_SESSION['receipt']['customer_address']; ?></p>
           
            <table border="1" width="100%">
                <thead>
                    <tr>
                        <th style="background-color: white;">Description</th>
                        <th style="background-color: white;">Price</th>
                        <th style="background-color: white;">Quantity</th>
                        <th style="background-color: white;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['receipt']['items'] as $item): ?>
                        <tr>
                            <td style="font-size: 20px;"><?php echo htmlspecialchars($item['Description']); ?></td>
                            <td style="font-size: 20px;">₱ <?php echo number_format($item['Price'], 2); ?></td>
                            <td style="font-size: 20px;"><?php echo $item['Quantity']; ?></td>
                            <td style="font-size: 20px;">₱ <?php echo number_format($item['Total_Amt'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="font-size: 30px; font-weight: bold; color: #333; text-align: left;"><strong>Total Amount:</strong> ₱ <?php echo number_format($_SESSION['receipt']['total_amount'], 2); ?></p>
            <p style="font-size: 30px; font-weight: bold; color: #333; text-align: left;"><strong>Received Amount:</strong> ₱ <?php echo number_format($_SESSION['receipt']['received_amount'], 2); ?></p>
            <p style="font-size: 30px; font-weight: bold; color: #333; text-align: left;"><strong>Change:</strong> ₱ <?php echo $_SESSION['receipt']['change']; ?></p>
            
            <style>
                @media print {
                .print-button {
                display: none;
                }
            }
            </style>

            <button class="print-button" onclick="printReceipt()">Print Receipt</button>
        </div>
    </div>

    <!-- Trigger the modal with JavaScript when the form is submitted -->
    <script>
        <?php if (isset($_SESSION['success_message'])): ?>
            openModal();
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
