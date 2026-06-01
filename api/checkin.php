<?php
// backend/api/checkin.php

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->booking_id)) {
    $booking_id = $data->booking_id;
    
    $query = "UPDATE bookings SET checked_in = 1 WHERE id = :booking_id AND payment_status = 'approved'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Team checked in successfully! Enjoy the match!"
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to check in. Ensure the booking exists and the payment is already approved."
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database execution failed"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete request. 'booking_id' is required."]);
}
?>
