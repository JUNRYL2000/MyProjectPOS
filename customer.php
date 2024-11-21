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
    color: #6464AF;
    text-align: center;
    margin-bottom: 20px;
}

/* Button Styling */
button {
    background-color: #6464af;
    color: white;
    border: none;
    padding: 8px 12px;
    margin-right: 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    margin-left: 6%;
}

button:hover {
    background-color: #6464af;
}

/* Success and Error Messages */
p {
    text-align: center;
    font-weight: bold;
}

p[style*="color:green"] {
    color: green;
}

p[style*="color:red"] {
    color: red;
}

/* Table Styling */
table {
    width: 90%;
    border-collapse: collapse;
    margin-top: 20px auto;
    margin-left: 6%;

}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #6464AF;
    color: white;
}

td {
    background-color: #fff;
}

/* Action Buttons */
button {
    background-color: #6464af;
    color: white;
    border: none;
    padding: 8px 12px;
    margin-right: 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    margin-left: 6%;
}

button:hover {
    background-color: #5959cd;
}

/* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* Black with opacity */
    padding-top: 60px;
    box-sizing: border-box;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 5px;
    width: 50%;
    box-sizing: border-box;
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
    text-decoration: none;
}

/* Modal Header */
h3 {
    color: #6464AF;
    margin-left: 6%;
    font-size: 35px;
}

/* Form Inputs */
input[type="text"] {
    width: 100%;
    padding: 8px;
    margin: 8px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
    table {
        font-size: 14px;
    }

    th, td {
        padding: 8px;
    }

    .modal-content {
        width: 90%;
    }

    button {
        font-size: 12px;
        padding: 6px 12px;
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
