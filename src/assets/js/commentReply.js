(function () {
	// Matches VIEW_REPLY_EXCERPT_LENGTH in viewHelpers.php.
	const REPLY_EXCERPT_LENGTH = 100;

	// "Svar" on a comment turns the comment form into a reply to it. Without JavaScript the button is a link
	// to ?replyTo=<id>, which postViewer.php renders the same way.
	function initializeCommentReplies() {
		const list = document.getElementById('commentList');
		const form = document.getElementById('commentForm');
		if (!list || !form) {
			return;
		}

		const replyInput = document.getElementById('replyToCommentId');
		const banner = document.getElementById('commentReplyBanner');
		const replyName = document.getElementById('commentReplyName');
		const replyExcerpt = document.getElementById('commentReplyExcerpt');
		const cancelButton = document.getElementById('commentReplyCancel');
		const textarea = document.getElementById('newCommentContent');
		const label = form.querySelector('label[for="newCommentContent"]');
		const submitButton = form.querySelector('.commentFormSubmit');

		// Same as viewExcerpt() in viewHelpers.php. Soft hyphens from viewBreakLongWords() are dropped.
		function excerpt(text) {
			const characters = Array.from(text.replace(/­/g, '').replace(/\s+/g, ' ').trim());
			if (characters.length <= REPLY_EXCERPT_LENGTH) {
				return characters.join('');
			}
			return characters.slice(0, REPLY_EXCERPT_LENGTH).join('').replace(/[ .,;:-]+$/, '') + '…';
		}

		// Labels must match the comment form in posts/post.php.
		function setReplyMode(isReply) {
			const prompt = isReply ? 'Skriv et svar' : 'Skriv en kommentar';
			textarea.placeholder = prompt;
			label.textContent = prompt;
			submitButton.textContent = isReply ? 'Send svar' : 'Send kommentar';
			banner.hidden = !isReply;
		}

		function startReply(entry) {
			const name = entry.querySelector('.post-card-byline .user-name');
			const content = entry.querySelector('.commentContent');

			replyInput.value = entry.id.replace('comment-', '');
			replyName.replaceChildren(name ? name.cloneNode(true) : document.createTextNode('Ukendt bruger'));
			replyExcerpt.textContent = content ? excerpt(content.textContent) : '';
			setReplyMode(true);

			const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			form.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
			textarea.focus({ preventScroll: true });
		}

		list.addEventListener('click', function (event) {
			const button = event.target.closest('[data-reply-to]');
			const entry = button ? button.closest('.commentEntry') : null;
			if (!entry) {
				return;
			}
			event.preventDefault();
			startReply(entry);
		});

		cancelButton.addEventListener('click', function (event) {
			event.preventDefault();
			replyInput.value = '';
			replyName.replaceChildren();
			replyExcerpt.textContent = '';
			setReplyMode(false);
			textarea.focus();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeCommentReplies);
	} else {
		initializeCommentReplies();
	}
}());
