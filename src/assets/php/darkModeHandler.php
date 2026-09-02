<?php
$theme = $_COOKIE['theme'] ?? 'auto';

if (!in_array($theme, ['auto', 'light', 'dark'], true)) {
	$theme = 'auto';
}
?>
<script>
(function () {
	const savedTheme = <?= json_encode($theme) ?>;
	const cookieName = 'theme';
	const cookieLifetime = 31536000;

	function applyTheme(theme) {
		if (theme === 'auto') {
			document.documentElement.removeAttribute('data-theme');
		} else {
			document.documentElement.dataset.theme = theme;
		}
	}

	function saveTheme(theme) {
		document.cookie = cookieName + '=' + theme + '; max-age=' + cookieLifetime + '; path=/; SameSite=Lax';
	}

	applyTheme(savedTheme);

	document.addEventListener('DOMContentLoaded', function () {
		const selector = document.getElementById('darkModeSwitch');

		if (!selector) {
			return;
		}

		selector.value = savedTheme;
		selector.addEventListener('change', function () {
			applyTheme(selector.value);
			saveTheme(selector.value);
		});
	});
}());
</script>
