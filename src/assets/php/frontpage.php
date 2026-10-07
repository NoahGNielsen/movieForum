<?php
require_once __DIR__ . '/viewHelpers.php';
require_once __DIR__ . '/postVotes.php';

// Tråde sorteret efter seneste aktivitet (nyeste kommentar, ellers selve opslaget).
// "Anbefalet" ville kræve likes eller visninger, som databasen ikke har endnu.
function loadActiveThreads(int $limit = 8): ?array
{
	$conn = viewDbConnect();
	if ($conn === null) {
		return null;
	}

	$findThreads = $conn->prepare(
		'SELECT p.postId, p.postTitle, COALESCE(ch.channelName, \'\'), COUNT(cm.commentId), ' . postVoteCountsSql('p.postId') . ',
		        GREATEST(p.timeStamp, COALESCE(MAX(cm.timeStamp), p.timeStamp)) AS lastActivity
		 FROM Posts AS p
		 LEFT JOIN Comments AS cm ON cm.ownerPostId = p.postId
		 LEFT JOIN Channels AS ch ON ch.channelId = p.channelId
		 GROUP BY p.postId, p.postTitle, ch.channelName, p.timeStamp
		 ORDER BY lastActivity DESC, p.postId DESC
		 LIMIT ?'
	);

	if ($findThreads === false) {
		$conn->close();
		return null;
	}

	$threads = [];
	$findThreads->bind_param('i', $limit);
	$findThreads->execute();
	$findThreads->bind_result($postId, $postTitle, $categoryName, $replyCount, $upvotes, $downvotes, $lastActivity);
	while ($findThreads->fetch()) {
		$threads[] = [
			'id' => $postId,
			'title' => $postTitle,
			'category' => $categoryName,
			'replies' => (int) $replyCount,
			'upvotes' => (int) $upvotes,
			'downvotes' => (int) $downvotes,
			'last_activity' => $lastActivity,
		];
	}
	$findThreads->close();
	$conn->close();

	return $threads;
}

$activeThreads = loadActiveThreads();
$frontpageUser = viewCurrentUser();
