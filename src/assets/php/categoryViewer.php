<?php
mysqli_report(MYSQLI_REPORT_OFF);

$categoryName = $_GET['categoryName'] ?? '';
if (!is_string($categoryName) || trim($categoryName) === '') {
	return ['error' => 'not_found'];
}

if (!loadDbConfigIfNeeded()) {
	return ['error' => 'database'];
}

$dbConfig = $GLOBALS['db_config'] ?? null;
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
$findCategory = $conn->prepare(
	'SELECT channelId, channelName, channelDescription, isTopChannel
	 FROM Channels
	 WHERE channelName = ?
	 LIMIT 1'
);

if ($findCategory === false) {
	$conn->close();
	return ['error' => 'database'];
}

$categoryName = trim($categoryName);
$findCategory->bind_param('s', $categoryName);
$findCategory->execute();
$findCategory->bind_result($channelId, $resolvedCategoryName, $channelDescription, $isTopChannel);
$categoryFound = $findCategory->fetch();
$findCategory->close();

if (!$categoryFound) {
	$conn->close();
	return ['error' => 'not_found'];
}

$subcategories = [];
if ((int) $isTopChannel === 1) {
	$findSubcategories = $conn->prepare(
		'SELECT channelName, channelDescription
		 FROM Channels
		 WHERE ownerChannelId = ?
		 ORDER BY channelName'
	);

	if ($findSubcategories !== false) {
		$findSubcategories->bind_param('i', $channelId);
		$findSubcategories->execute();
		$findSubcategories->bind_result($subcategoryName, $subcategoryDescription);
		while ($findSubcategories->fetch()) {
			$subcategories[] = [
				'name' => $subcategoryName,
				'description' => $subcategoryDescription,
			];
		}
		$findSubcategories->close();
	}
}

$posts = [];
$findPosts = $conn->prepare(
	' SELECT p.postId, COALESCE(u.userName, \'Ukendt bruger\'), p.postContent, p.timeStamp
	 FROM Posts AS p
	 LEFT JOIN Users AS u ON u.userId = p.userId
	 WHERE p.channelId = ?
	 ORDER BY p.timeStamp DESC, p.postId DESC'
);

if ($findPosts !== false) {
	$findPosts->bind_param('i', $channelId);
	$findPosts->execute();
	$findPosts->bind_result($postId, $userName, $postContent, $postTimestamp);
	while ($findPosts->fetch()) {
		$posts[] = [
			'id' => $postId,
			'username' => $userName,
			'content' => $postContent,
			'timestamp' => $postTimestamp,
		];
	}
	$findPosts->close();
}

$conn->close();

return [
	'category' => [
		'id' => $channelId,
		'name' => $resolvedCategoryName,
		'description' => $channelDescription,
		'is_top' => (int) $isTopChannel === 1,
	],
	'subcategories' => $subcategories,
	'posts' => $posts,
];
?>
