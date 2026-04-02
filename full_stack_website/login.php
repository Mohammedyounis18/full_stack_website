<?php 
session_start();
require_once "database.php";

// Handle login
if (isset($_POST['login'])) {
    unset($_SESSION['username']);
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $conn->real_escape_string($_POST['password']);
    
    $result = $conn->query("SELECT * FROM Users WHERE Username='$user' AND Password='$pass'");
    //checks if the user exists
    if ($result->num_rows > 0) {
        $_SESSION['username'] = $user;
        header("Location: dashboard.php");
        exit;// exits to prevent further code execution and ensure redirect
        //user does not exist gives an error message
    } else {
        $error = "Invalid login credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="login.php" class="logo">
                <span class="logo-icon">📚</span>
                <span class="logo-text">Library System</span>
            </a>
        </div>
    </header>

    <!-- Hero Section, this is to motivate the user -->
    <section class="hero">
        <div class="container">
            <h1>Find Your Next Great Read</h1>
            <p>Discover, reserve, and enjoy books from our extensive collection</p>
        </div>
    </section>

    <div class="container auth-container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="form-section">
            <h2>Sign In to Your Account</h2>
            <form method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Sign In</button>
            </form>
            
            <div class="auth-switch"> <!-- This is a link to registration page -->
                <p>Don't have an account? <a href="register.php">Register here</a></p>
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