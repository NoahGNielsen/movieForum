// Marks that JavaScript runs, so CSS may hide the mobile menu until the menu button opens it.
// Set here, in an external file loaded in <head> before the page is drawn, and not in an inline
// <script>: a Content-Security-Policy that blocks inline scripts would otherwise leave the menu
// always open on phones.
document.documentElement.classList.add('has-js');

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
