<?php
require_once __DIR__ . '/userCookieHandeling.php';
require_once __DIR__ . '/postImages.php';
mysqli_report(MYSQLI_REPORT_OFF);

$titleMinLength = 3;
$titleMaxLength = 60; // Posts.postTitle is VARCHAR(60)
$contentMinLength = 10;
$contentMaxLength = 5000;

$textLength = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
// Remove control characters except line breaks and tabs, and normalise line endings.
$cleanText = static function (string $value): string {
	$value = str_replace(["\r\n", "\r"], "\n", $value);
	return trim(preg_replace('/[^\P{C}\n\t]/u', '', $value) ?? '');
};

$userId = $_COOKIE['user_session_cookie'] ?? '';
$isRegistered = is_string($userId)
	&& preg_match('/\A[A-Za-z]{8}_[0-9]{3}_[0-9]{5}\z/', $userId)
	&& isUserSessionCookieInUse($userId);

$state = [
	'category' => null,
	'error' => null,
	'errors' => [],
	'values' => ['title' => '', 'content' => ''],
	'is_registered' => $isRegistered,
	'limits' => [
		'title_min' => $titleMinLength,
		'title_max' => $titleMaxLength,
		'content_min' => $contentMinLength,
		'content_max' => $contentMaxLength,
		'images_max' => POST_IMAGE_MAX_COUNT,
		'image_bytes_max' => PROFILE_PICTURE_MAX_BYTES,
	],
];

$categoryName = $_GET['categoryName'] ?? '';
if (!is_string($categoryName) || trim($categoryName) === '') {
	$state['error'] = 'not_found';
	return $state;
}

if (!loadDbConfigIfNeeded()) {
	$state['error'] = 'database';
	return $state;
}

$dbConfig = $GLOBALS['db_config'] ?? null;
if (!is_array($dbConfig)) {
	$state['error'] = 'database';
	return $state;
}

$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	$state['error'] = 'database';
	return $state;
}

$conn->set_charset('utf8mb4');
$findCategory = $conn->prepare('SELECT channelId, channelName FROM Channels WHERE channelName = ? LIMIT 1');
if ($findCategory === false) {
	$conn->close();
	$state['error'] = 'database';
	return $state;
}

$categoryName = trim($categoryName);
$findCategory->bind_param('s', $categoryName);
$findCategory->execute();
$findCategory->bind_result($channelId, $resolvedCategoryName);
$categoryFound = $findCategory->fetch();
$findCategory->close();

if (!$categoryFound) {
	$conn->close();
	$state['error'] = 'not_found';
	return $state;
}

$state['category'] = [
	'id' => $channelId,
	'name' => $resolvedCategoryName,
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	$conn->close();
	return $state;
}

if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
	// Above post_max_size PHP drops the whole request body, the title and CSRF token included.
	$conn->close();
	http_response_code(413);
	$state['errors'][] = 'Billederne fylder for meget. Du kan vedhæfte højst ' . POST_IMAGE_MAX_COUNT . ' billeder på hver højst 4 MB.';
	return $state;
}

$titleInput = $_POST['newPostTitle'] ?? '';
$contentInput = $_POST['newPostContent'] ?? '';
$title = is_string($titleInput) ? $cleanText(str_replace(["\n", "\t"], ' ', $titleInput)) : '';
$content = is_string($contentInput) ? $cleanText($contentInput) : '';
$state['values'] = ['title' => $title, 'content' => $content];

if (!isValidCsrfRequest()) {
	$state['errors'][] = 'Formularen er udløbet. Prøv at sende indlægget igen.';
}

if (!$isRegistered) {
	$state['errors'][] = 'Du skal have en registreret bruger for at oprette et indlæg.';
}

if ($textLength($title) < $titleMinLength || $textLength($title) > $titleMaxLength) {
	$state['errors'][] = "Titlen skal være mellem {$titleMinLength} og {$titleMaxLength} tegn.";
}

if ($textLength($content) < $contentMinLength || $textLength($content) > $contentMaxLength) {
	$state['errors'][] = "Indholdet skal være mellem {$contentMinLength} og {$contentMaxLength} tegn.";
}

if (($_POST['acceptTerms'] ?? '') !== '1') {
	$state['errors'][] = 'Du skal acceptere erklæringen for at oprette et indlæg.';
}

// Decoding images is heavy, so it is skipped when the request could not create a post anyway.
$images = [];
if (empty($state['errors'])) {
	$postImages = postImagesFromUploads($_FILES['newPostImages'] ?? null);
	$images = $postImages['images'];
	array_push($state['errors'], ...$postImages['errors']);
}

if (!empty($state['errors'])) {
	$conn->close();
	http_response_code(400);
	return $state;
}

// timeStamp is filled in by the database (DEFAULT CURRENT_TIMESTAMP).
$createPost = $conn->prepare('INSERT INTO Posts (userId, channelId, postTitle, postContent) VALUES (?, ?, ?, ?)');
if ($createPost === false) {
	$conn->close();
	http_response_code(500);
	$state['errors'][] = 'Indlægget kunne ikke oprettes. Prøv igen senere.';
	return $state;
}

// The post and its images are saved together, so a failed image doesn't leave a post without it.
$conn->begin_transaction();
$createPost->bind_param('siss', $userId, $channelId, $title, $content);
$created = $createPost->execute();
$newPostId = (int) $conn->insert_id;
$createPost->close();

$created = $created && $newPostId > 0 && savePostImages($conn, $userId, $newPostId, $images);
if ($created) {
	$conn->commit();
} else {
	$conn->rollback();
}
$conn->close();

if (!$created) {
	http_response_code(500);
	$state['errors'][] = 'Indlægget kunne ikke oprettes. Prøv igen senere.';
	return $state;
}

header('Location: /posts/' . (int) $newPostId, true, 303);
exit;
?>
