(function () {
	const cookieName = 'theme';
	const cookieLifetime = 31536000;
	const validThemes = ['dark', 'light'];
	const defaultTheme = 'dark';

	function getSavedTheme() {
		const themeCookie = document.cookie.split('; ').find(function (cookie) {
			return cookie.startsWith(cookieName + '=');
		});
		const theme = themeCookie ? decodeURIComponent(themeCookie.split('=')[1]) : defaultTheme;

		// Ældre cookies kan have værdien "auto"; den falder tilbage til mørk
		return validThemes.includes(theme) ? theme : defaultTheme;
	}

	function applyTheme(theme) {
		document.documentElement.classList.toggle('light', theme === 'light');
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
