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

$result = $conn->query(
	'SELECT channelName, channelDescription
	 FROM Channels
	WHERE isTopChannel = 1
	 ORDER BY channelName'
);

if ($result !== false) {
	while ($category = $result->fetch_assoc()) {
		$categoryName = (string) $category['channelName'];
		$safeCategoryName = htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8');
		$categoryDescription = $category['channelDescription'] ?? 'Ingen beskrivelse tilgængelig.';
		$safeCategoryDescription = htmlspecialchars((string) $categoryDescription, ENT_QUOTES, 'UTF-8');
		$categoryUrl = rawurlencode($categoryName);

		echo '<article class="category">';
		echo '<h2 class="category-title">' . $safeCategoryName . '</h2>';
		echo '<p class="category-description">' . $safeCategoryDescription . '</p>';
		echo '<a href="https://forum.noahgajnielsen.dk/categories/' . $categoryUrl . '" class="category-link">Gå til ' . $safeCategoryName . '</a>';
		echo '<div class="category-stats">';
		echo '<time class="category-time" datetime="">Latest post: Ikke tilgængelig</time>';
		echo '<span class="category-posts">Posts: XXX</span>';
		echo '<span class="category-comments">Comments: XXX</span>';
		echo '</div>';
		echo '</article>';
	}

	$result->free();
}

$conn->close();
?>
