<?php
session_start();
require 'db.php';

$error_message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Note: For a production environment, use password_verify($password, $user['password']).
        // For this assignment, we are doing a direct string comparison for simplicity based on your manual SQL inserts.
        if ($user && $password === $user['password']) {
            
            // Set session variables
            $_SESSION['user_uuid'] = $user['uuid'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Route user based on their role
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error_message = "ACCESS DENIED: Invalid credentials.";
        }
    } catch (PDOException $e) {
        $error_message = "SYSTEM ERROR: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="global.css">
    <style>
        .login-wrapper {
            min-height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .error-alert {
            background: rgba(255, 0, 60, 0.1);
            border: 1px solid var(--neon-red);
            color: var(--neon-red);
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<!-- Toast Notification System -->
<?php if (isset($_SESSION['toast'])): ?>
    <div class="toast-container">
        <div class="toast <?php echo $_SESSION['toast']['type']; ?>" id="sys-toast">
            <?php echo htmlspecialchars($_SESSION['toast']['msg']); ?>
        </div>
    </div>
    <script>
        // Remove from DOM after 5 seconds
        setTimeout(() => {
            const toast = document.getElementById('sys-toast');
            if (toast) toast.remove();
        }, 5000);
    </script>
    <?php unset($_SESSION['toast']); // Clear the message so it doesn't show again ?>
<?php endif; ?>
</body>
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php">Return to Main</a>
        </div>
    </nav>

    <main class="container login-wrapper">
        <div class="glass-panel" style="width: 100%; max-width: 450px;">
            <h2 style="text-align: center; color: var(--neon-cyan);">Login</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Agent Email</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@workshop.com" required>
                </div>
                
                <div class="form-group">
                    <label>Security Key</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-top: 1.5rem; padding: 1rem; font-size: 1.2rem;">
                    Authenticate
                </button>
                <div style="text-align: center; margin-top: 1.5rem;">
    <span style="color: var(--text-muted);">No active profile?</span>
    <a href="signup.php" style="color: var(--neon-cyan); text-decoration: none; font-weight: 600; margin-left: 0.5rem;">Register Here</a>
</div>
            </form>
        </div>
        
    </main>


</html>