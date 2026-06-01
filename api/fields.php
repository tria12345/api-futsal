<?php
// backend/api/fields.php

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Check if client is customer (hide maintenance fields) or admin (show all)
    $filter_maintenance = isset($_GET['customer']) && $_GET['customer'] == 'true';
    
    if ($filter_maintenance) {
        $query = "SELECT * FROM fields WHERE is_maintenance = 0 ORDER BY id DESC";
    } else {
        $query = "SELECT * FROM fields ORDER BY id DESC";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $fields = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['id'] = (int)$row['id'];
        $row['price_per_hour'] = (float)$row['price_per_hour'];
        $row['is_maintenance'] = (int)$row['is_maintenance'];
        $fields[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(["status" => "success", "data" => $fields]);

} elseif ($method == 'POST') {
    // Create new field
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->name) && !empty($data->price_per_hour)) {
        $name = $data->name;
        $description = !empty($data->description) ? $data->description : "";
        $price_per_hour = $data->price_per_hour;
        $image_url = !empty($data->image_url) ? $data->image_url : "https://images.unsplash.com/photo-1577223625856-74552436858d?q=80&w=600&auto=format&fit=crop";
        
        $query = "INSERT INTO fields (name, description, price_per_hour, image_url, is_maintenance) VALUES (:name, :description, :price_per_hour, :image_url, 0)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price_per_hour", $price_per_hour);
        $stmt->bindParam(":image_url", $image_url);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Court created successfully",
                "data" => [
                    "id" => (int)$db->lastInsertId(),
                    "name" => $name,
                    "description" => $description,
                    "price_per_hour" => (float)$price_per_hour,
                    "image_url" => $image_url,
                    "is_maintenance" => 0
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create court"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete court data. 'name' and 'price_per_hour' are required."]);
    }

} elseif ($method == 'PUT') {
    // Toggle maintenance status or edit court
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->id) && isset($data->is_maintenance)) {
        $id = $data->id;
        $is_maintenance = $data->is_maintenance ? 1 : 0;
        
        $query = "UPDATE fields SET is_maintenance = :is_maintenance WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":is_maintenance", $is_maintenance, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Court maintenance status updated successfully",
                "data" => [
                    "id" => (int)$id,
                    "is_maintenance" => $is_maintenance
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update maintenance status"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing parameters 'id' and 'is_maintenance'."]);
    }
}
?>
