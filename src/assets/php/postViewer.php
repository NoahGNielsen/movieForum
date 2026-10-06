<?php
require_once __DIR__ . '/userCookieHandeling.php';

mysqli_report(MYSQLI_REPORT_OFF);

$newComment = require __DIR__ . '/newComment.php';

// Only the newest comments are shown, newest first; "Vis flere kommentarer" loads the next page of this size
// from posts/newComments.php. Ordered by commentId, which is also the cursor that endpoint pages by.
const COMMENT_DISPLAY_LIMIT = 30;

function loadPostViewer(): array
{
	$postIdInput = $_GET['postId'] ?? '';
	if (!is_string($postIdInput) || !ctype_digit($postIdInput) || (int) $postIdInput <= 0) {
		return ['error' => 'not_found'];
	}
	$postId = (int) $postIdInput;

	$dbConfig = loadDbConfigIfNeeded() ? ($GLOBALS['db_config'] ?? null) : null;
	if (!is_array($dbConfig)) {
		return ['error' => 'database'];
	}

	$conn = new mysqli(
		$dbConfig['servername'],
		$dbConfig['username'],
		$dbConfig['password'],
		$dbConfig['dbname']
	);

	if ($conn->connect_error) {
		return ['error' => 'database'];
	}

	$conn->set_charset('utf8mb4');
	$findPost = $conn->prepare(
		'SELECT p.postId, COALESCE(u.userName, \'Ukendt bruger\'), p.postTitle, p.postContent, p.timeStamp, c.channelName
		 FROM Posts AS p
		 LEFT JOIN Users AS u ON u.userId = p.userId
		 LEFT JOIN Channels AS c ON c.channelId = p.channelId
		 WHERE p.postId = ?
		 LIMIT 1'
	);

	if ($findPost === false) {
		$conn->close();
		return ['error' => 'database'];
	}

	$findPost->bind_param('i', $postId);
	$findPost->execute();
	$findPost->bind_result($resolvedPostId, $userName, $postTitle, $postContent, $postTimestamp, $categoryName);
	$postFound = $findPost->fetch();
	$findPost->close();

	if (!$postFound) {
		$conn->close();
		return ['error' => 'not_found'];
	}

	// The heading shows the total, even though only the newest COMMENT_DISPLAY_LIMIT are listed.
	$totalComments = 0;
	$countComments = $conn->prepare('SELECT COUNT(*) FROM Comments WHERE ownerPostId = ?');
	if ($countComments !== false) {
		$countComments->bind_param('i', $postId);
		$countComments->execute();
		$countComments->bind_result($totalComments);
		$countComments->fetch();
		$countComments->close();
	}

	$comments = [];
	$commentLimit = COMMENT_DISPLAY_LIMIT;
	$findComments = $conn->prepare(
		'SELECT cm.commentId, COALESCE(u.userName, \'Ukendt bruger\'), cm.messageContent, cm.timeStamp
		 FROM Comments AS cm
		 LEFT JOIN Users AS u ON u.userId = cm.userId
		 WHERE cm.ownerPostId = ?
		 ORDER BY cm.commentId DESC
		 LIMIT ?'
	);

	if ($findComments !== false) {
		$findComments->bind_param('ii', $postId, $commentLimit);
		$findComments->execute();
		$findComments->bind_result($commentId, $commentUserName, $commentContent, $commentTimestamp);
		while ($findComments->fetch()) {
			$comments[] = [
				'id' => $commentId,
				'username' => $commentUserName,
				'content' => $commentContent,
				'timestamp' => $commentTimestamp,
			];
		}
		$findComments->close();
	}

	$conn->close();

	return [
		'post' => [
			'id' => $resolvedPostId,
			'title' => trim((string) $postTitle),
			'body' => trim((string) $postContent),
			'username' => $userName,
			'timestamp' => $postTimestamp,
			'category' => $categoryName,
		],
		'comments' => $comments,
		'totalComments' => max((int) $totalComments, count($comments)),
	];
}

$postViewer = loadPostViewer();
$post = $postViewer['post'] ?? null;
$postError = $postViewer['error'] ?? '';
$comments = $postViewer['comments'] ?? [];
$totalComments = $postViewer['totalComments'] ?? 0;
// Starting point for commentPoller.js, which asks for comments newer than this.
$lastCommentId = empty($comments) ? 0 : max(array_column($comments, 'id'));
$commentValues = $newComment['values'];
$commentLimits = $newComment['limits'];

if ($post === null) {
	http_response_code($postError === 'database' ? 500 : 404);
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatTime = static function ($timestamp): string {
	$time = strtotime((string) $timestamp);
	return $time === false ? (string) $timestamp : date('d.m.Y \k\l. H:i', $time);
};

$pageTitle = $post ? $post['title'] : 'Indlægget blev ikke fundet';
$pageDescription = $post ? ($post['body'] !== '' ? $post['body'] : $post['title']) : 'Indlægget kunne ikke findes.';
$pageDescription = function_exists('mb_substr') ? mb_substr($pageDescription, 0, 155, 'UTF-8') : substr($pageDescription, 0, 155);
?>
