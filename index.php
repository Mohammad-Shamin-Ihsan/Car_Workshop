<?php
session_start();
require 'db.php';

// Fetch available mechanics from the database to populate the dropdown
$mechanics = [];
try {
    $stmt = $pdo->query("SELECT uuid, name, specialization FROM mechanics");
    $mechanics = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$booking_message = '';
$booking_message_type = '';
if (isset($_SESSION['booking_msg'])) {
    $booking_message = $_SESSION['booking_msg'];
    $booking_message_type = $_SESSION['booking_msg_type'] ?? 'info';
    unset($_SESSION['booking_msg'], $_SESSION['booking_msg_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop</title>
    <link rel="stylesheet" href="global.css">
</head>
<body>
    <!-- Cinematic Navbar -->
    <nav class="navbar">
        <div class="nav-links">
            <a href="#mechanics">Mechanics</a>
            <a href="#about">About Us</a>
        </div>
        <div>
            <!-- Dynamic Login/Logout Button -->
            <?php if (isset($_SESSION['user_uuid'])): ?>
                <a href="logout.php" class="btn btn-danger">log Out (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Booking Interface -->
    <main class="container">
        <div class="glass-panel" style="max-width: 700px; margin: 0 auto;">
            <h2 style="text-align: center; color: var(--neon-cyan);">Initialize Appointment</h2>
            
            <?php if (!empty($booking_message)): ?>
                <div class="alert-card <?php echo $booking_message_type === 'error' ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($booking_message); ?>
                </div>
            <?php endif; ?>

            <form action="process_booking.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="client_name" class="form-control" placeholder="Full Name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phones</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Full Address" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Vehicle License ID</label>
                        <input type="text" name="car_license" class="form-control" placeholder="ABC-1234" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Engine Core ID</label>
                        <input type="text" name="car_engine" class="form-control" placeholder="Engine Number" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Booking Date</label>
                        <input type="date" name="booked_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Assign Technician</label>
                        <select name="mechanic_uuid" class="form-control" required>
                            <option value="" disabled selected>Select Available Tech...</option>
                            <!-- Dynamic PHP Output for Mechanics -->
                            <?php foreach ($mechanics as $mech): ?>
                                <option value="<?php echo htmlspecialchars($mech['uuid']); ?>">
                                    <?php echo htmlspecialchars($mech['name'] . ' // ' . $mech['specialization']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-top: 1rem; padding: 1rem; font-size: 1.3rem;">
                    Book Appointment
                </button>
            </form>
        </div>
    </main>

    <!-- Mechanics Section -->
    <section id="mechanics" class="container">
        <h2>Active Technicians</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Loop through mechanics for display -->
            <?php foreach ($mechanics as $mech): ?>
            <div class="glass-panel" style="padding: 2rem; border-top-color: #fff;">
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #fff;"><?php echo htmlspecialchars($mech['name']); ?></h3>
                <p style="color: var(--text-muted); font-size: 1.1rem;"><?php echo htmlspecialchars($mech['specialization']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="container">
        <div class="glass-panel" style="border-top-color: transparent;">
            <h2>Facility Overview</h2>
            <p style="font-size: 1.2rem; line-height: 1.6; color: #bbb;">
                Welcome to the prime automotive repair facility. Equipped with state-of-the-art diagnostics and 5 senior-level technicians, we ensure your vehicle operates at peak efficiency. Engage our terminal above to reserve your deployment slot in real-time.
            </p>
        </div>
    </section>
    
</body>
</html>