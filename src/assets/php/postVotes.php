<?php
// Votes on posts are stored in Interactions, one row per user per post (UNIQUE postId + userId).
// interactionType: 1 = upvote, 2 = downvote.
const POST_VOTE_UP = 1;
const POST_VOTE_DOWN = 2;

// Returns the vote totals for a post and how the given user voted on it (0 = no vote).
function loadPostVotes(mysqli $conn, int $postId, string $userId): array
{
	$votes = ['upvotes' => 0, 'downvotes' => 0, 'score' => 0, 'userVote' => 0];

	$countVotes = $conn->prepare(
		'SELECT COALESCE(SUM(interactionType = ' . POST_VOTE_UP . '), 0),
		        COALESCE(SUM(interactionType = ' . POST_VOTE_DOWN . '), 0),
		        COALESCE(MAX(CASE WHEN userId = ? THEN interactionType END), 0)
		 FROM Interactions
		 WHERE postId = ?'
	);

	if ($countVotes === false) {
		return $votes;
	}

	$countVotes->bind_param('si', $userId, $postId);
	$countVotes->execute();
	$countVotes->bind_result($upvotes, $downvotes, $userVote);
	$countVotes->fetch();
	$countVotes->close();

	$votes['upvotes'] = (int) $upvotes;
	$votes['downvotes'] = (int) $downvotes;
	$votes['score'] = $votes['upvotes'] - $votes['downvotes'];
	$votes['userVote'] = (int) $userVote;

	return $votes;
}
?>
