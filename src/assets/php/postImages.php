<?php
// Images attached to a post are stored in the attachments table as 'postImage' rows with the post's id,
// always as WebP. Like profilePictures.php this file has no side effects, so the image endpoint can use it.
require_once __DIR__ . '/profilePictures.php';

const POST_IMAGE_TYPE = 'postImage';
const POST_IMAGE_MAX_COUNT = 4;
const POST_IMAGE_MAX_SIDE = 1600;

function postImageUrl(int $attachmentId): string
{
	return '/posts/image?id=' . $attachmentId;
}

// Turns the newPostImages[] file field into WebP images. Empty slots (no file chosen) are skipped.
// Returns ['images' => [WebP bytes, ...], 'errors' => [message for the user, ...]].
function postImagesFromUploads($uploads): array
{
	$result = ['images' => [], 'errors' => []];
	if (!is_array($uploads) || !isset($uploads['error'])) {
		return $result;
	}

	// With name="newPostImages[]" PHP gives one array per field (name, tmp_name, error, ...) instead of one per file.
	$files = [];
	foreach ((array) $uploads['error'] as $index => $error) {
		if ($error === UPLOAD_ERR_NO_FILE) {
			continue;
		}
		$files[] = [
			'name' => (string) (is_array($uploads['name'] ?? null) ? ($uploads['name'][$index] ?? '') : ($uploads['name'] ?? '')),
			'tmp_name' => is_array($uploads['tmp_name'] ?? null) ? ($uploads['tmp_name'][$index] ?? '') : ($uploads['tmp_name'] ?? ''),
			'error' => $error,
		];
	}

	if (count($files) > POST_IMAGE_MAX_COUNT) {
		$result['errors'][] = 'Du kan højst vedhæfte ' . POST_IMAGE_MAX_COUNT . ' billeder til et indlæg.';
		return $result;
	}

	foreach ($files as $file) {
		$image = webpFromUploadedImage($file, POST_IMAGE_MAX_SIDE);
		if (isset($image['error'])) {
			$result['errors'][] = ($file['name'] !== '' ? $file['name'] . ': ' : '') . $image['error'];
		} else {
			$result['images'][] = $image['data'];
		}
	}

	return $result;
}

// Called inside the transaction that creates the post, so a post is never saved with only some of its images.
function savePostImages(mysqli $conn, string $userId, int $postId, array $images): bool
{
	if (empty($images)) {
		return true;
	}

	$insert = $conn->prepare('INSERT INTO ' . PROFILE_PICTURE_TABLE . ' (userId, attachmentType, attachmentFile, postId) VALUES (?, ?, ?, ?)');
	if ($insert === false) {
		return false;
	}

	$type = POST_IMAGE_TYPE;
	$data = '';
	$insert->bind_param('sssi', $userId, $type, $data, $postId);
	$saved = true;
	foreach ($images as $data) {
		if (!$insert->execute()) {
			$saved = false;
			break;
		}
	}
	$insert->close();

	return $saved;
}

// Attachment ids of the post's images, in the order they were uploaded.
function loadPostImageIds(mysqli $conn, int $postId): array
{
	$findImages = $conn->prepare('SELECT attachmentId FROM ' . PROFILE_PICTURE_TABLE . ' WHERE postId = ? AND attachmentType = ? ORDER BY attachmentId');
	if ($findImages === false) {
		return [];
	}

	$type = POST_IMAGE_TYPE;
	$ids = [];
	$findImages->bind_param('is', $postId, $type);
	$findImages->execute();
	$findImages->bind_result($attachmentId);
	while ($findImages->fetch()) {
		$ids[] = (int) $attachmentId;
	}
	$findImages->close();

	return $ids;
}
