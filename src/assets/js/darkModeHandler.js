(function () {
	const cookieName = 'theme';
	const cookieLifetime = 31536000;
	const validThemes = ['auto', 'light', 'dark'];

	function getSavedTheme() {
		const themeCookie = document.cookie.split('; ').find(function (cookie) {
			return cookie.startsWith(cookieName + '=');
		});
		const theme = themeCookie ? decodeURIComponent(themeCookie.split('=')[1]) : 'auto';

		return validThemes.includes(theme) ? theme : 'auto';
	}

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

	const savedTheme = getSavedTheme();
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