<?php
require_once __DIR__ . '/userCookieHandeling.php';
mysqli_report(MYSQLI_REPORT_OFF);

$commentMinLength = 2;
$commentMaxLength = 2000;

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
	'errors' => [],
	'values' => ['content' => ''],
	'is_registered' => $isRegistered,
	'limits' => [
		'content_min' => $commentMinLength,
		'content_max' => $commentMaxLength,
	],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	return $state;
}

$postIdInput = $_GET['postId'] ?? '';
if (!is_string($postIdInput) || !ctype_digit($postIdInput) || (int) $postIdInput <= 0) {
	// post.php shows the "not found" page for an invalid id.
	return $state;
}
$postId = (int) $postIdInput;

$contentInput = $_POST['newCommentContent'] ?? '';
$content = is_string($contentInput) ? $cleanText($contentInput) : '';
$state['values'] = ['content' => $content];

if (!isValidCsrfRequest()) {
	$state['errors'][] = 'Formularen er udløbet. Prøv at sende kommentaren igen.';
}

if (!$isRegistered) {
	$state['errors'][] = 'Du skal have en registreret bruger for at skrive en kommentar.';
}

if ($textLength($content) < $commentMinLength || $textLength($content) > $commentMaxLength) {
	$state['errors'][] = "Kommentaren skal være mellem {$commentMinLength} og {$commentMaxLength} tegn.";
}

if (!empty($state['errors'])) {
	http_response_code(400);
	return $state;
}

$dbConfig = loadDbConfigIfNeeded() ? ($GLOBALS['db_config'] ?? null) : null;
if (!is_array($dbConfig)) {
	http_response_code(500);
	$state['errors'][] = 'Kommentaren kunne ikke gemmes. Prøv igen senere.';
	return $state;
}

$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	http_response_code(500);
	$state['errors'][] = 'Kommentaren kunne ikke gemmes. Prøv igen senere.';
	return $state;
}

$conn->set_charset('utf8mb4');

// Make sure the post still exists before attaching a comment to it.
$findPost = $conn->prepare('SELECT postId FROM Posts WHERE postId = ? LIMIT 1');
if ($findPost === false) {
	$conn->close();
	http_response_code(500);
	$state['errors'][] = 'Kommentaren kunne ikke gemmes. Prøv igen senere.';
	return $state;
}

$findPost->bind_param('i', $postId);
$findPost->execute();
$findPost->store_result();
$postExists = $findPost->num_rows > 0;
$findPost->close();

if (!$postExists) {
	$conn->close();
	return $state;
}

// timeStamp is filled in by the database (DEFAULT CURRENT_TIMESTAMP).
$createComment = $conn->prepare('INSERT INTO Comments (userId, ownerPostId, messageContent) VALUES (?, ?, ?)');
if ($createComment === false) {
	$conn->close();
	http_response_code(500);
	$state['errors'][] = 'Kommentaren kunne ikke gemmes. Prøv igen senere.';
	return $state;
}

$createComment->bind_param('sis', $userId, $postId, $content);
$created = $createComment->execute();
$newCommentId = $conn->insert_id;
$createComment->close();
$conn->close();

if (!$created || $newCommentId <= 0) {
	http_response_code(500);
	$state['errors'][] = 'Kommentaren kunne ikke gemmes. Prøv igen senere.';
	return $state;
}

// Post/Redirect/Get so a page refresh doesn't submit the comment twice.
header('Location: /posts/' . $postId . '#comment-' . (int) $newCommentId, true, 303);
exit;
?>
