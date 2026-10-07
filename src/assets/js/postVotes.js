(function () {
	function initializePostVotes() {
		const form = document.getElementById('postVotes');
		const score = document.getElementById('voteScore');

		if (!form || !score) {
			return;
		}

		// Error codes sent by vote.php. Anything else (including a non-JSON answer) gets the general message.
		const errorMessages = {
			csrf: 'Siden er udløbet. Genindlæs siden og prøv igen.',
			not_registered: 'Du skal have en registreret bruger for at stemme.',
			not_found: 'Indlægget findes ikke længere.'
		};
		const defaultErrorMessage = 'Din stemme kunne ikke gemmes. Prøv igen.';

		const upButton = form.querySelector('.voteUp');
		const downButton = form.querySelector('.voteDown');
		let isSending = false;

		const errorMessage = document.createElement('span');
		errorMessage.className = 'voteNotice voteError';
		errorMessage.setAttribute('role', 'status');
		form.appendChild(errorMessage);

		// userVote: 1 = upvote, 2 = downvote, 0 = no vote (same values as Interactions.interactionType).
		function showVotes(votes) {
			errorMessage.textContent = '';
			score.textContent = votes.score;
			score.title = votes.upvotes + ' op, ' + votes.downvotes + ' ned';
			upButton.setAttribute('aria-pressed', votes.userVote === 1 ? 'true' : 'false');
			downButton.setAttribute('aria-pressed', votes.userVote === 2 ? 'true' : 'false');
		}

		// Send the vote in the background instead of reloading the page. vote.php answers with the new totals.
		form.addEventListener('submit', function (event) {
			const button = event.submitter;
			if (!button || !button.value) {
				return;
			}
			event.preventDefault();

			if (isSending) {
				return;
			}
			isSending = true;

			const data = new FormData(form);
			data.append('vote', button.value);

			fetch(form.action, {
				method: 'POST',
				headers: { Accept: 'application/json' },
				body: data
			})
				.then(function (response) {
					return response.json()
						.catch(function () {
							throw new Error('Expected JSON from ' + form.action + ' but got status ' + response.status);
						})
						.then(function (votes) {
							// A failed save still sends the current totals, so the page stays in sync with the database.
							if (typeof votes.score === 'number') {
								showVotes(votes);
							}
							if (!response.ok) {
								const error = new Error('Vote failed with status ' + response.status + ': ' + votes.error);
								error.code = votes.error;
								throw error;
							}
						});
				})
				.catch(function (error) {
					console.error(error);
					errorMessage.textContent = errorMessages[error.code] || defaultErrorMessage;
				})
				.finally(function () {
					isSending = false;
				});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializePostVotes);
	} else {
		initializePostVotes();
	}
}());
