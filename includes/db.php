<?php
require_once 'config.php';

/**
 * Get a database connection
 * 
 * @return mysqli Database connection object
 */
function getDbConnection() {
    static $conn;
    
    if (!isset($conn)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            if (DEBUG_MODE) {
                die('Database connection failed: ' . $conn->connect_error);
            } else {
                die('Database connection failed. Please try again later.');
            }
        }
        
        $conn->set_charset('utf8mb4');
    }
    
    return $conn;
}

/**
 * Execute a query and return the result
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for the query
 * @param string $types Types of the parameters (i=integer, s=string, d=double, b=blob)
 * @return mysqli_result|bool Result of the query
 */
function executeQuery($sql, $params = [], $types = '') {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        if (DEBUG_MODE) {
            die('Query preparation failed: ' . $conn->error);
        } else {
            die('An error occurred. Please try again later.');
        }
    }
    
    if (!empty($params)) {
        if (empty($types)) {
            // Auto-detect types if not provided
            $types = str_repeat('s', count($params));
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    return $stmt->get_result();
}
