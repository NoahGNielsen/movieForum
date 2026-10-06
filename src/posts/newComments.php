<?php
require_once __DIR__ . '/../assets/php/userCookieHandeling.php';

mysqli_report(MYSQLI_REPORT_OFF);

// Polled by commentPoller.js: returns the comments on a post that are newer than the given commentId.
$commentBatchLimit = 50;

$sendJson = static function (int $status, array $body): void {
	http_response_code($status);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
};

$postIdInput = $_GET['postId'] ?? '';
$afterInput = $_GET['after'] ?? '0';
if (!is_string($postIdInput) || !ctype_digit($postIdInput) || (int) $postIdInput <= 0
	|| !is_string($afterInput) || !ctype_digit($afterInput)) {
	$sendJson(400, ['error' => 'invalid_request']);
}
$postId = (int) $postIdInput;
$afterCommentId = (int) $afterInput;

$dbConfig = loadDbConfigIfNeeded() ? ($GLOBALS['db_config'] ?? null) : null;
if (!is_array($dbConfig)) {
	$sendJson(500, ['error' => 'database']);
}

$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	$sendJson(500, ['error' => 'database']);
}

$conn->set_charset('utf8mb4');

// commentId is auto-increment, so "newer than the last comment on the page" is simply commentId > after.
$findComments = $conn->prepare(
	'SELECT cm.commentId, COALESCE(u.userName, \'Ukendt bruger\'), cm.messageContent, cm.timeStamp
	 FROM Comments AS cm
	 LEFT JOIN Users AS u ON u.userId = cm.userId
	 WHERE cm.ownerPostId = ? AND cm.commentId > ?
	 ORDER BY cm.commentId ASC
	 LIMIT ?'
);

if ($findComments === false) {
	$conn->close();
	$sendJson(500, ['error' => 'database']);
}

$findComments->bind_param('iii', $postId, $afterCommentId, $commentBatchLimit);
$findComments->execute();
$findComments->bind_result($commentId, $commentUserName, $commentContent, $commentTimestamp);

$comments = [];
while ($findComments->fetch()) {
	$time = strtotime((string) $commentTimestamp);
	$comments[] = [
		'id' => $commentId,
		'username' => $commentUserName,
		'content' => $commentContent,
		'timestamp' => $commentTimestamp,
		// Same format as $formatTime in postViewer.php.
		'displayTime' => $time === false ? (string) $commentTimestamp : date('d.m.Y \k\l. H:i', $time),
	];
}
$findComments->close();
$conn->close();

$sendJson(200, ['comments' => $comments]);
