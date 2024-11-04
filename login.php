<?php
include 'db.php'; // Include your database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$inactive_message = ""; // Variable to hold the inactive message

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $login_username = $_POST['username'];
    $login_password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$login_username'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the user is active
        if ($user['status'] == 0) {
            $inactive_message = "Your account is inactive. Please contact the administrator.";
        } else if (password_verify($login_password, $user['password'])) {
            // Login successful
            session_start();
            $_SESSION['username'] = $login_username;
            $_SESSION['user_type'] = $user['user_type']; // Store user type in session

            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                header("Location: admin_dashboard.php"); // Redirect to admin dashboard
            } else {
                header("Location: cashier_dashboard.php"); // Redirect to cashier dashboard
            }
            exit();
        } else {
            $inactive_message = "Invalid password.";
        }
    } else {
        $inactive_message = "Invalid username.";
    } 
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $register_username = $_POST['reg_username'];
    $register_password = $_POST['reg_password'];
    $user_type = $_POST['user_type'];

    // Check if the username already exists
    $check_sql = "SELECT * FROM users WHERE username='$register_username'";
    $check_result = $conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        $inactive_message = "Username already exists. Please choose a different username.";
    } else {
        // Hash password for security
        $hashed_password = password_hash($register_password, PASSWORD_DEFAULT);
        $status = 1; // Set default status to active

        $sql = "INSERT INTO users (username, password, user_type, status) VALUES ('$register_username', '$hashed_password', '$user_type', '$status')";
        
        if ($conn->query($sql) === TRUE) {
            $inactive_message = "Registration successful!";
        } else {
            $inactive_message = "Error: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
body {
    font-family: 'Verdana', sans-serif;
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    margin-top: -30px;
}
.login-container {
    background-color: #fff;
    color: #333;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
    text-align: center;
    width: 350px; /* Set width to match modal */
    transform: translateZ(0);
    transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    margin-top:
}
.login-container:hover {
    transform: translateY(-10px);
}
h2 {
    font-size: 36px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
    margin-bottom: 20px;
    margin-top: -10px;
}
input[type="text"], input[type="password"], select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: none;
    border-radius: 5px;
    box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.3);
    font-size: 16px;
    color: #333;
}
button {
    background-color: #1abc9c;
    color: white;
    border: none;
    padding: 12px 20px;
    margin-top: 15px;
    font-size: 18px;
    border-radius: 5px;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
    width: 100%;
}
button:hover {
    transform: scale(1.05);
    box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.4);
}
button:active {
    transform: scale(0.98);
}
.modal {
    display: none;
    position: fixed;
    z-index: 2;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    perspective: 1000px;
}
.modal-content {
    background-color: #fff;
    color: #333;
    border-radius: 15px;
    padding: 40px; /* Match padding with login-container */
    position: relative;
    width: 350px; /* Set width to match login-container */
    height: 400px;
    margin: 15% auto; /* Center the modal */
    transform: translateZ(0);
    transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
}
.close {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}
.close:hover {
    color: #e74c3c;
}
    @media (max-width: 768px) {
.login-container, .modal-content {
width: 90%; /* Adjust width for smaller screens */
}
}
    </style>
<body>
<h1>Boning Store</h1>
<div class="login-container">
<h2>Login</h2>
<form method="post">
    
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required id="myInput">
    <input type="checkbox" onclick="myFunction()">Show Password
    <button type="submit" name="login">Login</button>
</form>
</div>

<script>
    function myFunction() {
  var x = document.getElementById("myInput");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
</script>

<!-- Button to open the modal -->
<button id="openModal">Register</button>

<!-- The Modal -->
<div id="registrationModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Register</h2>
        <form method="post">
            <input type="text" name="reg_username" placeholder="Username" required>
            <input type="password" name="reg_password" placeholder="Password" required>
            <select name="user_type" required>
                <option value="admin">Admin</option>
                <option value="cashier">Cashier</option>
            </select>
            <button type="submit reg" name="register">Register</button>
        </form>
    </div>
</div>

<!-- Inactive User Message Modal -->
<div id="messageModal" class="modal" <?php echo ($inactive_message ? 'style="display:block;"' : ''); ?>>
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('messageModal').style.display='none';">&times;</span>
        <p><?php echo $inactive_message; ?></p>
    </div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById("registrationModal");
    var messageModal = document.getElementById("messageModal");
    var btn = document.getElementById("openModal");
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the registration modal
    btn.onclick = function() {
        modal.style.display = "block";
    }
    
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        if (event.target == messageModal) {
            messageModal.style.display = "none";
        }
    }
</script>

</body>
</html>

<?php
$conn->close();
?>
