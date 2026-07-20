<?php
// process_booking.php
session_start(); // Ensure session is started here!
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_name   = $_POST['client_name'];
    $address       = $_POST['address'];
    $phone         = $_POST['phone'];
    $car_license   = $_POST['car_license'];
    $car_engine    = $_POST['car_engine'];
    $booked_date   = $_POST['booked_date'];
    $mechanic_uuid = $_POST['mechanic_uuid'];
    
    $user_uuid = isset($_SESSION['user_uuid']) ? $_SESSION['user_uuid'] : null;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS active_cars FROM bookings WHERE mechanic_uuid = ? AND booked_date = ?");
        $stmt->execute([$mechanic_uuid, $booked_date]);
        $result = $stmt->fetch();

        if ($result['active_cars'] >= 4) {
            $_SESSION['booking_msg_type'] = 'error';
            $_SESSION['booking_msg'] = 'DENIED: Technician fully booked on this date.';
            header("Location: index.php");
            exit;
        }

        $insertQuery = "INSERT INTO bookings (uuid, user_uuid, client_name, address, phone, car_license, car_engine, booked_date, mechanic_uuid) 
                        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$user_uuid, $client_name, $address, $phone, $car_license, $car_engine, $booked_date, $mechanic_uuid]);

        $_SESSION['booking_msg_type'] = 'success';
        $_SESSION['booking_msg'] = 'SUCCESS: Deployment locked.';
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
            $_SESSION['booking_msg_type'] = 'error';
            $_SESSION['booking_msg'] = 'DENIED: Vehicle already deployed on this date.';
        } else {
            $_SESSION['booking_msg_type'] = 'error';
            $_SESSION['booking_msg'] = 'SYSTEM ERROR: ' . $e->getMessage();
        }
        header("Location: index.php");
        exit;
    }
}
?>