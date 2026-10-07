<?php
// Serves a profile picture by its public attachmentId. Deliberately skips userCookieHandeling.php:
// image requests should not set cookies or update lastSeen.
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../assets/php/profilePictures.php';

mysqli_report(MYSQLI_REPORT_OFF);

$notFound = static function (): void {
	http_response_code(404);
	header('Cache-Control: no-store');
	exit;
};

$idInput = $_GET['id'] ?? '';
if (!is_string($idInput) || !ctype_digit($idInput) || (int) $idInput <= 0) {
	$notFound();
}
$attachmentId = (int) $idInput;

$dbConfig = $GLOBALS['db_config'] ?? null;
if (!is_array($dbConfig)) {
	$notFound();
}

$conn = new mysqli($dbConfig['servername'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
if ($conn->connect_error) {
	$notFound();
}

$findPicture = $conn->prepare('SELECT attachmentFile FROM ' . PROFILE_PICTURE_TABLE . ' WHERE attachmentId = ? AND attachmentType = ? LIMIT 1');
if ($findPicture === false) {
	$conn->close();
	$notFound();
}

// get_result() rather than bind_result(): binding a LONGBLOB can make the client reserve its 4 GB maximum size.
$type = PROFILE_PICTURE_TYPE;
$findPicture->bind_param('is', $attachmentId, $type);
$findPicture->execute();
$result = $findPicture->get_result();
$row = $result !== false ? $result->fetch_row() : null;
$findPicture->close();
$conn->close();

if (!is_array($row) || !is_string($row[0]) || $row[0] === '') {
	$notFound();
}

// Each upload gets a new id, so the content behind a URL never changes (see also .htaccess).
header('Content-Type: image/webp');
header('Content-Length: ' . strlen($row[0]));
header('Cache-Control: public, max-age=31536000, immutable');
echo $row[0];
