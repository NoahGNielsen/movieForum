<?php
// Function to generate a random string of specified length using given characters
function generateRandomString($length, $characters) {
    $charLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, $charLength - 1)];
    }
    return $randomString;
}

// Function to create the custom format: AAAAAAAA_000_00000
function generateCustomCookieValue() {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    
    $part1 = generateRandomString(8, $letters); // 8 letters
    $part2 = generateRandomString(3, $numbers); // 3 numbers
    $part3 = generateRandomString(5, $numbers); // 5 numbers
    
    return "{$part1}_{$part2}_{$part3}";
}

$cookieName = 'user_session_cookie';
$cookieValue = '';
$isNewCookie = false;

// Check if the cookie already exists
if (isset($_COOKIE[$cookieName])) {
    $cookieValue = $_COOKIE[$cookieName];
    echo "Existing cookie found: " . $cookieValue;
} else {
    // Generate a new cookie value if it doesn't exist
    $cookieValue = generateCustomCookieValue();
    
    // Set cookie to expire in 12 hours
    $expireTime = time() + (12 * 60 * 60);
    
    // Set the cookie securely using PHP 7.3+ options array
    setcookie($cookieName, $cookieValue, [
        'expires'  => $expireTime,
        'path'     => '/',
        'secure'   => true,  // True ensures it's only sent over HTTPS (set to false if testing strictly on local http://localhost)
        'httponly' => true,  // Prevents JavaScript access (mitigates XSS)
        'samesite' => 'Lax'  // Protects against CSRF
    ]);
    
    $isNewCookie = true;
}
?>
