(function () {
	// Poll every 10 seconds, slowing down to once a minute while nothing new arrives.
	const baseDelay = 5000;
	const maxDelay = 30000;

	function initializeCommentPoller() {
		const list = document.getElementById('commentList');
		const button = document.getElementById('newCommentsButton');
		const heading = document.getElementById('commentsHeading');
		const loadMoreButton = document.getElementById('loadMoreCommentsButton');

		if (!list || !button) {
			return;
		}

		const postId = list.dataset.postId;
		let lastCommentId = Number(list.dataset.lastCommentId) || 0;
		let totalComments = Number(list.dataset.totalComments) || list.children.length;
		const commentLimit = Number(list.dataset.commentLimit) || 30;
		let pendingComments = [];
		let delay = baseDelay;
		let timer = null;
		let isFetching = false;

		function scheduleCheck() {
			clearTimeout(timer);
			if (document.visibilityState === 'visible') {
				timer = setTimeout(checkForComments, delay);
			}
		}

		function checkForComments() {
			if (isFetching) {
				return;
			}
			isFetching = true;

			fetch('/posts/newComments?postId=' + encodeURIComponent(postId) + '&after=' + lastCommentId, {
				headers: { Accept: 'application/json' }
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Request failed with status ' + response.status);
					}
					return response.json();
				})
				.then(function (data) {
					const comments = Array.isArray(data.comments) ? data.comments : [];
					if (comments.length > 0) {
						pendingComments = pendingComments.concat(comments);
						lastCommentId = comments[comments.length - 1].id;
						delay = baseDelay;
						updateButton();
					} else {
						delay = Math.min(delay * 1.5, maxDelay);
					}
				})
				.catch(function () {
					delay = Math.min(delay * 2, maxDelay);
				})
				.finally(function () {
					isFetching = false;
					scheduleCheck();
				});
		}

		function updateButton() {
			const count = pendingComments.length;
			button.hidden = count === 0;
			button.textContent = count === 1 ? 'Vis 1 ny kommentar' : 'Vis ' + count + ' nye kommentarer';
		}

		// Built with textContent so user-written text is never parsed as HTML.
		function buildComment(comment) {
			const entry = document.createElement('li');
			entry.className = 'commentEntry';
			entry.id = 'comment-' + comment.id;

			const meta = document.createElement('p');
			meta.className = 'postMeta';
			const author = document.createElement('span');
			author.textContent = comment.username;
			meta.appendChild(author);

			if (comment.timestamp) {
				const time = document.createElement('time');
				time.dateTime = comment.timestamp;
				time.textContent = comment.displayTime;
				meta.appendChild(time);
			}

			const content = document.createElement('p');
			content.className = 'commentContent';
			content.textContent = comment.content;

			entry.appendChild(meta);
			entry.appendChild(content);
			return entry;
		}

		function showPendingComments() {
			const newEntries = pendingComments
				.filter(function (comment) {
					return !document.getElementById('comment-' + comment.id);
				})
				.map(buildComment);
			pendingComments = [];
			updateButton();

			if (newEntries.length === 0) {
				return;
			}

			// Keep the list at the length the reader already had (at least one page); anything pushed off the
			// bottom can be fetched again with "Vis flere kommentarer".
			const keepCount = Math.max(list.children.length, commentLimit);

			// Pending comments arrive oldest first, so prepending each one leaves the newest at the top.
			newEntries.forEach(function (entry) {
				list.prepend(entry);
			});
			if (list.children.length > keepCount) {
				while (list.children.length > keepCount) {
					list.lastElementChild.remove();
				}
				if (loadMoreButton) {
					loadMoreButton.hidden = false;
				}
			}
			list.hidden = false;

			const emptyState = document.getElementById('commentsEmptyState');
			if (emptyState) {
				emptyState.remove();
			}
			totalComments += newEntries.length;
			if (heading) {
				heading.textContent = 'Kommentarer (' + totalComments + ')';
			}

			const firstEntry = list.firstElementChild;
			const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			firstEntry.tabIndex = -1;
			firstEntry.focus({ preventScroll: true });
			firstEntry.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
		}

		function loadOlderComments() {
			const oldestEntry = list.lastElementChild;
			const oldestCommentId = oldestEntry ? Number(oldestEntry.id.replace('comment-', '')) : 0;
			if (!oldestCommentId) {
				loadMoreButton.hidden = true;
				return;
			}

			loadMoreButton.disabled = true;
			loadMoreButton.textContent = 'Indlæser…';

			fetch('/posts/newComments?postId=' + encodeURIComponent(postId) + '&before=' + oldestCommentId, {
				headers: { Accept: 'application/json' }
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Request failed with status ' + response.status);
					}
					return response.json();
				})
				.then(function (data) {
					const comments = Array.isArray(data.comments) ? data.comments : [];
					comments
						.filter(function (comment) {
							return !document.getElementById('comment-' + comment.id);
						})
						.forEach(function (comment) {
							list.appendChild(buildComment(comment));
						});
					loadMoreButton.textContent = 'Vis flere kommentarer';
					loadMoreButton.hidden = !data.hasMore;
				})
				.catch(function () {
					loadMoreButton.textContent = 'Kunne ikke hente kommentarer. Prøv igen';
				})
				.finally(function () {
					loadMoreButton.disabled = false;
				});
		}

		button.addEventListener('click', showPendingComments);
		if (loadMoreButton) {
			loadMoreButton.addEventListener('click', loadOlderComments);
		}

		document.addEventListener('visibilitychange', function () {
			clearTimeout(timer);
			if (document.visibilityState === 'visible') {
				checkForComments();
			}
		});

		scheduleCheck();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeCommentPoller);
	} else {
		initializeCommentPoller();
	}
}());
