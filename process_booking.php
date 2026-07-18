<?php
// process_booking.php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Retrieve POST data
    $client_name   = $_POST['client_name'];
    $address       = $_POST['address'];
    $phone         = $_POST['phone'];
    $car_license   = $_POST['car_license'];
    $car_engine    = $_POST['car_engine'];
    $booked_date   = $_POST['booked_date'];
    $mechanic_uuid = $_POST['mechanic_uuid'];
    
    // Default user_uuid to NULL for guest bookings
    $user_uuid = null; 

    try {
        // 2. Check Mechanic Availability (Max 4 active cars per day)
        $stmt = $pdo->prepare("SELECT COUNT(*) AS active_cars FROM bookings WHERE mechanic_uuid = ? AND booked_date = ?");
        $stmt->execute([$mechanic_uuid, $booked_date]);
        $result = $stmt->fetch();

        if ($result['active_cars'] >= 4) {
            die("Sorry, this mechanic is fully booked on that date. Please select another mechanic or date.");
        }

        // 3. Insert the Booking
        // We use MySQL's UUID() function to automatically generate the primary key
        $insertQuery = "INSERT INTO bookings 
                        (uuid, user_uuid, client_name, address, phone, car_license, car_engine, booked_date, mechanic_uuid) 
                        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            $user_uuid, 
            $client_name, 
            $address, 
            $phone, 
            $car_license, 
            $car_engine, 
            $booked_date, 
            $mechanic_uuid
        ]);

        echo "Success! Your appointment has been booked.";

    } catch (PDOException $e) {
        // 4. Catch the Composite Unique Constraint violation (Error Code 1062)
        if ($e->getCode() == 23000 || strpos($e->getMessage(), '1062') !== false) {
            die("Error: You have already booked an appointment for this car on this specific date.");
        } else {
            // Catch any other database errors
            die("An error occurred: " . $e->getMessage());
        }
    }
}
?>