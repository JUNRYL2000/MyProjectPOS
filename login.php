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
            font-family: 'Times New Roman', Times, serif;
            background-color: #a9a9e1; 
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 50px;
            color:#4c4c9f;
            font-size: 50px;
            font-weight: bold;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 7px 15px rgba(0, 0, 0, 0.3);
            padding: 40px 30px;
            margin-top: 60px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 40px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%; 
            padding: 10px;
            margin: 10px 0;
            border: 3px solid #ccc;
            border-radius: 4px;
            font-size: 20px;
            box-sizing: border-box; 
        }
        button {
            width: 80%;
            padding: 12px;
            background-color:#474785;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 20px;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            cursor: pointer;
            margin-top: 30px;
            margin-left: 47px;
        }

        button:hover {
            background-color:#8484cd;
            color: black;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .show-password {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .show-password input[type="checkbox"] {
            margin-right: 10px;
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
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 0;
            right: 15px;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        /* Error Message Styles */
        .modal p {
            text-align: center;
            color: #e74c3c;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
<body>
<h1><div class="text">
       <span class="typing"></span>
    </div></h1>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.12/typed.min.js"></script>
<script>
    var typing = new Typed(".typing", {
        strings: ["Bossing Store"],
        typeSpeed: 150,
        backSpeed: 60,
        loop: true
    });
</script>

<div class="login-container">
<h2>Login</h2>
<form method="post">
    
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required id="myInput">
    <input type="checkbox" onclick="myFunction()">Show Password
    <button type="submit" name="login">Login</button>
</form>

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
</div>
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
