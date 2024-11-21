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

// Handle adding a store
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_store'])) {
    $store_name = $_POST['store_name'];
    $location = $_POST['location'];

    $sql = "INSERT INTO store (StoreName, Location) VALUES ('$store_name', '$location')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Store added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle editing a store
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_store'])) {
    $store_id = $_POST['store_id'];
    $store_name = $_POST['edit_store_name'];
    $location = $_POST['edit_location'];

    $sql = "UPDATE store SET StoreName='$store_name', Location='$location' WHERE StoreID='$store_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Store updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle deleting a store
if (isset($_GET['delete'])) {
    $store_id = $_GET['delete'];

    $sql = "DELETE FROM store WHERE StoreID='$store_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Store deleted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch stores from the database
$sql = "SELECT * FROM store";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Management</title>
    <style>
      /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f5f6fa;
    color: #333;
    font-size: 16px;
    line-height: 1.6;
    padding: 20px;
}

h2 {
    font-size: 45px;
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
    margin-right: 10px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 15px;
    margin-left: 5%;
}

button:hover {
    background-color: #3b4778;
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
    width: 85%;
    border-collapse: collapse;
    margin-top: 20px;
    margin-left: 5%;
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
    background-color: #fff;
}

/* Action Buttons */
 /* Button Styling - General */
button {
    background-color: #4e5d94;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 15px;
    margin-right: 10px;
}

/* Button Hover Styling */
button:hover {
    background-color: #3b4778;
}

/* Form Button - Add Store */
form button[type="submit"] {
    background-color: #4e5d94;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 15px;
    margin-left: 15px;
    margin-top: 10px;
}

/* Edit Button (Inside Table) */
table button {
    background-color: #4e5d94;
    color: white;
    border: none;
    padding: 8px 12px;
    margin-right: 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

table button:hover {
    background-color: #3b4778;
}

/* Delete Link Button (Inside Table) */
table a {
    color: white;
    text-decoration: none;
    background-color: #e74c3c;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    display: inline-block;
}

table a:hover {
    background-color: #c0392b;
}

/* Modal Button Styling */
.modal button[type="submit"] {
    background-color: #4e5d94;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 15px;
    margin-top: 20px;
}

.modal button[type="submit"]:hover {
    background-color: #3b4778;
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
    background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
    padding-top: 60px;
    box-sizing: border-box;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 25px;
    border-radius: 5px;
    width: 30%;
    box-sizing: border-box;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    right: 20px;
    top: 20px;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

/* Modal Header */
h3 {
    color: #4e5d94;
    margin-left: 6%;
    font-size: 30px;
}

/* Form Inputs */
input[type="text"] {
    width: 20%;
    padding: 15px;
    margin: 8px 0;
    border: 3px solid #797575;
    border-radius: 5px;
    font-size: 20px;
    font-weight: bold;
    margin-left: 5%;
    margin-top: 10px;
}
input[type="text1"] {
    width: 20%;
    padding: 15px;
    margin: 8px 0;
    border: 3px solid #797575;
    border-radius: 5px;
    font-size: 20px;
    font-weight: bold;
    margin-left: 2%;
    margin-top: 20px;
}
input[type="text2"] {
    width: 100%;
    padding: 15px;
    margin: 8px 0;
    border: 3px solid #797575;
    border-radius: 5px;
    font-size: 20px;
    font-weight: bold;
    
}

/* Responsive Design */
@media (max-width: 768px) {
    table {
        font-size: 14px;
    }

    th, td {
        padding: 10px;
    }

    .modal-content {
        width: 80%;
    }

    button {
        font-size: 12px;
        padding: 8px 12px;
    }
}

    </style>
</head>
<body>

<h2>Store Management</h2>

<!-- Back Button -->
<a href="<?php echo $dashboard_url; ?>"><button>Back to Dashboard</button></a>

<!-- Add Store Form -->
<form method="post">
    <input type="text" name="store_name" placeholder="Store Name" required>
    <input type="text1" name="location" placeholder="Location" required>
    <button type="submit" name="add_store">Add Store</button>
</form>

<!-- Store Table -->
<table>
    <thead>
        <tr>
            <th>Store ID</th>
            <th>Store Name</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['StoreID']; ?></td>
                    <td><?php echo $row['StoreName']; ?></td>
                    <td><?php echo $row['Location']; ?></td>
                    <td>
                        <button onclick="openEditModal(<?php echo $row['StoreID']; ?>, '<?php echo $row['StoreName']; ?>', '<?php echo $row['Location']; ?>')">Edit</button>
<style>
a.delete-link {
    display: inline-block;
    padding: 10px 20px;
    background-color: #ff4d4d; /* Red background for the delete link */
    color: white;
    font-size: 1.2em;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
    transition: background-color 0.3s, transform 0.2s ease-in-out;
}
a.delete-link:hover {
    background-color: #e60000; /* Darker red on hover */
    transform: scale(1.05); /* Slightly grow on hover */
}
a.delete-link:active {
    transform: scale(0.98); /* Slightly shrink when clicked */
}
a.delete-link + a.delete-link {
    margin-left: 10px;
}
</style>
                        <a href="?delete=<?php echo $row['StoreID']; ?>" 
                        class="delete-link" onclick="return confirm('Are you sure you want to delete this store?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No stores found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Edit Store Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Store</h2>
        <form id="editForm" method="post">
            <input type="hidden" name="store_id" id="edit_store_id">
            <input type="text2" name="edit_store_name" id="edit_store_name" required><br>
            <input type="text2" name="edit_location" id="edit_location" required><br>
            <button type="submit" name="edit_store">Update Store</button>
        </form>
    </div>
</div>

<script>
// Function to open the edit modal
function openEditModal(id, name, location) {
    document.getElementById('edit_store_id').value = id;
    document.getElementById('edit_store_name').value = name;
    document.getElementById('edit_location').value = location;
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
