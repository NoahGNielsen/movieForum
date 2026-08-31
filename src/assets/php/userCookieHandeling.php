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

function setUserSessionCookie($cookieValue, $seconds) {
    $cookieName = 'user_session_cookie';
    $expireTime = time() + $seconds;
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    setcookie($cookieName, $cookieValue, [
        'expires'  => $expireTime,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    $_COOKIE[$cookieName] = $cookieValue;
    return $cookieValue;
}

function updateUserSessionCookieToOneYear() {
    $cookieName = 'user_session_cookie';
    $cookieValue = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : generateCustomCookieValue();

    return setUserSessionCookie($cookieValue, 365 * 24 * 60 * 60);
}

$cookieName = 'user_session_cookie';
$cookieValue = '';
$isNewCookie = false;

if (isset($_COOKIE[$cookieName])) {
    $cookieValue = $_COOKIE[$cookieName];
} else {
    $cookieValue = generateCustomCookieValue();
    setUserSessionCookie($cookieValue, 12 * 60 * 60);
    $isNewCookie = true;
}
?>
