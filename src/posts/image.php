<?php
// Serves an image attached to a post by its public attachmentId. Works like userMgmt/avatar.php and
// deliberately skips userCookieHandeling.php for the same reason: image requests should not set cookies.
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../assets/php/postImages.php';

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

$findImage = $conn->prepare('SELECT attachmentFile FROM ' . PROFILE_PICTURE_TABLE . ' WHERE attachmentId = ? AND attachmentType = ? LIMIT 1');
if ($findImage === false) {
	$conn->close();
	$notFound();
}

// get_result() rather than bind_result(): binding a LONGBLOB can make the client reserve its 4 GB maximum size.
$type = POST_IMAGE_TYPE;
$findImage->bind_param('is', $attachmentId, $type);
$findImage->execute();
$result = $findImage->get_result();
$row = $result !== false ? $result->fetch_row() : null;
$findImage->close();
$conn->close();

if (!is_array($row) || !is_string($row[0]) || $row[0] === '') {
	$notFound();
}

// Images are never edited, so the content behind a URL never changes (see also .htaccess).
header('Content-Type: image/webp');
header('Content-Length: ' . strlen($row[0]));
header('Cache-Control: public, max-age=31536000, immutable');
echo $row[0];
