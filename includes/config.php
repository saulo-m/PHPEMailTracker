<?php
/**
 * Configuration file for EmailTracker
 * 
 * This file loads configuration from .env file
 */

// Load environment variables from .env file
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        trigger_error(".env file not found. Please create one from .env.example", E_USER_WARNING);
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!empty($name)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    
    return true;
}

// Load .env file from project root
loadEnv(dirname(__DIR__) . '/.env');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'emailtracker');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application settings
define('APP_URL', getenv('APP_URL') ?: ''); // Will be auto-detected in most cases
define('DEBUG_MODE', getenv('DEBUG_MODE') ? filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN) : false);
