<?php
require_once __DIR__ . '/userCookieHandeling.php';

$respondWithError = static function (int $statusCode, string $message): void {
	header('Content-Type: text/html; charset=UTF-8');
	http_response_code($statusCode);
	$safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
	exit('<!doctype html><html lang="da"><meta charset="UTF-8"><title>Kunne ikke oprette kategori</title><body><p>' . $safeMessage . '</p><p><a href="../../categories/newCategory.php">Tilbage til kategorioprettelse</a></p></body></html>');
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$respondWithError(405, 'Ugyldig forespørgsel.');
}

if (!isValidCsrfRequest()) {
	$respondWithError(403, 'Formularen er udløbet. Gå tilbage, genindlæs siden og prøv igen.');
}

$channelNameInput = $_POST['newCategoriName'] ?? null;
$channelDescriptionInput = $_POST['newCategoriDescription'] ?? null;
$ownerChannelIdInput = $_POST['newCategoriFormListSelect'] ?? null;
if (!is_string($channelNameInput) || !is_string($channelDescriptionInput) || !is_string($ownerChannelIdInput)) {
	$respondWithError(400, 'Udfyld alle felter korrekt.');
}

$channelName = trim($channelNameInput);
$channelDescription = trim($channelDescriptionInput);
$ownerChannelId = filter_var(
	$ownerChannelIdInput,
	FILTER_VALIDATE_INT,
	['options' => ['min_range' => 1]]
);
$acceptedTerms = ($_POST['acceptTerms'] ?? '') === '1';
// Names end up in the URL path, so slashes are not allowed there.
$allowedNamePattern = '/\A[\p{L}\p{N} .,_@:!?()+&\'"#%*=-]+\z/u';
$allowedDescriptionPattern = '/\A[\p{L}\p{N} .,_@:!?()+&\'"#%*=\/;€$-]+\z/u';

if (
	!preg_match($allowedNamePattern, $channelName) || mb_strlen($channelName, 'UTF-8') < 3 || mb_strlen($channelName, 'UTF-8') > 35 ||
	!preg_match($allowedDescriptionPattern, $channelDescription) || mb_strlen($channelDescription, 'UTF-8') < 10 || mb_strlen($channelDescription, 'UTF-8') > 254 ||
	$ownerChannelId === false
) {
	$respondWithError(400, 'Kontrollér navn, beskrivelse og valgt overkategori.');
}

if (!$acceptedTerms) {
	$respondWithError(400, 'Du skal acceptere erklæringen for at oprette en kategori.');
}

$userId = $_COOKIE['user_session_cookie'] ?? '';
if (!is_string($userId) || !preg_match('/\A[A-Za-z]{8}_[0-9]{3}_[0-9]{5}\z/', $userId) || !isUserSessionCookieInUse($userId)) {
	$respondWithError(403, 'Du skal have en registreret bruger for at oprette en kategori.');
}

if (!loadDbConfigIfNeeded()) {
	$respondWithError(500, 'Databasekonfigurationen kunne ikke indlæses.');
}

$dbConfig = $GLOBALS['db_config'] ?? null;
if (!is_array($dbConfig)) {
	$respondWithError(500, 'Databasekonfigurationen kunne ikke indlæses.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	$respondWithError(500, 'Forbindelsen til databasen mislykkedes.');
}

$conn->set_charset('utf8mb4');

$parentCheck = $conn->prepare('SELECT channelId FROM Channels WHERE channelId = ? AND isTopChannel = 1 LIMIT 1');
if ($parentCheck === false) {
	$conn->close();
	$respondWithError(500, 'Den valgte overkategori kunne ikke kontrolleres.');
}

$parentCheck->bind_param('i', $ownerChannelId);
$parentCheck->execute();
$parentCheck->store_result();
$parentExists = $parentCheck->num_rows === 1;
$parentCheck->close();

if (!$parentExists) {
	$conn->close();
	$respondWithError(400, 'Den valgte overkategori findes ikke.');
}

$duplicateCheck = $conn->prepare('SELECT channelId FROM Channels WHERE ownerChannelId = ? AND LOWER(channelName) = LOWER(?) LIMIT 1');
if ($duplicateCheck === false) {
	$conn->close();
	$respondWithError(500, 'Kategorien kunne ikke kontrolleres for dubletter.');
}

$duplicateCheck->bind_param('is', $ownerChannelId, $channelName);
$duplicateCheck->execute();
$duplicateCheck->store_result();
$duplicateExists = $duplicateCheck->num_rows > 0;
$duplicateCheck->close();

if ($duplicateExists) {
	$conn->close();
	$respondWithError(409, 'Der findes allerede en kategori med dette navn under den valgte overkategori.');
}

$createChannel = $conn->prepare(
	'INSERT INTO Channels (isTopChannel, ownerChannelId, channelName, channelDescription, channelCreator)
	 VALUES (0, ?, ?, ?, ?)'
);

if ($createChannel === false) {
	$conn->close();
	$respondWithError(500, 'Kategorien kunne ikke oprettes. Kontrollér også, at channelCreator er en tekstkolonne.');
}

$createChannel->bind_param('isss', $ownerChannelId, $channelName, $channelDescription, $userId);
$created = $createChannel->execute();
$createChannel->close();
$conn->close();

if (!$created) {
	$respondWithError(500, 'Kategorien kunne ikke oprettes. Kontrollér også, at channelCreator er en tekstkolonne.');
}

header('Location: /categories/' . rawurlencode($channelName));
exit;
?>
