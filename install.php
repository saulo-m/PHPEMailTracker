<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Set header content type
header('Content-Type: text/plain');

try {
    // Connect to database
    $conn = getDbConnection();
    
    echo "Connected to database successfully.\n\n";
    
    // Create tokens table
    $sql = "
    CREATE TABLE IF NOT EXISTS tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token VARCHAR(40) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_opens INT DEFAULT 0
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table 'tokens' created successfully.\n";
    } else {
        throw new Exception("Error creating table 'tokens': " . $conn->error);
    }
    
    // Create tracking_data table
    $sql = "
    CREATE TABLE IF NOT EXISTS tracking_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        device_info TEXT,
        geo_location TEXT,
        opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table 'tracking_data' created successfully.\n";
    } else {
        throw new Exception("Error creating table 'tracking_data': " . $conn->error);
    }
    
    // Create token_history table for saving token history for quick access in UI
    $sql = "
    CREATE TABLE IF NOT EXISTS token_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_ip VARCHAR(45),
        token_id INT NOT NULL,
        last_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE CASCADE,
        UNIQUE KEY (user_ip, token_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table 'token_history' created successfully.\n";
    } else {
        throw new Exception("Error creating table 'token_history': " . $conn->error);
    }
    
    echo "\nInstallation completed successfully!";
    echo "\n\nIMPORTANT: Please delete or rename this file after successful installation for security reasons.";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
