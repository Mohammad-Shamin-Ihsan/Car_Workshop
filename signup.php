<?php
session_start();
require 'db.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if ($password !== $confirm_password) {
        $error_message = "SECURITY ALERT: Security keys do not match.";
    } else {
        try {
            // Check if the email is already registered
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $error_message = "ACCESS DENIED: Agent email is already registered in the system.";
            } else {
                // Insert the new user into the database
                // Note: We are storing the password as plain text to match your current login.php logic. 
                // In a production environment, always use password_hash()!
                $insertStmt = $pdo->prepare("INSERT INTO users (uuid, name, email, password, role) VALUES (UUID(), ?, ?, ?, 'client')");
                $insertStmt->execute([$name, $email, $password]);

                $success_message = "REGISTRATION COMPLETE: Credentials accepted. You may now log in.";
            }
        } catch (PDOException $e) {
            $error_message = "SYSTEM ERROR: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYS-CORE // Agent Registration</title>
    <link rel="stylesheet" href="global.css">
    <style>
        .signup-wrapper {
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
        .success-alert {
            background: rgba(0, 243, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
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
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php">Return to Main</a>
        </div>
        <div>
            <a href="login.php" class="btn">Login</a>
        </div>
    </nav>

    <main class="container signup-wrapper">
        <div class="glass-panel" style="width: 100%; max-width: 500px;">
            <h2 style="text-align: center; color: var(--neon-cyan);">Agent Registration</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-alert">
                    <?php echo $success_message; ?>
                </div>
            <?php else: ?>

            <form action="signup.php" method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="agent@workshop.com" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    </div>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-top: 1.5rem; padding: 1rem; font-size: 1.2rem;">
                    Sign Up
                </button>
            </form>
            
            <?php endif; ?>

        </div>
    </main>

</body>
</html>