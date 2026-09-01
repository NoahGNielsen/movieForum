<?php
require_once 'userCookieHandeling.php';
require '../../../config.php';

// Accessing via the $GLOBALS superglobal
$servername = $GLOBALS['db_config']['servername'];
$username = $GLOBALS['db_config']['username'];
$password = $GLOBALS['db_config']['password'];
$dbname = $GLOBALS['db_config']['dbname'];

// Create connection using the variables
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
	http_response_code(500);
	exit('Database connection failed.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['username'])) {
	http_response_code(400);
	exit('Invalid request.');
}

$requestedUsername = trim($_POST['username']);
$userId = $_COOKIE['user_session_cookie'] ?? '';

if (!preg_match('/^[A-Za-z]+$/', $requestedUsername)) {
	http_response_code(400);
	exit('Username may contain letters only.');
}

$rememberUser = isset($_POST['remember']) && $_POST['remember'] === 'on';
$cookieLifetime = $rememberUser ? 365 * 24 * 60 * 60 : 12 * 60 * 60;

if (!preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $userId)) {
	$userId = generateCustomCookieValue();
}

setUserSessionCookie($userId, $cookieLifetime);

$userName = $requestedUsername . '#' . substr($userId, -5);
$lastSeen = date('Y-m-d H:i:s');

$checkUser = $conn->prepare('SELECT userId FROM Users WHERE userId = ?');
$checkUser->bind_param('s', $userId);
$checkUser->execute();
$checkUser->store_result();

if ($checkUser->num_rows === 0) {
	$createUser = $conn->prepare('INSERT INTO Users (userId, userName, lastSeen) VALUES (?, ?, ?)');
	$createUser->bind_param('sss', $userId, $userName, $lastSeen);
	$createUser->execute();
	$createUser->close();
} else {
	$checkUser->close();
	$conn->close();
	header('Content-Type: text/html; charset=UTF-8');
	http_response_code(409);
	$safeUsername = htmlspecialchars($requestedUsername, ENT_QUOTES, 'UTF-8');
	exit("<p>En bruger må ikke oprette flere brugernavne på samme tid. Navnet <strong>{$safeUsername}</strong> blev ikke oprette.</p><p>Du kan <a href=\"https://forum.noahgajnielsen.dk/userMgmt/changeUsername\">ændre dit brugernavn her</a>.</p>");
}

$checkUser->close();
$conn->close();

header('Location: ../../userMgmt/onboarding.php');
exit;


?>