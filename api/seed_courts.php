<?php
// backend/api/seed_courts.php

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

try {
    // Temporarily disable foreign keys to clean up
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE bookings;");
    $db->exec("TRUNCATE TABLE fields;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $courts = [
        [
            "name" => "Lapangan 1",
            "description" => "Premium Indoor Futsal with standard vinyl court. Ideal for high speed play and tournament simulation.",
            "price_per_hour" => 65000.00,
            "image_url" => "uploads/lapangan_1.png"
        ],
        [
            "name" => "Lapangan 2",
            "description" => "Top-grade Indoor Futsal. Beautiful interlock tiles designed for high traction and safety.",
            "price_per_hour" => 65000.00,
            "image_url" => "uploads/lapangan_2.png"
        ],
        [
            "name" => "Lapangan 3",
            "description" => "Professional Vinyl Futsal field. Premium shock absorption and bright studio lighting.",
            "price_per_hour" => 65000.00,
            "image_url" => "uploads/lapangan_3.png"
        ],
        [
            "name" => "Lapangan 4",
            "description" => "Premium Turf Futsal field. Real grass feel with excellent cushion and joint comfort.",
            "price_per_hour" => 65000.00,
            "image_url" => "uploads/lapangan_4.png"
        ],
        [
            "name" => "Lapangan 5",
            "description" => "Premium Indoor Futsal with standard vinyl flooring. Perfect for team training.",
            "price_per_hour" => 65000.00,
            "image_url" => "uploads/lapangan_5.png"
        ],
        [
            "name" => "Lapangan 6",
            "description" => "Modern Futsal field with premium interlocking polypropylene tiles.",
            "price_per_hour" => 65000.00,
            "image_url" => "uploads/lapangan_6.png"
        ]
    ];

    $query = "INSERT INTO fields (name, description, price_per_hour, image_url, is_maintenance) VALUES (:name, :description, :price_per_hour, :image_url, 0)";
    $stmt = $db->prepare($query);

    foreach ($courts as $court) {
        $stmt->bindParam(":name", $court['name']);
        $stmt->bindParam(":description", $court['description']);
        $stmt->bindParam(":price_per_hour", $court['price_per_hour']);
        $stmt->bindParam(":image_url", $court['image_url']);
        $stmt->execute();
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Database successfully seeded with 6 standard courts (Lapangan 1 to Lapangan 6)."
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to seed database: " . $e->getMessage()
    ]);
}
?>
