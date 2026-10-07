<?php
require_once __DIR__ . '/../assets/php/viewHelpers.php';

mysqli_report(MYSQLI_REPORT_OFF);

// Used by commentPoller.js in two ways:
// - ?after=<id>  polling: comments newer than the given commentId, oldest first.
// - ?before=<id> "Vis flere kommentarer": the next page of comments older than the given commentId, newest first.
$commentBatchLimit = 50;
$olderPageSize = 30; // Matches COMMENT_DISPLAY_LIMIT in postViewer.php.

$sendJson = static function (int $status, array $body): void {
	http_response_code($status);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
};

$postIdInput = $_GET['postId'] ?? '';
$afterInput = $_GET['after'] ?? '0';
$beforeInput = $_GET['before'] ?? null;
if (!is_string($postIdInput) || !ctype_digit($postIdInput) || (int) $postIdInput <= 0
	|| !is_string($afterInput) || !ctype_digit($afterInput)
	|| ($beforeInput !== null && (!is_string($beforeInput) || !ctype_digit($beforeInput)))) {
	$sendJson(400, ['error' => 'invalid_request']);
}
$postId = (int) $postIdInput;
$afterCommentId = (int) $afterInput;
$loadOlder = $beforeInput !== null;
$beforeCommentId = $loadOlder ? (int) $beforeInput : 0;

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

// commentId is auto-increment, so "newer than the last comment on the page" is simply commentId > after,
// and "older than the oldest comment on the page" is commentId < before.
if ($loadOlder) {
	// One extra row tells us whether there is yet another page after this one.
	$fetchLimit = $olderPageSize + 1;
	$findComments = $conn->prepare(
		'SELECT cm.commentId, COALESCE(u.userName, \'Ukendt bruger\'), ' . profilePictureIdSql('cm.userId') . ', cm.messageContent, cm.timeStamp
		 FROM Comments AS cm
		 LEFT JOIN Users AS u ON u.userId = cm.userId
		 WHERE cm.ownerPostId = ? AND cm.commentId < ?
		 ORDER BY cm.commentId DESC
		 LIMIT ?'
	);
	$cursorCommentId = $beforeCommentId;
} else {
	$fetchLimit = $commentBatchLimit;
	$findComments = $conn->prepare(
		'SELECT cm.commentId, COALESCE(u.userName, \'Ukendt bruger\'), ' . profilePictureIdSql('cm.userId') . ', cm.messageContent, cm.timeStamp
		 FROM Comments AS cm
		 LEFT JOIN Users AS u ON u.userId = cm.userId
		 WHERE cm.ownerPostId = ? AND cm.commentId > ?
		 ORDER BY cm.commentId ASC
		 LIMIT ?'
	);
	$cursorCommentId = $afterCommentId;
}

if ($findComments === false) {
	$conn->close();
	$sendJson(500, ['error' => 'database']);
}

$findComments->bind_param('iii', $postId, $cursorCommentId, $fetchLimit);
$findComments->execute();
$findComments->bind_result($commentId, $commentUserName, $commentAvatarId, $commentContent, $commentTimestamp);

$comments = [];
while ($findComments->fetch()) {
	// Pre-formatted with the same helpers post.php uses, so cards added by commentPoller.js match.
	$nameParts = viewSplitUserName($commentUserName);
	$comments[] = [
		'id' => $commentId,
		'username' => $commentUserName,
		'nameBase' => $nameParts['name'],
		'nameTag' => $nameParts['tag'],
		'initial' => viewInitial($commentUserName),
		'avatarUrl' => $commentAvatarId !== null ? profilePictureUrl((int) $commentAvatarId) : '',
		'content' => $commentContent,
		'displayContent' => viewBreakLongWords($commentContent),
		'timestamp' => $commentTimestamp,
		'displayTime' => viewRelativeTime($commentTimestamp),
		'exactTime' => viewExactTime($commentTimestamp),
	];
}
$findComments->close();
$conn->close();

if ($loadOlder) {
	$hasMore = count($comments) > $olderPageSize;
	$sendJson(200, ['comments' => array_slice($comments, 0, $olderPageSize), 'hasMore' => $hasMore]);
}

$sendJson(200, ['comments' => $comments]);
