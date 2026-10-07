<?php
require_once __DIR__ . '/../assets/php/userCookieHandeling.php';
require_once __DIR__ . '/../assets/php/postVotes.php';

mysqli_report(MYSQLI_REPORT_OFF);

// Up/down votes on a post. The vote form in post.php posts here:
// - postVotes.js sends Accept: application/json and gets the new totals back.
// - Without JavaScript the form submits normally and is redirected back to the post (Post/Redirect/Get).
// Voting the same way twice removes the vote; voting the other way switches it.
$wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
$postIdInput = $_POST['postId'] ?? '';
$postId = is_string($postIdInput) && ctype_digit($postIdInput) ? (int) $postIdInput : 0;

$respond = static function (int $status, array $body) use ($wantsJson, $postId): void {
	if ($wantsJson) {
		http_response_code($status);
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store');
		echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	} else {
		header('Location: ' . ($postId > 0 ? '/posts/' . $postId . '#postVotes' : '/categories/'), true, 303);
	}
	exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	$respond(405, ['error' => 'method_not_allowed']);
}

$voteInput = $_POST['vote'] ?? '';
$voteTypes = ['up' => POST_VOTE_UP, 'down' => POST_VOTE_DOWN];
if ($postId <= 0 || !is_string($voteInput) || !isset($voteTypes[$voteInput])) {
	$respond(400, ['error' => 'invalid_request']);
}
$voteType = $voteTypes[$voteInput];

if (!isValidCsrfRequest()) {
	$respond(403, ['error' => 'csrf']);
}

$userId = $_COOKIE['user_session_cookie'] ?? '';
$isRegistered = is_string($userId)
	&& preg_match('/\A[A-Za-z]{8}_[0-9]{3}_[0-9]{5}\z/', $userId)
	&& isUserSessionCookieInUse($userId);
if (!$isRegistered) {
	$respond(403, ['error' => 'not_registered']);
}

$dbConfig = loadDbConfigIfNeeded() ? ($GLOBALS['db_config'] ?? null) : null;
if (!is_array($dbConfig)) {
	$respond(500, ['error' => 'database']);
}

$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	$respond(500, ['error' => 'database']);
}

$conn->set_charset('utf8mb4');

$findPost = $conn->prepare('SELECT postId FROM Posts WHERE postId = ? LIMIT 1');
if ($findPost === false) {
	$conn->close();
	$respond(500, ['error' => 'database']);
}

$findPost->bind_param('i', $postId);
$findPost->execute();
$findPost->store_result();
$postExists = $findPost->num_rows > 0;
$findPost->close();

if (!$postExists) {
	$conn->close();
	$respond(404, ['error' => 'not_found']);
}

$currentVote = loadPostVotes($conn, $postId, $userId)['userVote'];

if ($currentVote === $voteType) {
	$changeVote = $conn->prepare('DELETE FROM Interactions WHERE postId = ? AND userId = ?');
	$types = 'is';
	$params = [$postId, $userId];
} elseif ($currentVote === 0) {
	$changeVote = $conn->prepare('INSERT INTO Interactions (interactionType, postId, userId) VALUES (?, ?, ?)');
	$types = 'iis';
	$params = [$voteType, $postId, $userId];
} else {
	$changeVote = $conn->prepare('UPDATE Interactions SET interactionType = ? WHERE postId = ? AND userId = ?');
	$types = 'iis';
	$params = [$voteType, $postId, $userId];
}

// If two clicks race, the UNIQUE (postId, userId) key rejects the second insert; the totals below are still correct.
$saved = false;
if ($changeVote !== false) {
	$changeVote->bind_param($types, ...$params);
	$saved = $changeVote->execute();
	$changeVote->close();
}

$votes = loadPostVotes($conn, $postId, $userId);
$conn->close();

if (!$saved) {
	$respond(500, ['error' => 'database'] + $votes);
}

$respond(200, $votes);
