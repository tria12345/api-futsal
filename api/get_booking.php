<?php
// backend/api/get_booking.php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // 1. Get unavailable timeslots for a court on a date
    if (isset($_GET['field_id']) && isset($_GET['book_date'])) {
        $field_id = $_GET['field_id'];
        $book_date = $_GET['book_date'];
        
        $query = "SELECT start_hour, end_hour FROM bookings 
                  WHERE field_id = :field_id AND book_date = :book_date AND payment_status != 'rejected'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":field_id", $field_id);
        $stmt->bindParam(":book_date", $book_date);
        $stmt->execute();
        
        $slots = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $slots[] = [
                "start_hour" => (int)$row['start_hour'],
                "end_hour" => (int)$row['end_hour']
            ];
        }
        
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $slots]);
        exit();
    }
    
    // 2. Fetch bookings for a specific user (Customer)
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $query = "SELECT b.*, f.name as field_name, f.image_url as field_image 
                  FROM bookings b 
                  JOIN fields f ON b.field_id = f.id 
                  WHERE b.user_id = :user_id 
                  ORDER BY b.id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
    } else {
        // 3. Admin view (all bookings)
        $query = "SELECT b.*, f.name as field_name, f.image_url as field_image, u.name as user_name, u.email as user_email 
                  FROM bookings b 
                  JOIN fields f ON b.field_id = f.id 
                  JOIN users u ON b.user_id = u.id 
                  ORDER BY b.id DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    
    $bookings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['id'] = (int)$row['id'];
        $row['user_id'] = (int)$row['user_id'];
        $row['field_id'] = (int)$row['field_id'];
        $row['start_hour'] = (int)$row['start_hour'];
        $row['end_hour'] = (int)$row['end_hour'];
        $row['total_price'] = (float)$row['total_price'];
        $row['checked_in'] = (int)$row['checked_in'];
        $bookings[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(["status" => "success", "data" => $bookings]);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Use GET."]);
}
?>
