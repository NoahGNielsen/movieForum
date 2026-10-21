(function () {
	// Billeder til nyt indlæg: afviser for mange eller for store filer før upload og viser de valgte billeder.
	// Serveren tjekker det hele igen; dette sparer kun brugeren for at vente på en upload, der bliver afvist.
	function initializeNewPostImages() {
		const input = document.getElementById('newPostImages');
		const preview = document.getElementById('newPostImagePreview');

		if (!input || !preview) {
			return;
		}

		const maxCount = Number(input.dataset.maxCount) || 4;
		const maxBytes = Number(input.dataset.maxBytes) || 4 * 1024 * 1024;

		input.addEventListener('change', function () {
			const files = Array.from(input.files || []);
			input.setCustomValidity('');
			preview.replaceChildren();
			preview.hidden = true;

			if (files.length === 0) {
				return;
			}

			if (files.length > maxCount) {
				input.setCustomValidity('Du kan højst vedhæfte ' + maxCount + ' billeder til et indlæg.');
				input.reportValidity();
				return;
			}

			const tooLarge = files.find(function (file) {
				return file.size > maxBytes;
			});
			if (tooLarge) {
				input.setCustomValidity(tooLarge.name + ': Billedet må højst være 4 MB.');
				input.reportValidity();
				return;
			}

			preview.hidden = false;
			files.forEach(function (file) {
				const image = document.createElement('img');
				image.className = 'newPostImagePreviewItem';
				image.alt = file.name;
				preview.append(image);

				// Data-URL i stedet for blob-URL, fordi CSP'en kun tillader billeder fra 'self' og data:.
				const reader = new FileReader();
				reader.addEventListener('load', function () {
					image.src = reader.result;
				});
				reader.readAsDataURL(file);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeNewPostImages);
	} else {
		initializeNewPostImages();
	}
}());
