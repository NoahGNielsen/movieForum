<?php
$configPath = __DIR__ . '/../../../config.php';

if (!is_file($configPath)) {
	return;
}

$config = require_once $configPath;
$dbConfig = $GLOBALS['db_config'] ?? ($db_config ?? $config ?? null);

if (!is_array($dbConfig)) {
	return;
}

$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	return;
}

$timestampColumn = null;
$columnsResult = $conn->query('SHOW COLUMNS FROM Posts');
$timestampCandidates = ['createdAt', 'created_at', 'postDate', 'post_date', 'datePosted', 'postTime', 'created'];

if ($columnsResult !== false) {
	$columns = [];
	while ($column = $columnsResult->fetch_assoc()) {
		$columns[] = $column;
	}
	$columnsResult->free();

	foreach ($timestampCandidates as $candidate) {
		foreach ($columns as $column) {
			if ($column['Field'] === $candidate) {
				$timestampColumn = $candidate;
				break 2;
			}
		}
	}

	if ($timestampColumn === null) {
		foreach ($columns as $column) {
			if (preg_match('/^(date|datetime|timestamp|time)/i', (string) $column['Type'])) {
				$timestampColumn = $column['Field'];
				break;
			}
		}
	}
}

$latestPostExpression = $timestampColumn !== null
	? 'MAX(p.`' . str_replace('`', '``', $timestampColumn) . '`)' 
	: 'NULL';

$commentPostColumn = null;
$commentColumnsResult = $conn->query('SHOW COLUMNS FROM Comments');
$commentPostCandidates = ['postId', 'post_id', 'postID', 'postReference', 'post_id_fk'];

if ($commentColumnsResult !== false) {
	$commentColumns = [];
	while ($column = $commentColumnsResult->fetch_assoc()) {
		$commentColumns[] = $column;
	}
	$commentColumnsResult->free();

	foreach ($commentPostCandidates as $candidate) {
		foreach ($commentColumns as $column) {
			if ($column['Field'] === $candidate) {
				$commentPostColumn = $candidate;
				break 2;
			}
		}
	}

	if ($commentPostColumn === null) {
		foreach ($commentColumns as $column) {
			if (preg_match('/post.*(id|reference)/i', (string) $column['Field'])) {
				$commentPostColumn = $column['Field'];
				break;
			}
		}
	}
}

$commentJoin = $commentPostColumn !== null
	? 'comment.`' . str_replace('`', '``', $commentPostColumn) . '` = p.postId'
	: '1 = 0';

$result = $conn->query(
	'SELECT c.channelId, c.channelName, c.channelDescription,
			COUNT(DISTINCT p.postId) AS postCount,
			' . $latestPostExpression . ' AS latestPost,
			COUNT(comment.commentId) AS commentCount
	 FROM Channels c
	 LEFT JOIN Posts p ON p.channelId = c.channelId
	 LEFT JOIN Comments comment ON ' . $commentJoin . '
	 WHERE c.isTopChannel = 1
	 GROUP BY c.channelId, c.channelName, c.channelDescription
	 ORDER BY c.channelName'
);

if ($result !== false) {
	while ($category = $result->fetch_assoc()) {
		$categoryName = (string) $category['channelName'];
		$safeCategoryName = htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8');
		$categoryDescription = $category['channelDescription'] ?? 'Ingen beskrivelse tilgængelig.';
		$safeCategoryDescription = htmlspecialchars((string) $categoryDescription, ENT_QUOTES, 'UTF-8');
		$categoryUrl = rawurlencode($categoryName);
		$postCount = (int) $category['postCount'];
		$commentCount = (int) $category['commentCount'];
		$latestPost = $category['latestPost'];
		$safeLatestPost = $latestPost !== null
			? htmlspecialchars((string) $latestPost, ENT_QUOTES, 'UTF-8')
			: '';
		$latestPostLabel = $latestPost !== null ? (string) $latestPost : 'Ikke tilgængelig';
		$safeLatestPostLabel = htmlspecialchars($latestPostLabel, ENT_QUOTES, 'UTF-8');

		echo '<article class="category">';
		echo '<h2 class="category-title">' . $safeCategoryName . '</h2>';
		echo '<div class="category-stats">';
		echo '<span class="category-posts"><span class="category-icon" aria-hidden="true">▤</span> ' . $postCount . ' posts</span>';
		echo '<span class="category-comments"><span class="category-icon" aria-hidden="true">◌</span> ' . $commentCount . ' comments</span>';
		echo '<time class="category-time" datetime="' . $safeLatestPost . '"><span class="category-icon" aria-hidden="true">◷</span> Sidste post: ' . $safeLatestPostLabel . '</time>';
		echo '</div>';
		echo '<p class="category-description">' . $safeCategoryDescription . '</p>';
		echo '<a href="https://forum.noahgajnielsen.dk/categories/' . $categoryUrl . '" class="category-link">Gå til ' . $safeCategoryName . '</a>';
		echo '</article>';
	}

	$result->free();
}

$conn->close();
?>
