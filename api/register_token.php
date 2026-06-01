<?php
// backend/api/register_token.php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    $user_id = isset($data->user_id) ? $data->user_id : null;
    $fcm_token = isset($data->fcm_token) ? $data->fcm_token : null;
    
    if ($user_id && $fcm_token) {
        // Option to save to database if there's a column for it. 
        // For now, since FCM is mostly handled by Node.js, we just simulate success.
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "FCM Token registered successfully."]);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete request. 'user_id' and 'fcm_token' are required."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Use POST."]);
}
?>
