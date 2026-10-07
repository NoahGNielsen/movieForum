(function () {
	// Profilbillede: afviser for store filer før upload og viser det valgte billede i cirklen.
	// Serveren tjekker størrelsen igen; dette sparer kun brugeren for at vente på en upload, der bliver afvist.
	function initializeProfilePicture() {
		const input = document.getElementById('profile_picture');
		const preview = document.querySelector('[data-profile-picture-preview]');

		if (!input || !preview) {
			return;
		}

		const maxBytes = Number(input.dataset.maxBytes) || 4 * 1024 * 1024;
		const originalPreview = preview.innerHTML;

		input.addEventListener('change', function () {
			const file = input.files && input.files[0];
			input.setCustomValidity('');
			preview.innerHTML = originalPreview;

			if (!file) {
				return;
			}

			if (file.size > maxBytes) {
				input.setCustomValidity('Billedet må højst være 4 MB.');
				input.reportValidity();
				return;
			}

			// Data-URL i stedet for blob-URL, fordi CSP'en kun tillader billeder fra 'self' og data:.
			const reader = new FileReader();
			reader.addEventListener('load', function () {
				// Brugeren kan have valgt en anden fil, mens denne blev læst
				if (input.files[0] !== file) {
					return;
				}
				const image = document.createElement('img');
				image.className = 'avatar avatar-lg avatar-image';
				image.alt = '';
				image.src = reader.result;
				preview.replaceChildren(image);
			});
			reader.readAsDataURL(file);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeProfilePicture);
	} else {
		initializeProfilePicture();
	}
}());
