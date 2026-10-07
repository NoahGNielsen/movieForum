<?php
require_once __DIR__ . '/viewHelpers.php';

// All top categories with their subcategories, for /categories/. One query; the tree is built in PHP.
function loadCategoryDirectory(): ?array
{
	$conn = viewDbConnect();
	if ($conn === null) {
		return null;
	}

	$result = $conn->query(
		'SELECT c.channelId, c.channelName, c.channelDescription, c.isTopChannel, c.ownerChannelId, COUNT(p.postId)
		 FROM Channels AS c
		 LEFT JOIN Posts AS p ON p.channelId = c.channelId
		 GROUP BY c.channelId, c.channelName, c.channelDescription, c.isTopChannel, c.ownerChannelId
		 ORDER BY c.channelName'
	);

	if ($result === false) {
		$conn->close();
		return null;
	}

	$topCategories = [];
	$subcategories = [];
	while ($row = $result->fetch_row()) {
		[$id, $name, $description, $isTop, $ownerId, $postCount] = $row;
		$category = [
			'id' => (int) $id,
			'name' => (string) $name,
			'description' => trim((string) $description),
			'posts' => (int) $postCount,
		];

		if ((int) $isTop === 1) {
			$topCategories[(int) $id] = $category + ['subcategories' => []];
		} else {
			$subcategories[] = $category + ['owner' => (int) $ownerId];
		}
	}
	$result->free();
	$conn->close();

	foreach ($subcategories as $subcategory) {
		if (isset($topCategories[$subcategory['owner']])) {
			$topCategories[$subcategory['owner']]['subcategories'][] = $subcategory;
		}
	}

	return array_values($topCategories);
}

function directoryCategoryUrl(string $name): string
{
	return '/categories/' . rawurlencode($name);
}

$categoryDirectory = loadCategoryDirectory();
