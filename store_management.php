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
        /* Basic styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
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

<h2>Store Management</h2>

<!-- Back Button -->
<a href="<?php echo $dashboard_url; ?>"><button>Back to Dashboard</button></a>

<!-- Add Store Form -->
<form method="post">
    <input type="text" name="store_name" placeholder="Store Name" required>
    <input type="text" name="location" placeholder="Location" required>
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
                        <a href="?delete=<?php echo $row['StoreID']; ?>" onclick="return confirm('Are you sure you want to delete this store?')">Delete</a>
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
            <input type="text" name="edit_store_name" id="edit_store_name" required>
            <input type="text" name="edit_location" id="edit_location" required>
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
