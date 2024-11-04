<?php 
include 'db.php'; 
session_start(); // Ensure session is started to handle cart data

    // Redirect to dashboard if not logged in
    if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

    // Initialize cart if not already set
    if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

    // Add to Cart Logic
    if (isset($_POST['addToCart'])) {
    $productID = $_POST['productID'];
    $quantity = $_POST['quantity'];
    $newPrice = $_POST['price'];

    // Fetch product details
    $productResult = $conn->query("SELECT * FROM Product WHERE ProductID = $productID");
    $product = $productResult->fetch_assoc();

    if ($product) {
        if (isset($_SESSION['cart'][$productID])) {
            $_SESSION['cart'][$productID]['quantity'] += $quantity;
            $_SESSION['cart'][$productID]['price'] = $newPrice;
        } else {
            $_SESSION['cart'][$productID] = [
                'description' => $product['Description'],
                'price' => $newPrice,
                'quantity' => $quantity
            ];
        }
    }
    header("Location: delivery.php");
    exit();
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

    // Remove from Cart Logic
    if (isset($_POST['removeFromCart'])) {
    $productID = $_POST['productID'];
    unset($_SESSION['cart'][$productID]);
    header("Location: delivery.php");
    exit();
}

    // Initialize OR Number
    $orNumber = uniqid("OR"); // Generate a unique OR number

    // Submit Delivery Logic
    if (isset($_POST['submitDelivery'])) {
    $deliveryDate = $_POST['deliveryDate'];
    $storeID = $_POST['storeID'];
    $totalAmount = $_POST['totalAmount'];
    if (isset($_SESSION['id'])) {
        $userId = $_SESSION['id'];} // Ensure user ID is stored in the session

    $dateFormat = 'Y-m-d';
    $d = DateTime::createFromFormat($dateFormat, $deliveryDate);
    if ($d && $d->format($dateFormat) === $deliveryDate) {
        if (!empty($_SESSION['cart'])) {
            $runningTotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $runningTotal += $item['price'] * $item['quantity'];
            }

            $difference = $totalAmount - $runningTotal;
            if (abs($difference) > 0.01) {
                echo "<div>Warning: The running total does not match the total amount entered. Delivery not saved.</div>";
            } else {
                $conn->autocommit(FALSE);

                try {
                    // Insert into DeliveryTransaction table
                    $stmt = $conn->prepare("INSERT INTO DeliveryTransaction (OR_No, Total_Amount, Delivery_Date, StoreID) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('sdsi', $orNumber, $totalAmount, $deliveryDate, $storeID);
                    $stmt->execute();
                    $transactionID = $stmt->insert_id;
                    $stmt->close();

                  // Insert into DeliveryTransactionDetails table
                    $stmt = $conn->prepare("INSERT INTO DeliveryTransactionDetails (TransactionID, ProductID, Quantity, Cost, Total_Cost) VALUES (?, ?, ?, ?, ?)");
                    foreach ($_SESSION['cart'] as $productID => $item) {
                    $cost = $item['price'];
                    $totalCost = $cost * $item['quantity'];
                    $stmt->bind_param('iiidd', $transactionID, $productID, $item['quantity'], $cost, $totalCost);
                    $stmt->execute();

                // Update stock in Product table
                    $stmtUpdate = $conn->prepare("UPDATE Product SET Stock_Quantity = Stock_Quantity + ?, Price = ? WHERE ProductID = ?");
                    $stmtUpdate->bind_param('idi', $item['quantity'], $item['price'], $productID);
                    $stmtUpdate->execute();
                    $stmtUpdate->close();
                    }
                    $stmt->close();


                    // Insert into delivery_OR table
                    $stmt = $conn->prepare("INSERT INTO delivery_OR (OR_No, DateTime, id) VALUES (?, NOW(), ?)");
                    $stmt->bind_param('si', $orNumber, $userId);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['cart'] = [];
                    $conn->commit();
                    echo "<div>Delivery successfully saved.</div>";
                } catch (Exception $e) {
                    $conn->rollback();
                    echo "<div>Error: " . $e->getMessage() . "</div>";
                }
            }
        } else {
            echo "<div>Cart is empty. Cannot submit delivery.</div>";
        }
    } else {
        echo "<div>Invalid date format. Please enter a valid date.</div>";
    }
}
    // Determine the dashboard redirect URL
    $dashboard_url = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'cashier_dashboard.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Delivery</title>
</head>
<body>
    <style>
/* General Styling */
body {
    font-family: 'Verdana', sans-serif;
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: #333;
    margin: 0;
    padding: 20px;
    height: 100vh; /* Full screen height */   
}
h1, h2, h3 {
    color: black;
    font-weight: bold;
    font-size: 35px;
    margin-left: 500px;
    margin-top: 20px
}
.container1{
    margin-left: 10px;
    padding: 10px;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    width: 75%;
}
.container2{
    width: 66.2%;
    background: ;
    margin-left: 10px;
}
.container3{
    right: 0;
    top: 0;
    width: 50%;
    position: absolute;
    margin-left: 100%;
    padding: 10px;
    background: lightgray;
    width: 20%;
    margin-right: 15px;
    margin-top: 15px;
}
input[type="text"], input[type="number"], input[type="date"], select {
    width: calc(80% - 5px);
    padding: 10px;
    margin: 6px 0;
    border: 2px solid #ccc;
    border-radius: 4px;
    weight: bold;
    margin-left: 20px;
    font-size: 18px;
    color: solid black;
}
select {
    width:  ;
    padding: 10px;
    margin: 6px 0;
    border: 2px solid #ccc;
    border-radius: 4px;
    weight: bold;
    margin-left: 20px;
}
button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 12px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 18px;
    width: 120px;
    margin-left: 20px;
}
button:hover {
    background-color: #45a049;
}
table {
    width: 100%;
    margin-top: 2px;
    padding: 20px;
    background-color: #fff;
}
.table1 {
    width: 100%;
    margin-top: 2px;
    padding: 20px;
    background-color: #fff;
}
table th, table td {
    padding: 12px;
    text-align: ;
    border-bottom: 1px solid #ddd;
}
th, td {
    padding: 12px;
    text-align: left;
    background-color: gray;
}
th {
    background-color: #4CAF50;
    color: white;
    font-size: 25px;
    font-weight: bold;
}
tbody tr:nth-child(even) {
    background-color: #f2f2f2;
}
tbody tr:hover {
    background-color: #ddd;
}
/* Running Total */
div {
    font-weight: bold;
    font-size: 30px;
    color: #333;
    text-align: left;
    width: 50%; /* Adjusted width for input field */
    border: none;
    margin-left: 15px; /* Added space between ₱ and total */
    padding: 10px;
    margin-top: 5px;
    color: white;
}
/* Short/Over Message Styling */
span {
    display: block;
    margin-top: 5px;
    font-size: 30px;
    background-color: white;
    width: 30%;
    margin-left: 20px;
    text-align: center;
}
/* Submit Form Section */
.container3 form {
    margin-top: 20px;
}
.container3 form input[type="text"] {
    background-color: #e9ecef;
    cursor: not-allowed;
}
/* Back to Dashboard Button */
.container3 a button {
    margin-top: 10px;
    background-color: #2196F3;
}
.container3 a button:hover {
    background-color: #1976D2;
}
</style>


    <h1>Product Delivery</h1>
    <div class='container1'>   
        <form method="POST" action="delivery.php">
            <input type="text" name="search" placeholder="Search for Product" required>
            <button type="submit" name="searchProduct">Search</button>
        </form>
    <?php if (isset($_POST['searchProduct'])): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Description</th>
                    <th>Current Price</th>               
                    <th>Stock Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
        <tbody>
    </div>

