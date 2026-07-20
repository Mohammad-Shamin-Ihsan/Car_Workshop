<?php
// update_appointment.php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_uuid  = $_POST['booking_uuid'];
    $new_date      = $_POST['new_date'];
    $new_mechanic  = $_POST['new_mechanic'];

    try {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) AS active_cars FROM bookings WHERE mechanic_uuid = ? AND booked_date = ? AND uuid != ?");
        $checkStmt->execute([$new_mechanic, $new_date, $booking_uuid]);
        $result = $checkStmt->fetch();

        if ($result['active_cars'] >= 4) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'OVERRIDE FAILED: Target technician is at maximum capacity.'];
            header("Location: admin.php");
            exit;
        }

        $updateQuery = "UPDATE bookings SET mechanic_uuid = ?, booked_date = ? WHERE uuid = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$new_mechanic, $new_date, $booking_uuid]);

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'OVERRIDE SUCCESSFUL: Parameters updated.'];
        header("Location: admin.php");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'OVERRIDE FAILED: Duplicate vehicle deployment detected.'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'SYSTEM ERROR: ' . $e->getMessage()];
        }
        header("Location: admin.php");
        exit;
    }
}
?>