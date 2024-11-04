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
       /* Additional Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
    padding: 20px;
    color: #333;
}

h2 {
    text-align: center;
    color: #2c3e50;
}

/* Centering the form and table */
form {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

input[type="text"], input[type="number"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 200px;
}

button {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2980b9;
}

/* Styling the table */
table {
    margin: 0 auto; /* Center the table */
    width: 80%; /* Control the width of the table */
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #3498db;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

a {
    color: #e74c3c;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    padding-top: 50px;
}

.modal-content {
    background-color: white;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
    position: relative;
}

.close {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #e74c3c;
}

/* Button for Back to Dashboard */
a button {
    background-color: #1abc9c;
    color: white;
}

a button:hover {
    background-color: #16a085;
}

/* Responsive Design */
@media screen and (max-width: 600px) {
    table {
        width: 100%;
    }
    
    form {
        flex-direction: column;
        width: 100%;
    }
    
    input, button {
        width: 100%;
    }
    
    .modal-content {
        width: 90%;
    }
}

    </style>
</head>
<body>

<h2>Inventory Management</h2>

<!-- Back Button -->
<a href="<?php echo $dashboard_url; ?>"><button>Back to Dashboard</button></a>

<!-- Add Product Form -->
<form method="post">
    <input type="text" name="description" placeholder="Product Description" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="number" name="stock_quantity" placeholder="Stock Quantity" required>
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