<div class='container2'>
    <div class='table1'>
        <?php
            $search = $conn->real_escape_string($_POST['search']);
            $result = $conn->query("SELECT * FROM Product WHERE Description LIKE '%$search%'");
            while ($row = $result->fetch_assoc()) {
            echo "<tr>
                        <td>{$row['ProductID']}</td>
                        <td>{$row['Description']}</td>
                        <td>{$row['Price']}</td>
                        <td>{$row['Stock_Quantity']}</td>
                    <td>
                    <form method='POST' action='delivery.php'>
                        <input type='hidden' name='productID' value='{$row['ProductID']}' />
                        <input type='number' name='quantity' placeholder='Quantity' required min='1' />
                        <input type='number' name='price' placeholder='New Price' required min='1' />
                        <button type='submit' name='addToCart'>Dispatch</button>
                    </form>
                    </td>
                </tr>";
            }
            ?>
         </tbody>
        <?php endif; ?>
     </table>
   </div>
</div>
<?php if (!empty($_SESSION['cart'])): ?>

<div class='container2'>
    <h3>Delivery List</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Cost</th>
                    <th>Action</th>
                </tr>
            </thead>
        <tbody>
        <?php
            $runningTotal = 0; // Initialize running total
            foreach ($_SESSION['cart'] as $productID => $item) {
                $totalCost = $item['price'] * $item['quantity']; // Calculate total cost for each item
                $runningTotal += $totalCost; // Add to running total
                echo "<tr>
                    <td>{$item['description']}</td>
                    <td>{$item['price']}</td>
                    <td>{$item['quantity']}</td>
                    <td>" . number_format($totalCost, 2) . "</td>
                    <td>
                    <form method='POST' action='delivery.php'>
                    <input type='hidden' name='productID' value='$productID' />
                    <button type='submit' name='removeFromCart'>Remove</button>
                </form>
                </td>
                </tr>";
            }
        ?>
          </table>
         <div>Running Total: ₱ <?php echo number_format($runningTotal, 2); ?></div>
        <!-- Display Short/Over message below the Running Total -->
        <?php if (isset($difference) && abs($difference) > 0.01): ?>
           
                <?php if ($difference < 0): ?>
                    <span style="color: red;">Short: ₱ - <?php echo number_format(abs($difference), 2); ?></span>
                <?php elseif ($difference > 0): ?>
                    <span style="color: green;">Over: <?php echo number_format($difference, 2); ?></span>
                <?php endif; ?>
        <?php endif; ?>

    <?php endif; ?>
    
</div>
    </tbody>

    

<div class='container3'>
<!-- Submit Delivery Form -->
<form method="POST" action="delivery.php">
    <input type="text" name="orNumber" value="<?php echo $orNumber; ?>" readonly><br>
    
    <!-- Change the input type to make the date editable -->
    <input type="date" name="deliveryDate" value="<?php echo date('Y-m-d'); ?>" required><br>
    
    <select name="storeID" required>
        <option value="">Select Store</option>
        <?php
        $storeResult = $conn->query("SELECT * FROM Store");
        while ($store = $storeResult->fetch_assoc()) {
            echo "<option value='{$store['StoreID']}'>{$store['StoreName']}</option>";
        }
        ?>
    </select><br>
    
    <input type="number" name="totalAmount" placeholder="Total Amount" step="0.01" required><br>
    <button type="submit" name="submitDelivery">Submit Delivery</button>
</form>
<a href="<?php echo $dashboard_url; ?>"><button>Back to Dashboard</button></a>
