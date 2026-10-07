<?php
require_once __DIR__ . '/viewHelpers.php';

// Profilen viser kun den besøgendes egen bruger. Andre brugeres profiler kræver et offentligt
// bruger-id i URL'en; userId kan ikke bruges, fordi det er selve login-cookien.
function loadOwnProfile(array $user, int $limit = 10): ?array
{
	$conn = viewDbConnect();
	if ($conn === null) {
		return null;
	}

	$counts = ['posts' => 0, 'comments' => 0];
	foreach (['posts' => 'SELECT COUNT(*) FROM Posts WHERE userId = ?', 'comments' => 'SELECT COUNT(*) FROM Comments WHERE userId = ?'] as $key => $sql) {
		$countQuery = $conn->prepare($sql);
		if ($countQuery !== false) {
			$countQuery->bind_param('s', $user['id']);
			$countQuery->execute();
			$countQuery->bind_result($count);
			$countQuery->fetch();
			$counts[$key] = (int) $count;
			$countQuery->close();
		}
	}

	// Seneste opslag og kommentarer i én liste, nyeste først
	$activity = [];
	$findActivity = $conn->prepare(
		'(SELECT \'post\' AS kind, p.postId, p.postTitle, p.postContent, p.timeStamp, NULL AS commentId
		  FROM Posts AS p WHERE p.userId = ?)
		 UNION ALL
		 (SELECT \'comment\', p.postId, p.postTitle, cm.messageContent, cm.timeStamp, cm.commentId
		  FROM Comments AS cm JOIN Posts AS p ON p.postId = cm.ownerPostId WHERE cm.userId = ?)
		 ORDER BY timeStamp DESC
		 LIMIT ?'
	);

	if ($findActivity !== false) {
		$findActivity->bind_param('ssi', $user['id'], $user['id'], $limit);
		$findActivity->execute();
		$findActivity->bind_result($kind, $postId, $postTitle, $content, $timestamp, $commentId);
		while ($findActivity->fetch()) {
			$activity[] = [
				'kind' => $kind,
				'post_id' => (int) $postId,
				'post_title' => $postTitle,
				'content' => $content,
				'timestamp' => $timestamp,
				'comment_id' => $commentId !== null ? (int) $commentId : null,
			];
		}
		$findActivity->close();
	}

	$conn->close();

	return ['counts' => $counts, 'activity' => $activity];
}

$profileUser = viewCurrentUser();
$profile = $profileUser !== null ? loadOwnProfile($profileUser) : null;
