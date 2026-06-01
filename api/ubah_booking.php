<?php
// backend/api/verify.php

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->booking_id) && !empty($data->status)) {
    $booking_id = $data->booking_id;
    $status = $data->status; // 'approved' or 'rejected'
    
    // 1. Update status in Database
    $query = "UPDATE bookings SET payment_status = :status WHERE id = :booking_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Fetch user and court details for notifications
        $info_query = "SELECT b.*, u.name as user_name, u.email as user_email, f.name as field_name 
                       FROM bookings b 
                       JOIN users u ON b.user_id = u.id 
                       JOIN fields f ON b.field_id = f.id 
                       WHERE b.id = :booking_id LIMIT 1";
        $info_stmt = $db->prepare($info_query);
        $info_stmt->bindParam(":booking_id", $booking_id);
        $info_stmt->execute();
        $booking_info = $info_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking_info) {
            // 2. Trigger Real-time Broadcast via Node.js WebSockets
            trigger_websocket_broadcast($booking_id, $status, $booking_info['team_name']);
            
            // 3. Trigger Push Notification via FCM HTTP v1
            trigger_fcm_notification($booking_info['user_email'], $booking_info['field_name'], $status);
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Booking transaction verified successfully (" . $status . ")"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to verify booking"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete request. 'booking_id' and 'status' are required."]);
}

/**
 * Triggers a real-time broadcast via the local Node.js Socket.IO server.
 */
function trigger_websocket_broadcast($booking_id, $status, $team_name) {
    $url = 'http://localhost:3000/api/broadcast-booking';
    $payload = json_encode([
        'event' => 'booking_updated',
        'booking_id' => (int)$booking_id,
        'status' => $status,
        'team_name' => $team_name
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Quick timeout to not block
    curl_exec($ch);
    curl_close($ch);
}

/**
 * Simulates triggering a background push notification via the FCM HTTP v1 API.
 */
function trigger_fcm_notification($user_email, $court_name, $status) {
    // In a fully configured system, you fetch the FCM service account token, 
    // construct the JSON payload complying with the v1 API standard, and send it:
    // POST https://fcm.googleapis.com/v1/projects/{your-project-id}/messages:send
    
    $title = "Booking Status Update! 🏟️";
    $body = "Your booking for " . $court_name . " has been " . strtoupper($status) . " by the Admin.";
    if ($status === 'approved') {
        $body .= " Get ready for the game! 🔥";
    } else {
        $body .= " Please contact support if you uploaded the wrong receipt.";
    }

    // Log the event locally for verification/inspection
    $log_message = "[" . date('Y-m-d H:i:s') . "] FCM HTTP v1 Broadcast to " . $user_email . " -> Title: " . $title . " | Body: " . $body . "\n";
    file_put_contents("../uploads/fcm_notifications.log", $log_message, FILE_APPEND);
}
?>
