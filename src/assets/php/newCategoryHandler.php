<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	header('Allow: POST');
	exit('Ugyldig forespørgsel.');
}

$categoryName = $_POST['newCategoriName'] ?? null;
$categoryDescription = $_POST['newCategoriDescription'] ?? null;
$parentChannelId = filter_var($_POST['newCategoriFormListSelect'] ?? null, FILTER_VALIDATE_INT);
$allowedCharacters = '/\A[A-Za-z0-9.,_@:!?()+& -]+\z/';

if (
	!is_string($categoryName)
	|| !is_string($categoryDescription)
	|| strlen($categoryName) < 3
	|| strlen($categoryName) > 35
	|| strlen($categoryDescription) < 10
	|| strlen($categoryDescription) > 254
	|| !preg_match($allowedCharacters, $categoryName)
	|| !preg_match($allowedCharacters, $categoryDescription)
	|| $parentChannelId === false
	|| $parentChannelId < 1
	|| ($_POST['acceptTerms'] ?? null) !== '1'
) {
	http_response_code(400);
	exit('Kontrollér kategoriens navn, beskrivelse, overkategori og accept af retningslinjerne.');
}

$configPath = __DIR__ . '/../../../config.php';
if (!is_file($configPath)) {
	http_response_code(500);
	exit('Databasekonfigurationen mangler.');
}

$config = require_once $configPath;
$dbConfig = $GLOBALS['db_config'] ?? ($db_config ?? $config ?? null);
if (
	!is_array($dbConfig)
	|| !isset($dbConfig['servername'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname'])
) {
	http_response_code(500);
	exit('Databasekonfigurationen er ugyldig.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	http_response_code(500);
	exit('Forbindelse til databasen mislykkedes.');
}

$conn->set_charset('utf8mb4');
$columnsResult = $conn->query('SHOW COLUMNS FROM Channels');
if ($columnsResult === false) {
	$conn->close();
	http_response_code(500);
	exit('Kategoritabellen kunne ikke læses.');
}

$availableColumns = [];
while ($column = $columnsResult->fetch_assoc()) {
	$availableColumns[] = $column['Field'];
}
$columnsResult->free();

$parentColumn = null;
foreach (['parentChannelId', 'channelParentId', 'parentId', 'topChannelId'] as $candidate) {
	if (in_array($candidate, $availableColumns, true)) {
		$parentColumn = $candidate;
		break;
	}
}

if ($parentColumn === null) {
	$conn->close();
	http_response_code(500);
	exit('Kategoritabellen mangler en understøttet reference til overkategorien.');
}

$parentCheck = $conn->prepare('SELECT channelId FROM Channels WHERE channelId = ? AND isTopChannel = 1');
if ($parentCheck === false) {
	$conn->close();
	http_response_code(500);
	exit('Overkategorien kunne ikke kontrolleres.');
}
$parentCheck->bind_param('i', $parentChannelId);
$parentCheck->execute();
$parentCheck->store_result();
$parentExists = $parentCheck->num_rows === 1;
$parentCheck->close();

if (!$parentExists) {
	$conn->close();
	http_response_code(400);
	exit('Den valgte overkategori findes ikke.');
}

$escapedParentColumn = '`' . str_replace('`', '``', $parentColumn) . '`';
$insert = $conn->prepare(
	'INSERT INTO Channels (channelName, channelDescription, isTopChannel, ' . $escapedParentColumn . ') VALUES (?, ?, 0, ?)'
);

if ($insert === false) {
	$conn->close();
	http_response_code(500);
	exit('Kategorien kunne ikke oprettes.');
}

$insert->bind_param('ssi', $categoryName, $categoryDescription, $parentChannelId);
$created = $insert->execute();
$insert->close();
$conn->close();

if (!$created) {
	http_response_code(500);
	exit('Kategorien kunne ikke oprettes.');
}

header('Location: ../../categories/newCategory.php?created=1');
exit;