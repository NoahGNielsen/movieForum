<?php
require_once __DIR__ . '/userCookieHandeling.php';

function rejectCategoryRequest($statusCode, $message)
{
	http_response_code($statusCode);
	header('Content-Type: text/plain; charset=UTF-8');
	exit($message);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	rejectCategoryRequest(405, 'Ugyldig forespørgsel.');
}

$name = $_POST['newCategoriName'] ?? null;
$description = $_POST['newCategoriDescription'] ?? null;
$parentId = $_POST['newCategoriFormListSelect'] ?? null;

if (!is_string($name) || !is_string($description) || !is_string($parentId)) {
	rejectCategoryRequest(400, 'Udfyld alle felter korrekt.');
}

$name = trim($name);
$description = trim($description);

if (strlen($name) < 3 || strlen($name) > 35 || !preg_match('/^[A-Za-z0-9.,_@:!?()+& -]+$/D', $name)) {
	rejectCategoryRequest(400, 'Kategorinavnet skal være 3-35 tegn og må kun indeholde tilladte tegn.');
}

if (strlen($description) < 10 || strlen($description) > 254 || !preg_match('/^[A-Za-z0-9.,_@:!?()+& -]+$/D', $description)) {
	rejectCategoryRequest(400, 'Beskrivelsen skal være 10-254 tegn og må kun indeholde tilladte tegn.');
}

if (!preg_match('/^[1-9][0-9]*$/D', $parentId)) {
	rejectCategoryRequest(400, 'Vælg en gyldig overkategori.');
}

if (($_POST['acceptTerms'] ?? null) !== '1') {
	rejectCategoryRequest(400, 'Du skal acceptere erklæringen for at oprette en kategori.');
}

if (!loadDbConfigIfNeeded()) {
	rejectCategoryRequest(500, 'Databasekonfigurationen kunne ikke indlæses.');
}

$dbConfig = $GLOBALS['db_config'] ?? null;
if (!is_array($dbConfig)) {
	rejectCategoryRequest(500, 'Databasekonfigurationen kunne ikke indlæses.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli(
	$dbConfig['servername'],
	$dbConfig['username'],
	$dbConfig['password'],
	$dbConfig['dbname']
);

if ($conn->connect_error) {
	rejectCategoryRequest(500, 'Databaseforbindelsen mislykkedes.');
}

$userId = $_COOKIE['user_session_cookie'] ?? '';
if (!preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/D', $userId)) {
	$conn->close();
	rejectCategoryRequest(403, 'Du skal have en aktiv bruger for at oprette en kategori.');
}

$userCheck = $conn->prepare('SELECT userId FROM Users WHERE userId = ? LIMIT 1');
if ($userCheck === false) {
	$conn->close();
	rejectCategoryRequest(500, 'Brugeren kunne ikke kontrolleres.');
}
$userCheck->bind_param('s', $userId);
$userCheck->execute();
$userCheck->store_result();
$hasUser = $userCheck->num_rows > 0;
$userCheck->close();

if (!$hasUser) {
	$conn->close();
	rejectCategoryRequest(403, 'Du skal have en aktiv bruger for at oprette en kategori.');
}

$columnsResult = $conn->query('SHOW COLUMNS FROM Channels');
if ($columnsResult === false) {
	$conn->close();
	rejectCategoryRequest(500, 'Kategoritabellen kunne ikke kontrolleres.');
}

$parentColumn = null;
$parentColumnCandidates = ['parentChannelId', 'parentChannelID', 'parentId', 'channelParentId', 'parent_channel_id', 'parentCategoryId', 'parent_category_id'];
while ($column = $columnsResult->fetch_assoc()) {
	if (in_array($column['Field'], $parentColumnCandidates, true) || preg_match('/^(parent.*(channel|category|id)|(channel|category).*parent)/i', $column['Field'])) {
		$parentColumn = $column['Field'];
		break;
	}
}
$columnsResult->free();

if ($parentColumn === null) {
	$conn->close();
	rejectCategoryRequest(500, 'Kategoritabellen mangler en understøttet overkategori-reference.');
}

$safeParentColumn = '`' . str_replace('`', '``', $parentColumn) . '`';
$parentCheck = $conn->prepare('SELECT channelId FROM Channels WHERE channelId = ? AND isTopChannel = 1 LIMIT 1');
if ($parentCheck === false) {
	$conn->close();
	rejectCategoryRequest(500, 'Overkategorien kunne ikke kontrolleres.');
}
$parentCheck->bind_param('i', $parentId);
$parentCheck->execute();
$parentCheck->store_result();
$hasParent = $parentCheck->num_rows > 0;
$parentCheck->close();

if (!$hasParent) {
	$conn->close();
	rejectCategoryRequest(400, 'Den valgte overkategori findes ikke.');
}

$duplicateCheck = $conn->prepare(
	'SELECT channelId FROM Channels WHERE ' . $safeParentColumn . ' = ? AND LOWER(channelName) = LOWER(?) LIMIT 1'
);
if ($duplicateCheck === false) {
	$conn->close();
	rejectCategoryRequest(500, 'Kategorien kunne ikke kontrolleres for dubletter.');
}
$duplicateCheck->bind_param('is', $parentId, $name);
$duplicateCheck->execute();
$duplicateCheck->store_result();
$isDuplicate = $duplicateCheck->num_rows > 0;
$duplicateCheck->close();

if ($isDuplicate) {
	$conn->close();
	rejectCategoryRequest(409, 'Der findes allerede en underkategori med dette navn.');
}

$insert = $conn->prepare(
	'INSERT INTO Channels (channelName, channelDescription, isTopChannel, ' . $safeParentColumn . ') VALUES (?, ?, 0, ?)'
);
if ($insert === false) {
	$conn->close();
	rejectCategoryRequest(500, 'Kategorien kunne ikke oprettes.');
}
$insert->bind_param('ssi', $name, $description, $parentId);

if (!$insert->execute()) {
	$insert->close();
	$conn->close();
	rejectCategoryRequest(500, 'Kategorien kunne ikke oprettes.');
}

$insert->close();
$conn->close();
header('Location: ../../categories/');
exit;
?>
