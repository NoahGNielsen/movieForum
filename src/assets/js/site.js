(function () {
	// Mobilmenu: knappen viser og skjuler navigationen under topbaren
	const menuButton = document.querySelector('.topbar-menu-button');
	const topbar = document.querySelector('.topbar');

	if (menuButton && topbar) {
		const setOpen = function (isOpen) {
			topbar.classList.toggle('is-open', isOpen);
			menuButton.setAttribute('aria-expanded', String(isOpen));
		};

		menuButton.addEventListener('click', function () {
			setOpen(menuButton.getAttribute('aria-expanded') !== 'true');
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && topbar.classList.contains('is-open')) {
				setOpen(false);
				menuButton.focus();
			}
		});
	}

	// Del-knapper: systemets delingsmenu hvis den findes, ellers kopieres linket
	document.querySelectorAll('[data-share-path]').forEach(function (button) {
		const label = button.querySelector('[data-share-label]');

		button.addEventListener('click', async function () {
			const url = new URL(button.dataset.sharePath, window.location.origin).href;

			if (navigator.share) {
				try {
					await navigator.share({ title: button.dataset.shareTitle, url: url });
				} catch (error) {
					// Brugeren lukkede delingsmenuen; intet at gøre
				}
				return;
			}

			try {
				await navigator.clipboard.writeText(url);
				label.textContent = 'Link kopieret';
			} catch (error) {
				label.textContent = 'Kunne ikke kopiere';
			}

			setTimeout(function () {
				label.textContent = 'Del';
			}, 2000);
		});
	});
}());
