<?php
session_start();
include 'db.php'; // Include database connection

// Redirect to dashboard if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Determine the dashboard redirect URL
$dashboard_url = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'cashier_dashboard.php';

// Handle adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    $sql = "INSERT INTO product (Description, Price, Stock_Quantity) VALUES ('$description', '$price', '$stock_quantity')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Product added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle editing a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $description = $_POST['edit_description'];
    $price = $_POST['edit_price'];
    $stock_quantity = $_POST['edit_stock_quantity'];

    $sql = "UPDATE product SET Description='$description', Price='$price', Stock_Quantity='$stock_quantity' WHERE ProductID='$product_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Product updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle deleting a product
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    $sql = "DELETE FROM product WHERE ProductID='$product_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Product deleted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch products from the database
$sql = "SELECT * FROM product";
$result = $conn->query($sql);

// Determine the dashboard redirect URL
$dashboard_url = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'cashier_dashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
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
    color: #4e5d94;
    text-align: center;
    margin-bottom: 20px;
}

/* Button Styling */
button {
    background-color: #4e5d94;
    color: white;
    border: none;
    padding: 10px 15px;
    margin-right: 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-align: center;
}

button:hover {
    background-color: #3b4778;
}

/* Back Button */
a button {
    background-color: #6464A;
    padding: 8px 16px;
    margin-right: 0;
    margin-left: 5%;
    height: 42px;
    margin-bottom: 20px;
}

a button:hover {
    background-color: #3b4778;
}

/* Form Styling */
form {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
}

form input[type="text"],
form input[type="number"] {
    width: 25%;
    height: 45px;
    padding: 10px;
    margin: 8px 0;
    border-radius: 5px;
    border: 3px solid #ddd;
    font-size: 14px;
    margin-left: 5%;
}

form button {
    width: 10%;
    padding: 10px;
    margin-top: 2px;
    border-radius: 5px;
    font-size: 14px;
    margin-left: 5%;
}

form input[type="text"]:focus,
form input[type="number"]:focus {
    border-color: #4e5d94;
    outline: none;
}

/* Table Styling */
table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #4e5d94;
    color: white;
}

td {
    background-color: white;
}

table button {
    background-color: #4e5d94;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

table button:hover {
    background-color: #3b4778;
}

/* Delete Link Styling */
table a {
    color: white;
    background-color: #e74c3c;
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
}

table a:hover {
    background-color: #c0392b;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 5px;
    width: 30%;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
}

.modal h2 {
    color: #4e5d94;
    text-align: center;
}

.modal input[type="text"],
.modal input[type="number"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 5px;
    border: 3px solid #ddd;
    font-size: 14px;
}

.modal button {
    padding: 10px 15px;
    background-color: #4e5d94;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    width: 40%;
    margin-left: 8px;
}

.modal button:hover {
    background-color: #3b4778;
}

/* Responsive Design */
@media (max-width: 768px) {
    form {
        width: 100%;
    }

    table {
        width: 100%;
    }

    .modal-content {
        width: 80%;
    }

    button {
        width: 100%;
    }

    table th, table td {
        font-size: 14px;
        padding: 8px;
    }
}

    </style>
</head>
<body>

<h2>Inventory Management</h2>

<a href="<?php echo $dashboard_url; ?>"><button>Back to Dashboard</button></a>

<!-- Add Product Form -->
<form method="post">
    <input type="text" name="description" placeholder="Product Description" required>
    <input type="hidden" step="0.01" name="price" placeholder="Price" required>
    <input type="hidden" name="stock_quantity" placeholder="Stock Quantity" required>
    <button type="submit" name="add_product">Add Product</button>
</form>

<!-- Product Table -->
<table>
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock Quantity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['ProductID']; ?></td>
                    <td><?php echo $row['Description']; ?></td>
                    <td><?php echo $row['Price']; ?></td>
                    <td><?php echo $row['Stock_Quantity']; ?></td>
                    <td>
                        <button onclick="openEditModal(<?php echo $row['ProductID']; ?>, '<?php echo $row['Description']; ?>', <?php echo $row['Price']; ?>, <?php echo $row['Stock_Quantity']; ?>)">Edit</button>
                        <a href="?delete=<?php echo $row['ProductID']; ?>" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No products found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Edit Product Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Product</h2>
        <form id="editForm" method="post">
            <input type="hidden" name="product_id" id="edit_product_id">
            <input type="text" name="edit_description" id="edit_description" required>
            <input type="number" step="0.01" name="edit_price" id="edit_price" required>
            <input type="number" name="edit_stock_quantity" id="edit_stock_quantity" required>
            <button type="submit" name="edit_product">Update Product</button> 
        </form>
    </div>
</div>

<script>
// Function to open the edit modal
function openEditModal(id, description, price, stock) {
    document.getElementById('edit_product_id').value = id;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_stock_quantity').value = stock;
    document.getElementById('editModal').style.display = 'block';
}

// Function to close the edit modal
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeEditModal();
    }
}
</script>

</body>
</html>

<?php
$conn->close();
?>
