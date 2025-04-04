<?php
// This file generates a transparent 1x1 pixel on the fly
// Useful as a fallback if the tracking.png file is missing

// Set content type to PNG image
header('Content-Type: image/png');

// Set cache headers
$maxAge = 31536000; // 1 year
header("Cache-Control: max-age=$maxAge, public");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");

// Base64 encoded transparent 1x1 PNG pixel
$transparentPixelData = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

// Output the decoded image data
echo base64_decode($transparentPixelData);
