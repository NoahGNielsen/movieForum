(function () {
	function initializeToast() {
		const toast = document.getElementById('successToast');

		if (!toast) {
			return;
		}

		// Remove the flag from the URL so a refresh doesn't show the toast again.
		const url = new URL(window.location.href);
		url.searchParams.delete('onboarded');
		window.history.replaceState(null, '', url.pathname + url.search + url.hash);

		function hideToast() {
			toast.classList.add('success-toast-hide');
			setTimeout(function () {
				toast.remove();
			}, 300);
		}

		toast.querySelector('.success-toast-close').addEventListener('click', hideToast);
		setTimeout(hideToast, 5000);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeToast);
	} else {
		initializeToast();
	}
}());
