<?php if (!empty($isNewCookie) && (($_COOKIE['user_consent'] ?? '') !== 'accepted')): ?>
<div class="new-user-popup" id="newUserPopup" role="dialog" aria-modal="true" aria-labelledby="newUserPopupTitle">
	<div class="new-user-popup-content">
		<h2 id="newUserPopupTitle">Velkommen til Pellicula Film Forum</h2>
		<p>Før du fortsætter, skal du acceptere vores vilkår & betingelser og bekræfte, at du er 18 år eller ældre.</p>
		<form id="newUserConsentForm">
			<label class="new-user-popup-consent" for="terms">
				<input id="terms" type="checkbox" name="terms" required>
				<span>Jeg accepterer <a class="newUserPopUpLink" href="/legal/terms" target="_blank" rel="noopener">vilkårene og betingelserne</a>. Derudover accepterer jeg brugen af cookies på dette websted. Cookies bruges til at forbedre brugeroplevelsen, analysere trafik og tilpasse indhold. Ved at acceptere cookies giver du os tilladelse til at gemme og få adgang til oplysninger på din enhed. Du kan til enhver tid ændre dine cookie-indstillinger i din browser. For mere information, se vores <a class="newUserPopUpLink" href="/legal/privacy" target="_blank" rel="noopener">privatlivspolitik</a>.</span>
            </label>
			<label class="new-user-popup-consent" for="age">
				<input id="age" type="checkbox" name="age" required>
				<span>Jeg bekræfter, at jeg er <b><u>18 år eller ældre</u></b>.</span>
            </label>
			<button type="submit" disabled>Fortsæt</button>
		</form>
	</div>
</div>
<script>
(function () {
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
}());
</script>
<?php endif; ?>
