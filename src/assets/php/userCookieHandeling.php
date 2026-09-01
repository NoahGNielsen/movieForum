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

function isUserSessionCookieInUse($cookieValue) {
    if (!is_string($cookieValue) || !preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $cookieValue)) {
        return true;
    }

    if (!loadDbConfigIfNeeded()) {
        return false;
    }

    $dbConfig = $GLOBALS['db_config'] ?? null;
    if (!is_array($dbConfig)) {
        return false;
    }

    $conn = new mysqli($dbConfig['servername'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if ($conn->connect_error) {
        return false;
    }

    $checkCookie = $conn->prepare('SELECT userId FROM Users WHERE userId = ? LIMIT 1');
    if ($checkCookie === false) {
        $conn->close();
        return false;
    }

    $checkCookie->bind_param('s', $cookieValue);
    $checkCookie->execute();
    $checkCookie->store_result();
    $cookieInUse = $checkCookie->num_rows > 0;
    $checkCookie->close();
    $conn->close();

    return $cookieInUse;
}

function generateUniqueUserSessionCookie() {
    do {
        $cookieValue = generateCustomCookieValue();
    } while (isUserSessionCookieInUse($cookieValue));

    return $cookieValue;
}

function updateUserSessionCookieToOneYear() {
    $cookieName = 'user_session_cookie';
    $cookieValue = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : generateUniqueUserSessionCookie();

    return setUserSessionCookie($cookieValue, 365 * 24 * 60 * 60);
}

function loadDbConfigIfNeeded() {
    if (isset($GLOBALS['db_config'])) {
        return true;
    }

    $candidatePaths = [
        __DIR__ . '/../../config.php',
        __DIR__ . '/../../../config.php',
        dirname(__DIR__, 2) . '/config.php',
        dirname(__DIR__, 3) . '/config.php',
        ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/config.php'
    ];

    foreach ($candidatePaths as $configPath) {
        if (is_string($configPath) && $configPath !== '' && is_file($configPath)) {
            require_once $configPath;
            if (isset($GLOBALS['db_config'])) {
                return true;
            }
        }
    }

    return false;
}

function updateUserLastSeenIfExists($userId) {
    if (!is_string($userId) || $userId === '' || !preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $userId)) {
        return false;
    }

    if (!loadDbConfigIfNeeded()) {
        return false;
    }

    $dbConfig = $GLOBALS['db_config'] ?? null;
    if (!is_array($dbConfig)) {
        return false;
    }

    $conn = new mysqli($dbConfig['servername'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
    if ($conn->connect_error) {
        return false;
    }

    $checkUser = $conn->prepare('SELECT userId FROM Users WHERE userId = ? LIMIT 1');
    if ($checkUser === false) {
        $conn->close();
        return false;
    }

    $checkUser->bind_param('s', $userId);
    $checkUser->execute();
    $checkUser->store_result();
    $userExists = $checkUser->num_rows > 0;
    $checkUser->close();

    if (!$userExists) {
        $conn->close();
        return false;
    }

    $lastSeen = date('Y-m-d H:i:s');
    $updateUser = $conn->prepare('UPDATE Users SET lastSeen = ? WHERE userId = ?');
    if ($updateUser !== false) {
        $updateUser->bind_param('ss', $lastSeen, $userId);
        $updateUser->execute();
        $updateUser->close();
    }

    $conn->close();
    return true;
}

$cookieName = 'user_session_cookie';
$cookieValue = '';
$isNewCookie = false;

if (isset($_COOKIE[$cookieName])) {
    $cookieValue = $_COOKIE[$cookieName];

    if (!preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $cookieValue) || !isUserSessionCookieInUse($cookieValue)) {
        $cookieValue = generateUniqueUserSessionCookie();
        setUserSessionCookie($cookieValue, 12 * 60 * 60);
        $isNewCookie = true;
    }
} else {
    $cookieValue = generateUniqueUserSessionCookie();
    setUserSessionCookie($cookieValue, 12 * 60 * 60);
    $isNewCookie = true;
}

updateUserLastSeenIfExists($cookieValue);
?>
