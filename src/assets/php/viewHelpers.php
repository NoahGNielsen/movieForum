<?php
// Shared helpers for rendering posts, comments and users the same way on every page.
require_once __DIR__ . '/userCookieHandeling.php';
require_once __DIR__ . '/profilePictures.php';

mysqli_report(MYSQLI_REPORT_OFF);

function viewEscape($value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Opens a connection with the shared db config, or returns null so callers can show an error state.
function viewDbConnect(): ?mysqli
{
	$dbConfig = loadDbConfigIfNeeded() ? ($GLOBALS['db_config'] ?? null) : null;
	if (!is_array($dbConfig)) {
		return null;
	}

	$conn = new mysqli($dbConfig['servername'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
	if ($conn->connect_error) {
		return null;
	}

	$conn->set_charset('utf8mb4');
	return $conn;
}

// The visitor's own user row, or null for guests without a registered username.
// The userId is the session cookie, so it must never be printed on a page or put in a URL.
function viewCurrentUser(): ?array
{
	static $resolved = false;
	static $user = null;
	if ($resolved) {
		return $user;
	}
	$resolved = true;

	$userId = $_COOKIE['user_session_cookie'] ?? '';
	if (!is_string($userId) || !preg_match('/\A[A-Za-z]{8}_[0-9]{3}_[0-9]{5}\z/', $userId)) {
		return null;
	}

	$conn = viewDbConnect();
	if ($conn === null) {
		return null;
	}

	$findUser = $conn->prepare('SELECT u.userName, u.lastSeen, ' . profilePictureIdSql('u.userId') . ' FROM Users AS u WHERE u.userId = ? LIMIT 1');
	if ($findUser !== false) {
		$findUser->bind_param('s', $userId);
		$findUser->execute();
		$findUser->bind_result($userName, $lastSeen, $avatarId);
		if ($findUser->fetch()) {
			$user = [
				'id' => $userId,
				'name' => (string) $userName,
				'last_seen' => $lastSeen,
				'avatar_id' => $avatarId !== null ? (int) $avatarId : null,
			];
		}
		$findUser->close();
	}
	$conn->close();

	return $user;
}

// "5 minutter siden", "i går", or a date once it is more than a week old.
function viewRelativeTime($timestamp): string
{
	$time = strtotime((string) $timestamp);
	if ($time === false) {
		return (string) $timestamp;
	}

	// Clock skew between PHP and the database must never show "-3 minutter siden".
	$seconds = max(0, time() - $time);
	$minutes = intdiv($seconds, 60);
	$hours = intdiv($seconds, 3600);
	$days = intdiv($seconds, 86400);

	if ($minutes < 1) {
		return 'lige nu';
	}
	if ($hours < 1) {
		return $minutes === 1 ? '1 minut siden' : "{$minutes} minutter siden";
	}
	if ($days < 1) {
		return $hours === 1 ? '1 time siden' : "{$hours} timer siden";
	}
	if ($days === 1) {
		return 'i går';
	}
	if ($days < 7) {
		return "{$days} dage siden";
	}

	return date('d.m.Y', $time);
}

function viewExactTime($timestamp): string
{
	$time = strtotime((string) $timestamp);
	return $time === false ? (string) $timestamp : date('d.m.Y \k\l. H:i', $time);
}

// <time> element with the relative label and the exact time as tooltip.
function viewTime($timestamp): string
{
	if ($timestamp === null || $timestamp === '') {
		return '';
	}

	return '<time datetime="' . viewEscape($timestamp) . '" title="' . viewEscape(viewExactTime($timestamp)) . '">'
		. viewEscape(viewRelativeTime($timestamp)) . '</time>';
}

// Usernames are stored as [G-]name#12345. Returns ['name' => '[G-]name', 'tag' => '#12345' or ''].
function viewSplitUserName($userName): array
{
	$userName = (string) $userName;
	$hashPosition = strrpos($userName, '#');
	if ($hashPosition === false) {
		return ['name' => $userName, 'tag' => ''];
	}

	return ['name' => substr($userName, 0, $hashPosition), 'tag' => substr($userName, $hashPosition)];
}

// The #12345 part is shown muted so the name reads first.
function viewUserName($userName): string
{
	$parts = viewSplitUserName($userName);
	$tag = $parts['tag'] === '' ? '' : '<span class="user-tag">' . viewEscape($parts['tag']) . '</span>';

	return '<span class="user-name">' . viewEscape($parts['name']) . $tag . '</span>';
}

// First letter of the name, ignoring the G- guest prefix.
function viewInitial($userName): string
{
	$name = preg_replace('/\AG-/', '', (string) $userName) ?? '';
	$initial = function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1);
	$initial = function_exists('mb_strtoupper') ? mb_strtoupper($initial, 'UTF-8') : strtoupper($initial);

	return $initial !== '' ? $initial : '?';
}

// Round avatar: the user's profile picture, or else their first letter, which tells users apart
// better than the same silhouette on every comment. $avatarId comes from profilePictureIdSql().
function viewAvatar($userName, string $size = 'md', ?int $avatarId = null): string
{
	if ($avatarId !== null) {
		return '<img class="avatar avatar-' . viewEscape($size) . ' avatar-image" src="' . viewEscape(profilePictureUrl($avatarId)) . '"'
			. ' alt="" loading="lazy" decoding="async">';
	}

	return '<span class="avatar avatar-' . viewEscape($size) . '" aria-hidden="true">'
		. viewEscape(viewInitial($userName)) . '</span>';
}

function viewExcerpt($text, int $length = 220): string
{
	$text = trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');
	$textLength = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
	if ($textLength <= $length) {
		return $text;
	}

	$cut = function_exists('mb_substr') ? mb_substr($text, 0, $length, 'UTF-8') : substr($text, 0, $length);
	return rtrim($cut, " .,;:-") . '…';
}

// Long unbroken words (e.g. "JDDDDDDDD...") would run out of the card. A soft hyphen (U+00AD) every
// few characters lets the browser break them, and a "-" is only drawn where a line actually breaks.
// Applied when rendering, never stored. Counted in grapheme clusters so emoji are never split.
const VIEW_LONG_WORD_LENGTH = 20;
const VIEW_HYPHEN_INTERVAL = 10;

function viewBreakLongWords($text): string
{
	$text = (string) $text;
	$result = preg_replace_callback(
		'/\S{' . (VIEW_LONG_WORD_LENGTH + 1) . ',}/u',
		static function (array $match): string {
			if (!preg_match_all('/\X/u', $match[0], $graphemes)) {
				return $match[0];
			}
			$chunks = array_chunk($graphemes[0], VIEW_HYPHEN_INTERVAL);
			return implode("\u{00AD}", array_map(static fn (array $chunk): string => implode('', $chunk), $chunks));
		},
		$text
	);

	return $result ?? $text;
}

// A reply quotes the start of the comment it answers. Matches REPLY_EXCERPT_LENGTH in commentReply.js.
const VIEW_REPLY_EXCERPT_LENGTH = 100;

// Extra columns and joins for a comment query (Comments AS cm) that describe the comment each reply answers.
// Read the four columns back with viewReplyContext().
function viewReplyColumnsSql(): string
{
	return 'cm.replyToCommentId, parent.commentId, COALESCE(pu.userName, \'Ukendt bruger\'), LEFT(parent.messageContent, 200)';
}

function viewReplyJoinSql(): string
{
	return 'LEFT JOIN Comments AS parent ON parent.commentId = cm.replyToCommentId
		 LEFT JOIN Users AS pu ON pu.userId = parent.userId';
}

// What a reply shows about the comment it answers, or null for a comment that is not a reply.
// If that comment has been deleted, the reply still says it was one.
function viewReplyContext($replyToCommentId, $parentId, $parentUserName, $parentContent): ?array
{
	if ($replyToCommentId === null) {
		return null;
	}
	if ($parentId === null) {
		return ['id' => (int) $replyToCommentId, 'deleted' => true];
	}

	$nameParts = viewSplitUserName($parentUserName);
	return [
		'id' => (int) $parentId,
		'deleted' => false,
		'username' => (string) $parentUserName,
		'nameBase' => $nameParts['name'],
		'nameTag' => $nameParts['tag'],
		'excerpt' => viewExcerpt($parentContent, VIEW_REPLY_EXCERPT_LENGTH),
	];
}

// "Svar til <name>" and the start of the answered comment, linking to it. Takes viewReplyContext().
// commentPoller.js builds the same markup in buildReplyContext(); keep the two in sync.
function viewReplyContextHtml(?array $reply): string
{
	if ($reply === null) {
		return '';
	}
	if ($reply['deleted']) {
		return '<p class="commentReplyContext">Svar til en slettet kommentar</p>';
	}

	return '<a class="commentReplyContext" href="#comment-' . (int) $reply['id'] . '">'
		. '<span class="commentReplyTo">Svar til ' . viewUserName($reply['username']) . '</span>'
		. '<span class="commentReplyExcerpt">' . viewEscape($reply['excerpt']) . '</span></a>';
}

function viewReplyLabel(int $count): string
{
	return $count === 1 ? '1 svar' : "{$count} svar";
}

// Share button: uses the native share sheet where available and falls back to copying the link.
function viewShareButton(string $path, string $title): string
{
	return '<button type="button" class="pill" data-share-path="' . viewEscape($path) . '" data-share-title="' . viewEscape($title) . '">'
		. '<svg class="pill-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7M12 3v13M7 8l5-5 5 5"/></svg>'
		. '<span data-share-label aria-live="polite">Del</span></button>';
}

function viewReplyIcon(): string
{
	return '<svg class="pill-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/></svg>';
}
