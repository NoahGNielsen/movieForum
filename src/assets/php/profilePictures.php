<?php
// Profile pictures are stored in the attachments table as one 'profilePicture' row per user, always as WebP.
// This file has no side effects, so the avatar endpoint can use it without the session cookie handling.

const PROFILE_PICTURE_TABLE = 'Attachments';
const PROFILE_PICTURE_TYPE = 'profilePicture';
const PROFILE_PICTURE_MAX_BYTES = 4 * 1024 * 1024;
const PROFILE_PICTURE_MAX_SIDE = 496;
const PROFILE_PICTURE_WEBP_QUALITY = 82;
// GD needs about 4 bytes per pixel to decode, so larger images could run out of memory.
const PROFILE_PICTURE_MAX_PIXELS = 25000000;

// Subquery for the user's current profile picture id (or NULL), for use in a SELECT list.
function profilePictureIdSql(string $userIdColumn): string
{
	return '(SELECT MAX(pp.attachmentId) FROM ' . PROFILE_PICTURE_TABLE . ' AS pp'
		. ' WHERE pp.userId = ' . $userIdColumn . ' AND pp.attachmentType = \'' . PROFILE_PICTURE_TYPE . '\')';
}

// Pictures are addressed by their public attachmentId; the userId is the login cookie and must never be in a URL.
function profilePictureUrl(int $attachmentId): string
{
	return '/userMgmt/avatar?id=' . $attachmentId;
}

// Validates an uploaded image and re-encodes it as WebP, scaled down to fit within
// PROFILE_PICTURE_MAX_SIDE x PROFILE_PICTURE_MAX_SIDE. Re-encoding also strips EXIF data such as GPS location.
// Returns ['data' => WebP bytes] or ['error' => message for the user].
function profilePictureFromUpload($upload): array
{
	if (!is_array($upload) || !isset($upload['error'], $upload['tmp_name']) || !is_int($upload['error'])) {
		return ['error' => 'Vælg et billede at uploade.'];
	}

	switch ($upload['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			return ['error' => 'Vælg et billede at uploade.'];
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			return ['error' => 'Billedet må højst være 4 MB.'];
		default:
			return ['error' => 'Billedet kunne ikke uploades. Prøv igen.'];
	}

	$path = $upload['tmp_name'];
	if (!is_uploaded_file($path)) {
		return ['error' => 'Billedet kunne ikke uploades. Prøv igen.'];
	}

	$size = filesize($path);
	if ($size === false || $size > PROFILE_PICTURE_MAX_BYTES) {
		return ['error' => 'Billedet må højst være 4 MB.'];
	}

	if (!function_exists('imagewebp')) {
		return ['error' => 'Serveren kan ikke behandle billeder lige nu. Prøv igen senere.'];
	}

	// Checked from the file header before decoding, so a fake or oversized image never reaches GD.
	$info = @getimagesize($path);
	$supportedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
	if ($info === false || !in_array($info[2], $supportedTypes, true)) {
		return ['error' => 'Filen skal være et JPG-, PNG-, GIF- eller WebP-billede.'];
	}
	if ($info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > PROFILE_PICTURE_MAX_PIXELS) {
		return ['error' => 'Billedet har for mange pixels. Brug et billede på højst 25 megapixel.'];
	}

	ini_set('memory_limit', '256M');
	$contents = file_get_contents($path);
	$source = $contents === false ? false : @imagecreatefromstring($contents);
	unset($contents);
	if ($source === false) {
		return ['error' => 'Billedet kunne ikke læses. Prøv et andet billede.'];
	}

	$width = imagesx($source);
	$height = imagesy($source);
	$scale = min(1, PROFILE_PICTURE_MAX_SIDE / max($width, $height));
	$targetWidth = max(1, (int) round($width * $scale));
	$targetHeight = max(1, (int) round($height * $scale));

	// Always copied onto a fresh true-colour canvas, also when no scaling is needed, so transparency is kept
	// and palette images (GIF, PNG-8) can be saved as WebP.
	$picture = imagecreatetruecolor($targetWidth, $targetHeight);
	imagealphablending($picture, false);
	imagesavealpha($picture, true);
	imagefill($picture, 0, 0, imagecolorallocatealpha($picture, 0, 0, 0, 127));
	imagecopyresampled($picture, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
	unset($source);

	// Rotated after scaling, which is cheaper. The bounding square is the same either way round.
	if ($info[2] === IMAGETYPE_JPEG) {
		$picture = profilePictureApplyOrientation($picture, $path);
	}

	ob_start();
	$encoded = imagewebp($picture, null, PROFILE_PICTURE_WEBP_QUALITY);
	$data = ob_get_clean();
	unset($picture);

	if (!$encoded || !is_string($data) || $data === '') {
		return ['error' => 'Billedet kunne ikke gemmes. Prøv igen.'];
	}

	return ['data' => $data];
}

// Phone cameras save JPEGs sideways and set an EXIF Orientation tag instead. Re-encoding drops the tag,
// so the pixels are turned to match it.
function profilePictureApplyOrientation(GdImage $picture, string $jpegPath): GdImage
{
	if (!function_exists('exif_read_data')) {
		return $picture;
	}

	$exif = @exif_read_data($jpegPath);
	$orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

	// Orientation => [mirror horizontally first, then rotate this many degrees counter-clockwise]
	$transforms = [2 => [true, 0], 3 => [false, 180], 4 => [true, 180], 5 => [true, 90], 6 => [false, 270], 7 => [true, 270], 8 => [false, 90]];
	if (!isset($transforms[$orientation])) {
		return $picture;
	}

	[$mirror, $angle] = $transforms[$orientation];
	if ($mirror) {
		imageflip($picture, IMG_FLIP_HORIZONTAL);
	}
	if ($angle !== 0) {
		$rotated = imagerotate($picture, $angle, imagecolorallocatealpha($picture, 0, 0, 0, 127));
		if ($rotated !== false) {
			imagesavealpha($rotated, true);
			$picture = $rotated;
		}
	}

	return $picture;
}

// Replaces the user's profile picture. A new row means a new attachmentId and so a new URL,
// which is what lets the avatar endpoint cache each URL forever.
function saveProfilePicture(mysqli $conn, string $userId, string $webpData): bool
{
	$type = PROFILE_PICTURE_TYPE;
	$removeOld = $conn->prepare('DELETE FROM ' . PROFILE_PICTURE_TABLE . ' WHERE userId = ? AND attachmentType = ?');
	$insertNew = $conn->prepare('INSERT INTO ' . PROFILE_PICTURE_TABLE . ' (userId, attachmentType, attachmentFile) VALUES (?, ?, ?)');

	$saved = false;
	if ($removeOld !== false && $insertNew !== false) {
		$conn->begin_transaction();
		$removeOld->bind_param('ss', $userId, $type);
		$insertNew->bind_param('sss', $userId, $type, $webpData);
		$saved = $removeOld->execute() && $insertNew->execute();
		if ($saved) {
			$conn->commit();
		} else {
			$conn->rollback();
		}
	}

	if ($removeOld !== false) {
		$removeOld->close();
	}
	if ($insertNew !== false) {
		$insertNew->close();
	}

	return $saved;
}

function removeProfilePicture(mysqli $conn, string $userId): bool
{
	$type = PROFILE_PICTURE_TYPE;
	$remove = $conn->prepare('DELETE FROM ' . PROFILE_PICTURE_TABLE . ' WHERE userId = ? AND attachmentType = ?');
	if ($remove === false) {
		return false;
	}

	$remove->bind_param('ss', $userId, $type);
	$removed = $remove->execute();
	$remove->close();

	return $removed;
}
