<?php
require_once __DIR__ . '/userCookieHandeling.php';
mysqli_report(MYSQLI_REPORT_OFF);

$titleMinLength = 3;
$titleMaxLength = 120;
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

$titleInput = $_POST['newPostTitle'] ?? '';
$contentInput = $_POST['newPostContent'] ?? '';
$title = is_string($titleInput) ? $cleanText(str_replace(["\n", "\t"], ' ', $titleInput)) : '';
$content = is_string($contentInput) ? $cleanText($contentInput) : '';
$state['values'] = ['title' => $title, 'content' => $content];

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

if (!empty($state['errors'])) {
	$conn->close();
	http_response_code(400);
	return $state;
}

// The first line of postContent is used as the post title elsewhere on the site.
$postContent = $title . "\n" . $content;

// timeStamp is filled in by the database (DEFAULT CURRENT_TIMESTAMP).
$createPost = $conn->prepare('INSERT INTO Posts (userId, channelId, postContent) VALUES (?, ?, ?)');
if ($createPost === false) {
	$conn->close();
	http_response_code(500);
	$state['errors'][] = 'Indlægget kunne ikke oprettes. Prøv igen senere.';
	return $state;
}

$createPost->bind_param('sis', $userId, $channelId, $postContent);
$created = $createPost->execute();
$newPostId = $conn->insert_id;
$createPost->close();
$conn->close();

if (!$created || $newPostId <= 0) {
	http_response_code(500);
	$state['errors'][] = 'Indlægget kunne ikke oprettes. Prøv igen senere.';
	return $state;
}

header('Location: /posts/' . (int) $newPostId, true, 303);
exit;
?>
