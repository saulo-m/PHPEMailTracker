<?php
/**
 * Email Tracker - Main Application Entry Point
 * 
 * This file handles all routing for the Email Tracker application.
 * It can be deployed in any directory structure and will adapt to its environment.
 * 
 * URL Pattern examples:
 * - Main page: /
 * - Generate token: /api/NewToken
 * - Get tracking info: /api/GetInfo?token=YOUR_TOKEN
 * - Tracking pixel: /image/?token=YOUR_TOKEN
 * - Get history: /api/GetHistory
 * 
 * When embedding the tracking pixel in emails, use the following format:
 * <img src="http://your-domain.com/path/to/EmailTracker/image/?token=YOUR_TOKEN" width="1" height="1" alt="">
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Enable error reporting in debug mode
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set the request path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($basePath, '/') . '/';

// Remove the base path from the request
$requestPath = substr($requestPath, strlen($basePath));
$requestPath = trim($requestPath, '/');

// Route the request
if (empty($requestPath) || $requestPath === 'index.php') {
    // Main page
    include 'templates/index.php';
    exit;
} elseif (preg_match('#^api/NewToken$#i', $requestPath)) {
    // Generate new token API
    header('Content-Type: application/json');
    
    try {
        $token = generateToken();
        
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO tokens (token) VALUES (?)");
        $stmt->bind_param("s", $token);
        
        if ($stmt->execute()) {
            echo json_encode(['token' => $token]);
        } else {
            throw new Exception("Failed to create token");
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
} elseif (preg_match('#^api/GetInfo$#i', $requestPath)) {
    // Get tracking info API
    header('Content-Type: application/json');
    
    $token = $_GET['token'] ?? '';
    
    if (empty($token) || !preg_match('/^[0-9a-f]{40}$/i', $token)) {
        echo json_encode(['Ok' => false, 'Error' => 'Token Does Not Exist']);
        exit;
    }
    
    $conn = getDbConnection();
    
    // Get token ID and check if has been opened - compatibility with original schema
    $stmt = $conn->prepare("SELECT t.id, t.opened, COUNT(td.id) as open_count 
                           FROM tokens t 
                           LEFT JOIN tracking_data td ON t.id = td.token_id 
                           WHERE t.token = ?
                           GROUP BY t.id");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['Ok' => false, 'Error' => 'Token Does Not Exist']);
        exit;
    }
    
    $tokenData = $result->fetch_assoc();
    
    if ($tokenData['open_count'] == 0) {
        echo json_encode(['Ok' => false, 'Error' => 'The email has not been opened yet.']);
        exit;
    }
    
    // Skip token history - table doesn't exist in current schema
    // We'll implement this in a future update after running install.php
    $userIp = getClientIP();
    
    // Code below is commented out since token_history table doesn't exist yet
    /*
    $stmt = $conn->prepare("
        INSERT INTO token_history (user_ip, token_id) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE last_viewed = CURRENT_TIMESTAMP
    ");
    $stmt->bind_param("si", $userIp, $tokenData['id']);
    $stmt->execute();
    */
    
    // Get all tracking data for this token
    $stmt = $conn->prepare("
        SELECT * FROM tracking_data 
        WHERE token_id = ? 
        ORDER BY opened_at DESC
    ");
    $stmt->bind_param("i", $tokenData['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['Ok' => false, 'Error' => 'No tracking data found.']);
        exit;
    }
    
    // Get most recent tracking data (for compatibility with old version)
    $mostRecent = $result->fetch_assoc();
    $result->data_seek(0); // Reset result pointer
    
    // Build array of all tracking records
    $allTrackingData = [];
    while ($row = $result->fetch_assoc()) {
        $allTrackingData[] = [
            'ipaddr' => $row['ip_address'],
            'useragent' => $row['user_agent'],
            'deviceinfo' => $row['device_info'],
            'glocation' => $row['geo_location'],
            'time' => strtotime($row['opened_at']) * 1000, // Convert to milliseconds
        ];
    }
    
    // Format enhanced response with all tracking data
    // Using count of tracking records as total_opens since that column doesn't exist
    $total_opens = count($allTrackingData);
    
    $response = [
        'ipaddr' => $mostRecent['ip_address'],
        'useragent' => $mostRecent['user_agent'],
        'deviceinfo' => $mostRecent['device_info'],
        'glocation' => $mostRecent['geo_location'],
        'time' => strtotime($mostRecent['opened_at']) * 1000, // Convert to milliseconds
        'opened' => true,
        'total_opens' => $total_opens, // Calculate from the number of records
        'all_records' => $allTrackingData,
        'token_id' => $tokenData['id']
    ];
    
    echo json_encode($response);
    exit;
} elseif (preg_match('#^image/?#i', $requestPath)) {
    // Tracking image
    $token = $_GET['token'] ?? '';
    
    // Set headers for image
    header('Content-Type: image/png');
    
    // Get absolute path to tracking image
    $scriptDir = dirname(__FILE__);
    $trackingImagePath = $scriptDir . '/assets/img/tracking.png';
    
    // Function to serve the tracking image or fallback to transparent pixel
    function serveTrackingImage($imagePath) {
        if (file_exists($imagePath)) {
            readfile($imagePath);
        } else {
            // Fallback - use the transparent-pixel.php file
            $scriptDir = dirname(__FILE__);
            $fallbackPath = $scriptDir . '/transparent-pixel.php';
            
            if (file_exists($fallbackPath)) {
                include($fallbackPath);
            } else {
                // If even the fallback is missing, generate a pixel on the fly
                $transparentPixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
                echo $transparentPixel;
            }
        }
        exit;
    }
    
    // Validate token
    if (empty($token) || !preg_match('/^[0-9a-f]{40}$/i', $token)) {
        // Serve image anyway but don't track
        serveTrackingImage($trackingImagePath);
    }
    
    // Check if it's the Google Image Proxy
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strpos($userAgent, 'GoogleImageProxy') !== false) {
        // Don't track Google Image Proxy
        serveTrackingImage($trackingImagePath);
    }
    
    $conn = getDbConnection();
    
    // Get token ID - using original schema (without total_opens column)
    $stmt = $conn->prepare("SELECT id, opened FROM tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Invalid token, serve image anyway
        serveTrackingImage($trackingImagePath);
    }
    
    $tokenData = $result->fetch_assoc();
    $tokenId = $tokenData['id'];
    
    // Track every open, not just the first
    // Collect visitor information
    $ipAddress = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $deviceInfo = getDeviceInfo($userAgent);
    $geoLocation = getGeoLocation($ipAddress);
    
    // Mark as opened (using original schema with opened boolean)
    // Set opened=1 if not already set
    $stmt = $conn->prepare("UPDATE tokens SET opened = 1 WHERE id = ?");
    $stmt->bind_param("i", $tokenId);
    $stmt->execute();
    
    // Store tracking data for each open
    $stmt = $conn->prepare("
        INSERT INTO tracking_data 
        (token_id, ip_address, user_agent, device_info, geo_location) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $tokenId, $ipAddress, $userAgent, $deviceInfo, $geoLocation);
    $stmt->execute();
    
    // Serve the tracking image
    serveTrackingImage($trackingImagePath);
    exit;
} elseif (preg_match('#^api/GetHistory$#i', $requestPath)) {
    // Get token history for current user
    header('Content-Type: application/json');
    
    // Returning empty history for now since token_history table doesn't exist yet
    // This prevents JavaScript errors on the frontend
    echo json_encode(['history' => []]);
    exit;
} elseif (preg_match('#^assets/(.+)$#i', $requestPath, $matches)) {
    // Serve static assets
    $scriptDir = dirname(__FILE__);
    $filePath = $scriptDir . '/assets/' . $matches[1];
    $relativeFilePath = 'assets/' . $matches[1]; // Keep relative path as fallback
    
    // Try absolute path first, then relative path
    if (file_exists($filePath) || file_exists($relativeFilePath)) {
        // Use the file that exists (preference for absolute path)
        $finalPath = file_exists($filePath) ? $filePath : $relativeFilePath;
        
        // Determine content type
        $extension = pathinfo($finalPath, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'css': 
                header('Content-Type: text/css'); 
                break;
            case 'js': 
                header('Content-Type: application/javascript'); 
                break;
            case 'png': 
                header('Content-Type: image/png'); 
                break;
            case 'jpg': 
            case 'jpeg': 
                header('Content-Type: image/jpeg'); 
                break;
            case 'gif': 
                header('Content-Type: image/gif'); 
                break;
        }
        
        // Output appropriate cache headers
        $maxAge = 86400; // 1 day
        header("Cache-Control: max-age=$maxAge, public");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");
        
        readfile($finalPath);
        exit;
    }
}

// 404 Not Found
header('HTTP/1.0 404 Not Found');
echo '404 - Page not found';
