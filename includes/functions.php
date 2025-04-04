<?php
/**
 * Get the client's IP address
 * 
 * @return string IP address
 */
function getClientIP() {
    $ipAddress = '';
    
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ipAddress = trim($ipAddressList[0]);
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ipAddress;
}

/**
 * Parse user agent to extract device information
 * 
 * @param string $userAgent User agent string
 * @return string Formatted device information
 */
function getDeviceInfo($userAgent) {
    $device = '';
    
    // Mobile detection
    $isMobile = (bool) preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', $userAgent);
    
    // Tablet detection
    $isTablet = (bool) preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $userAgent);
    
    // OS detection
    $os = 'Unknown OS';
    if (preg_match('/windows|win32|win64/i', $userAgent)) {
        $os = 'Windows';
    } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $os = 'macOS';
    } elseif (preg_match('/linux/i', $userAgent)) {
        $os = 'Linux';
    } elseif (preg_match('/android/i', $userAgent)) {
        $os = 'Android';
    } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
        $os = 'iOS';
    }
    
    // Browser detection
    $browser = 'Unknown Browser';
    if (preg_match('/msie|trident/i', $userAgent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/firefox/i', $userAgent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome|crios|crmo/i', $userAgent)) {
        $browser = 'Safari';
    } elseif (preg_match('/edg/i', $userAgent)) {
        $browser = 'Edge';
    } elseif (preg_match('/opera|opr\//i', $userAgent)) {
        $browser = 'Opera';
    }
    
    $device = "Name: $browser\nOS: $os\nMobile: " . ($isMobile ? 'Yes' : 'No') . "\nTablet: " . ($isTablet ? 'Yes' : 'No');
    
    return $device;
}

/**
 * Get geolocation information from IP address
 * 
 * @param string $ip IP address
 * @return string Formatted geolocation information
 */
function getGeoLocation($ip) {
    $url = "http://ip-api.com/json/" . urlencode($ip);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) {
        return "Location: Unknown";
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['status']) || $data['status'] !== 'success') {
        return "Location: Unknown";
    }
    
    $geoLocation = "\nCountry: " . ($data['country'] ?? 'Unknown');
    $geoLocation .= "\nRegion: " . ($data['regionName'] ?? 'Unknown');
    $geoLocation .= "\nCity: " . ($data['city'] ?? 'Unknown');
    $geoLocation .= "\nZip: " . ($data['zip'] ?? 'Unknown');
    $geoLocation .= "\nISP: " . ($data['isp'] ?? 'Unknown');
    
    return $geoLocation;
}

/**
 * Generate a random token
 * 
 * @return string Random token (40 characters hex)
 */
function generateToken() {
    $bytes = random_bytes(20);
    return bin2hex($bytes);
}

/**
 * Get the base URL of the application
 * 
 * @return string Base URL
 */
function getBaseUrl() {
    $baseUrl = APP_URL;
    
    if (empty($baseUrl)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = $protocol . '://' . $host . $scriptDir;
        
        // Remove /index.php from the end if present
        $baseUrl = preg_replace('/\/index\.php$/', '', $baseUrl);
        
        // Ensure no trailing slash
        $baseUrl = rtrim($baseUrl, '/');
    }
    
    return $baseUrl;
}
