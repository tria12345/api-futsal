CREATE DATABASE IF NOT EXISTS futsal_reserve;
USE futsal_reserve;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    avatar VARCHAR(255) NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fields (Courts) Table
CREATE TABLE IF NOT EXISTS fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price_per_hour DECIMAL(10, 2) NOT NULL,
    is_maintenance TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    field_id INT NOT NULL,
    book_date DATE NOT NULL,
    start_hour INT NOT NULL, -- e.g., 8 (for 08:00 AM)
    end_hour INT NOT NULL,   -- e.g., 9 (for 09:00 AM)
    team_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    payment_receipt VARCHAR(500) NULL,
    checked_in TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
);

-- Seed Initial Court Data (Lapangan 1 - 6)
INSERT INTO fields (name, description, price_per_hour, is_maintenance, image_url) VALUES
('Lapangan 1', 'Premium Indoor Futsal with standard vinyl court. Ideal for high speed play and tournament simulation.', 65000.00, 0, 'https://images.unsplash.com/photo-1577223625856-74552436858d?q=80&w=600&auto=format&fit=crop'),
('Lapangan 2', 'Top-grade Indoor Futsal. Beautiful interlock tiles designed for high traction and safety.', 65000.00, 0, 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?q=80&w=600&auto=format&fit=crop'),
('Lapangan 3', 'Professional Vinyl Futsal field. Premium shock absorption and bright studio lighting.', 65000.00, 0, 'https://images.unsplash.com/photo-1518063319789-7217e6706b04?q=80&w=600&auto=format&fit=crop'),
('Lapangan 4', 'Premium Turf Futsal field. Real grass feel with excellent cushion and joint comfort.', 65000.00, 0, 'https://images.unsplash.com/photo-1459865264687-595d652de67e?q=80&w=600&auto=format&fit=crop'),
('Lapangan 5', 'Premium Indoor Futsal with standard vinyl flooring. Perfect for team training.', 65000.00, 0, 'https://images.unsplash.com/photo-1577223625856-74552436858d?q=80&w=600&auto=format&fit=crop'),
('Lapangan 6', 'Modern Futsal field with premium interlocking polypropylene tiles.', 65000.00, 0, 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?q=80&w=600&auto=format&fit=crop');
