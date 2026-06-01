<?php
// backend/api/hapus_booking.php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Assuming JSON payload or form data
    $data = json_decode(file_get_contents("php://input"));
    
    $booking_id = isset($data->booking_id) ? $data->booking_id : (isset($_POST['booking_id']) ? $_POST['booking_id'] : null);
    
    if ($booking_id) {
        $query = "DELETE FROM bookings WHERE id = :booking_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Booking deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete booking."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete request. 'booking_id' is required."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Use POST."]);
}
?>
