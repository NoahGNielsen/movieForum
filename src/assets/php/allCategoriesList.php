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
	'SELECT channelId, channelName
	 FROM Channels
	 WHERE isTopChannel = 1
	 ORDER BY channelName'
);

$selectedParentId = filter_input(INPUT_GET, 'parentId', FILTER_VALIDATE_INT);

if ($result !== false) {
	while ($category = $result->fetch_assoc()) {
		$categoryId = htmlspecialchars((string) $category['channelId'], ENT_QUOTES, 'UTF-8');
		$categoryName = htmlspecialchars((string) $category['channelName'], ENT_QUOTES, 'UTF-8');
		$selected = (int) $category['channelId'] === $selectedParentId ? ' selected' : '';
		echo '<option value="' . $categoryId . '"' . $selected . '>' . $categoryName . '</option>';
	}

	$result->free();
}

$conn->close();
?>
