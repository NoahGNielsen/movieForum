(function () {
	function initializePostVotes() {
		const form = document.getElementById('postVotes');
		const score = document.getElementById('voteScore');

		if (!form || !score) {
			return;
		}

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
					return response.json().then(function (votes) {
						// A failed save still sends the current totals, so the page stays in sync with the database.
						if (typeof votes.score === 'number') {
							showVotes(votes);
						}
						if (!response.ok) {
							throw new Error('Request failed with status ' + response.status);
						}
					});
				})
				.catch(function () {
					errorMessage.textContent = 'Din stemme kunne ikke gemmes. Prøv igen.';
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
