<?php
session_start();
require_once "database.php";

// Handle registration and checks registration form was submitted
if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $conn->real_escape_string($_POST['password']);
    $first = $conn->real_escape_string($_POST['firstname']);
    $last = $conn->real_escape_string($_POST['surname']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    
    // Check password length (minimum 6 characters)
    if (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif (strlen($mobile) != 10 || !is_numeric($mobile)) {
        $error = "Mobile number must be exactly 10 digits";
    } else {
        $check = $conn->query("SELECT * FROM Users WHERE Username='$user'");
        if ($check->num_rows == 0) { //checks if the username already exists by checking if there are any rows with that username
            $conn->query("INSERT INTO Users (Username, Password, FirstName, Surname, Mobile) 
                         VALUES ('$user', '$pass', '$first', '$last', '$mobile')");
            $_SESSION['username'] = $user; //logs the user automatically after they register
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username already exists";
        }
    }
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="register.php" class="logo">
                <span class="logo-icon">📚</span>
                <span class="logo-text">Library System</span>
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Join Our Library Community</h1>
            <p>Create an account to start borrowing books today</p>
        </div>
    </section>

    <div class="container auth-container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <div class="form-section">
            <h2>Create Your Library Account</h2>
            <form method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="firstname" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Surname *</label>
                        <input type="text" name="surname" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="text" name="mobile" class="form-control" maxlength="10" required>
                    </div>
                </div>
                <button type="submit" name="register" class="btn btn-primary">Create Account</button>
            </form>
            
            <div class="auth-switch"> <!-- This is a link to login page -->
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="container">
            <p>Library System &copy; 2025</p>
        </div>
    </footer>
</body>
</html>