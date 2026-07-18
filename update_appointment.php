<?php
// update_appointment.php
require 'db.php';

// In a real application, ensure you check if the admin is logged in here using $_SESSION

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_uuid  = $_POST['booking_uuid'];
    $new_date      = $_POST['new_date'];
    $new_mechanic  = $_POST['new_mechanic'];

    try {
        // 1. Check if the newly assigned mechanic has free slots on the new date
        $checkStmt = $pdo->prepare("SELECT COUNT(*) AS active_cars FROM bookings WHERE mechanic_uuid = ? AND booked_date = ? AND uuid != ?");
        // We exclude the current booking_uuid from the count in case the admin is only changing the mechanic but keeping the same date
        $checkStmt->execute([$new_mechanic, $new_date, $booking_uuid]);
        $result = $checkStmt->fetch();

        if ($result['active_cars'] >= 4) {
            die("Update failed: The selected mechanic is fully booked on that date.");
        }

        // 2. Update the booking
        $updateQuery = "UPDATE bookings SET mechanic_uuid = ?, booked_date = ? WHERE uuid = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$new_mechanic, $new_date, $booking_uuid]);

        echo "Appointment successfully updated!";
        // header("Location: admin_dashboard.php"); // Redirect back to admin panel

    } catch (PDOException $e) {
        // Handle duplicate constraints in case the admin changes the date to a day the car is already booked
        if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
            die("Update failed: This car already has an appointment on the new date selected.");
        } else {
            die("An error occurred: " . $e->getMessage());
        }
    }
}
?>