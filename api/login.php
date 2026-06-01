<?php
// backend/api/auth.php

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->name)) {
    $email = $data->email;
    $name = $data->name;
    $google_id = !empty($data->google_id) ? $data->google_id : md5($email);
    $avatar = !empty($data->avatar) ? $data->avatar : "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($name);
    
    // Check if user already exists
    $query = "SELECT * FROM users WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Healing: If existing user is admin@futsal.com or email contains 'admin', ensure role is updated to 'admin' in the database!
        if ((strtolower($email) === 'admin@futsal.com' || strpos(strtolower($email), 'admin') !== false) && $user['role'] !== 'admin') {
            $update_query = "UPDATE users SET role = 'admin' WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(":id", $user['id'], PDO::PARAM_INT);
            $update_stmt->execute();
            $user['role'] = 'admin';
        }

        // User exists, return user details
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "data" => [
                "id" => (int)$user['id'],
                "google_id" => $user['google_id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "avatar" => $user['avatar'],
                "role" => $user['role']
            ]
        ]);
    } else {
        // Create new user. Determine role (if email contains 'admin', make admin, otherwise customer)
        // Tentukan role dasar
        $role = "customer";
        
        // DAFTAR EMAIL GMAIL YANG INGIN DIJADIKAN ADMIN:
        $admin_emails = [
            "triatria329@gmail.com", // <-- Tulis Gmail asli kamu di sini!
            "triaibnatussholihah@gmail.com", // <-- Tambahkan jika ada admin lain
            "admin@futsal.com" // <-- Tambahkan email admin demo bypass
        ];
        
        if (in_array(strtolower($email), array_map('strtolower', $admin_emails)) || strpos(strtolower($email), 'admin') !== false) {
            $role = "admin";
        }

        $insert_query = "INSERT INTO users (google_id, name, email, avatar, role) VALUES (:google_id, :name, :email, :avatar, :role)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(":google_id", $google_id);
        $insert_stmt->bindParam(":name", $name);
        $insert_stmt->bindParam(":email", $email);
        $insert_stmt->bindParam(":avatar", $avatar);
        $insert_stmt->bindParam(":role", $role);
        
        if ($insert_stmt->execute()) {
            $new_id = $db->lastInsertId();
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "User registered and logged in successfully",
                "data" => [
                    "id" => (int)$new_id,
                    "google_id" => $google_id,
                    "name" => $name,
                    "email" => $email,
                    "avatar" => $avatar,
                    "role" => $role
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to register user"]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete login data. 'email' and 'name' are required."]);
}
?>
