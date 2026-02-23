<?php
// public/captcha.php - STANDALONE CAPTCHA (No dependencies)

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear ALL output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Generate random code
$chars = '23456789abcdefghjkmnpqrstuvwxyz';
$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[rand(0, strlen($chars) - 1)];
}

// Store in session
$_SESSION['captcha_code'] = $code;

// Create image
$width = 150;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// Define colors
$bgColor = imagecolorallocate($image, 248, 250, 252);      // Light gray
$textColor = imagecolorallocate($image, 30, 41, 59);       // Dark slate
$lineColor = imagecolorallocate($image, 203, 213, 225);    // Border gray

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Add noise lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand(0, $height), $width, rand(0, $height), $lineColor);
}

// Add noise pixels
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $lineColor);
}

// Draw text with spacing
$fontSize = 5;
$x = 25;
$y = 12;

for ($i = 0; $i < strlen($code); $i++) {
    $char = strtoupper($code[$i]);
    $xPos = $x + ($i * 22) + rand(-2, 2);
    $yPos = $y + rand(-3, 3);
    imagestring($image, $fontSize, $xPos, $yPos, $char, $textColor);
}

// Send headers
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Mon, 01 Jan 1990 00:00:00 GMT');

// Output image and clean up
imagepng($image);
imagedestroy($image);
exit;