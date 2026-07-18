<?php
// MUST BE ON LINE 1: Secure the page
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require 'db.php';

// Fetch all bookings and mechanic data
$bookings = [];
$mechanics = [];

try {
    // Join bookings with mechanics to display mechanic names easily
    $stmt = $pdo->query("
        SELECT b.uuid as booking_uuid, b.client_name, b.phone, b.car_license, b.booked_date, b.mechanic_uuid, m.name as mechanic_name 
        FROM bookings b 
        JOIN mechanics m ON b.mechanic_uuid = m.uuid 
        ORDER BY b.booked_date ASC
    ");
    $bookings = $stmt->fetchAll();

    // Fetch mechanics list for the update dropdowns
    $mechStmt = $pdo->query("SELECT uuid, name FROM mechanics");
    $mechanics = $mechStmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVERSEER // Admin Terminal</title>
    <link rel="stylesheet" href="global.css">
</head>
<body>

    <!-- Admin Navbar -->
    <nav class="navbar">
        <div class="nav-links">
            <span style="color: var(--neon-cyan); font-size: 1.5rem; font-weight: 700; letter-spacing: 3px;">
                OVERSEER TERMINAL
            </span>
        </div>
        <div>
            <a href="logout.php" class="btn btn-danger">Terminate Session</a>
        </div>
    </nav>

    <!-- Admin Dashboard -->
    <main class="container" style="max-width: 1400px;">
        <h2>Active Deployments</h2>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Client ID</th>
                        <th>Comms</th>
                        <th>License Reg</th>
                        <th>Target Date</th>
                        <th>Assigned Tech</th>
                        <th>Override</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- PHP Iteration Starts Here -->
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No active deployments found in the database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <form action="update_appointment.php" method="POST">
                                <!-- Hidden ID to tell the backend WHICH booking to update -->
                                <input type="hidden" name="booking_uuid" value="<?php echo htmlspecialchars($booking['booking_uuid']); ?>">
                                
                                <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                <td><span style="color: var(--neon-cyan); font-family: monospace;"><?php echo htmlspecialchars($booking['car_license']); ?></span></td>
                                
                                <!-- Editable Date -->
                                <td>
                                    <input type="date" name="new_date" value="<?php echo htmlspecialchars($booking['booked_date']); ?>" class="table-input" required>
                                </td>
                                
                                <!-- Editable Mechanic -->
                                <td>
                                    <select name="new_mechanic" class="table-input" required>
                                        <?php foreach ($mechanics as $mech): ?>
                                            <option value="<?php echo htmlspecialchars($mech['uuid']); ?>" 
                                                <?php echo ($mech['uuid'] === $booking['mechanic_uuid']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($mech['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                
                                <!-- Submit Button -->
                                <td>
                                    <button type="submit" class="btn" style="padding: 0.4rem 1rem; font-size: 0.9rem;">
                                        Execute
                                    </button>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- PHP Iteration Ends Here -->
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>