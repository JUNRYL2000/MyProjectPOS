<?php
// Start session
session_start();
include 'db.php';

// Redirect to dashboard if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Determine the dashboard redirect URL
$dashboard_url = $_SESSION['user_type'] === 'admin' ? 'admin_dashboard.php' : 'cashier_dashboard.php';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add customer
if (isset($_POST['add_customer'])) {
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address']; // Address field

    $stmt = $conn->prepare("INSERT INTO customer (CustomerName, Address) VALUES (?, ?)");
    $stmt->bind_param("ss", $customer_name, $customer_address);

    if ($stmt->execute()) {
        $success_message = "Customer added successfully!";
    } else {
        $error_message = "Error adding customer: " . $stmt->error;
    }
}

// Edit customer
if (isset($_POST['edit_customer'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address']; // Address field

    $stmt = $conn->prepare("UPDATE customer SET CustomerName = ?, Address = ? WHERE CustomerID = ?");
    $stmt->bind_param("ssi", $customer_name, $customer_address, $customer_id);

    if ($stmt->execute()) {
        $success_message = "Customer updated successfully!";
    } else {
        $error_message = "Error updating customer: " . $stmt->error;
    }
}

// Delete customer
if (isset($_GET['delete_customer'])) {
    $customer_id = $_GET['delete_customer'];

    $stmt = $conn->prepare("DELETE FROM customer WHERE CustomerID = ?");
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        $success_message = "Customer deleted successfully!";
    } else {
        $error_message = "Error deleting customer: " . $stmt->error;
    }
}

// Fetch all customers
$customers_result = $conn->query("SELECT * FROM customer");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <style>
        /* General Body Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f0f4f7;
    margin: 0;
    padding: 20px;
    color: #333;
    text-align: center; /* Center the content inside the body */
}

/* Header Styles */
h2, h3 {
    color: #2c3e50;
    text-align: center;
}

p {
    text-align: center;
    font-size: 1.1em;
    margin: 10px 0;
}

/* Center the Table */
table {
    margin: 20px auto; /* This will center the table horizontally */
    width: 80%; /* Set a specific width for the table */
    border-collapse: collapse;
    background-color: #fff;
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

/* Form Styles */
form {
    width: 100%;
    max-width: 400px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 16px;
    color: #2c3e50;
}

input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

input[type="submit"] {
    background-color: #1abc9c;
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
    width: 100%;
}

input[type="submit"]:hover {
    background-color: #16a085;
}

/* Button Styles */
button {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2980b9;
}

/* Modal Styles */
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

/* Close Button */
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

/* Success and Error Message Styles */
p[style='color:green;'] {
    background-color: #dff0d8;
    padding: 10px;
    border-radius: 4px;
    max-width: 500px;
    margin: 20px auto;
    text-align: center;
}

p[style='color:red;'] {
    background-color: #f2dede;
    padding: 10px;
    border-radius: 4px;
    max-width: 500px;
    margin: 20px auto;
    text-align: center;
}

/* Responsive Design */
@media screen and (max-width: 600px) {
    .modal-content, form, table {
        width: 90%;
    }
}

    </style>

</head>

<body>

    <h2>Customer Management</h2>


    <a href="<?php echo $dashboard_url; ?>"><button>Back to Dashboard</button></a>
    <br>
    <?php
    if (isset($success_message)) {
        echo "<p style='color:green;'>{$success_message}</p>";
    }
    if (isset($error_message)) {
        echo "<p style='color:red;'>{$error_message}</p>";
    }
    ?>

    <!-- Add New Customer -->
  
    <form action="customer.php" method="POST">
    <h3>Add Customer</h3>
        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" required>
        <br>
        <label for="customer_address">Customer Address:</label>
        <input type="text" id="customer_address" name="customer_address" required>
        <br>
        <input type="submit" name="add_customer" value="Add Customer">
    </form>

    <!-- List of Customers -->
    <h3>Customer List</h3>
    <table border="1">
        <tr>
            <th>Customer ID</th>
            <th>Customer Name</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $customers_result->fetch_assoc()) : ?>
            <tr>
                <td><?= $row['CustomerID'] ?></td>
                <td><?= $row['CustomerName'] ?></td>
                <td><?= $row['Address'] ?></td>
                <td>
                    <!-- Trigger the Edit Modal -->
                    <button onclick="openEditModal(<?= $row['CustomerID'] ?>, '<?= $row['CustomerName'] ?>', '<?= $row['Address'] ?>')">Edit</button>
                    
                    <!-- Trigger the Delete Modal -->
                    <button onclick="openDeleteModal(<?= $row['CustomerID'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Edit Customer Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>Edit Customer</h3>
            <form action="customer.php" method="POST">
                <input type="hidden" id="edit_customer_id" name="customer_id">
                <label for="edit_customer_name">Customer Name:</label>
                <input type="text" id="edit_customer_name" name="customer_name" required>
                <br>
                <label for="edit_customer_address">Customer Address:</label>
                <input type="text" id="edit_customer_address" name="customer_address" required>
                <br>
                <input type="submit" name="edit_customer" value="Edit Customer">
            </form>
        </div>
    </div>

    <!-- Delete Customer Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            <h3>Delete Customer</h3>
            <p>Are you sure you want to delete this customer?</p>
            <a href="#" id="delete_customer_link">Delete</a>
        </div>
    </div>

    <script>
        // Open Edit Modal and populate fields
        function openEditModal(customerID, customerName, customerAddress) {
            document.getElementById('edit_customer_id').value = customerID;
            document.getElementById('edit_customer_name').value = customerName;
            document.getElementById('edit_customer_address').value = customerAddress;
            document.getElementById('editModal').style.display = 'block';
        }

        // Open Delete Modal and set delete link
        function openDeleteModal(customerID) {
            document.getElementById('delete_customer_link').href = "customer.php?delete_customer=" + customerID;
            document.getElementById('deleteModal').style.display = 'block';
        }

        // Close Modal
        function closeModal(modalID) {
            document.getElementById(modalID).style.display = 'none';
        }

        // Close modal if user clicks outside of the modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
