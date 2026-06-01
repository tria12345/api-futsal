<?php
// backend/api/tambah_booking.php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Read from $_POST for form fields
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : null;
    $book_date = isset($_POST['book_date']) ? $_POST['book_date'] : null;
    $start_hour = isset($_POST['start_hour']) ? $_POST['start_hour'] : null;
    $end_hour = isset($_POST['end_hour']) ? $_POST['end_hour'] : null;
    $team_name = isset($_POST['team_name']) ? $_POST['team_name'] : null;
    $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : null;
    $total_price = isset($_POST['total_price']) ? $_POST['total_price'] : null;
    
    if ($user_id && $field_id && $book_date && $start_hour && $end_hour && $team_name && $phone_number && $total_price) {
        
        // Double booking validation
        $check_query = "SELECT COUNT(*) as count FROM bookings 
                        WHERE field_id = :field_id AND book_date = :book_date 
                        AND start_hour = :start_hour AND payment_status != 'rejected'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":field_id", $field_id);
        $check_stmt->bindParam(":book_date", $book_date);
        $check_stmt->bindParam(":start_hour", $start_hour);
        $check_stmt->execute();
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['count'] > 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "This time slot is already booked."]);
            exit();
        }

        // Receipt Upload Handling
        $receipt_path = "";
        if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "../uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_tmp = $_FILES['payment_receipt']['tmp_name'];
            $file_name = $_FILES['payment_receipt']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png'];
            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = uniqid("receipt_", true) . "." . $file_ext;
                $dest_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Store relative URL path
                    $receipt_path = "uploads/" . $new_file_name;
                } else {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => "Failed to save uploaded receipt image."]);
                    exit();
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid receipt file format. Only JPG, JPEG, and PNG are allowed."]);
                exit();
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Payment receipt receipt image upload is required."]);
            exit();
        }
        
        // Save to Database
        $query = "INSERT INTO bookings (user_id, field_id, book_date, start_hour, end_hour, team_name, phone_number, total_price, payment_receipt, payment_status, checked_in) 
                  VALUES (:user_id, :field_id, :book_date, :start_hour, :end_hour, :team_name, :phone_number, :total_price, :payment_receipt, 'pending', 0)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":field_id", $field_id);
        $stmt->bindParam(":book_date", $book_date);
        $stmt->bindParam(":start_hour", $start_hour);
        $stmt->bindParam(":end_hour", $end_hour);
        $stmt->bindParam(":team_name", $team_name);
        $stmt->bindParam(":phone_number", $phone_number);
        $stmt->bindParam(":total_price", $total_price);
        $stmt->bindParam(":payment_receipt", $receipt_path);
        
        if ($stmt->execute()) {
            $booking_id = $db->lastInsertId();
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Booking submitted successfully. Waiting for admin approval.",
                "data" => [
                    "id" => (int)$booking_id,
                    "user_id" => (int)$user_id,
                    "field_id" => (int)$field_id,
                    "book_date" => $book_date,
                    "start_hour" => (int)$start_hour,
                    "end_hour" => (int)$end_hour,
                    "team_name" => $team_name,
                    "phone_number" => $phone_number,
                    "total_price" => (float)$total_price,
                    "payment_receipt" => $receipt_path,
                    "payment_status" => "pending",
                    "checked_in" => 0
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to save booking to database."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete booking form data."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Use POST."]);
}
?>
