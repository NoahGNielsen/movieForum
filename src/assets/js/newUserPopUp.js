(function () {
	function initializePopup() {
		const popup = document.getElementById('newUserPopup');
		const form = document.getElementById('newUserConsentForm');

		if (!popup || !form) {
			return;
		}

		const submitButton = form.querySelector('button[type="submit"]');
		const consentCheckboxes = form.querySelectorAll('input[type="checkbox"]');

		function updateSubmitButton() {
			submitButton.disabled = !Array.from(consentCheckboxes).every(function (checkbox) {
				return checkbox.checked;
			});
		}

		consentCheckboxes.forEach(function (checkbox) {
			checkbox.addEventListener('change', updateSubmitButton);
		});
		updateSubmitButton();

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			document.cookie = 'user_consent=accepted; max-age=31536000; path=/; SameSite=Lax';
			popup.remove();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializePopup);
	} else {
		initializePopup();
	}
}());