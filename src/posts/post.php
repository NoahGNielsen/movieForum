<?php require_once __DIR__ . '/../assets/php/postViewer.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title><?= $escape($pageTitle) ?> - Pellicula Film Forum</title>
    <meta name="description" content="<?= $escape($pageDescription) ?>">
    <link rel="stylesheet" href="/assets/css/post.css">
    <script src="/assets/js/commentPoller.js?v=<?= filemtime(__DIR__ . '/../assets/js/commentPoller.js') ?>" defer></script>
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <?php if ($post === null): ?>
            <section class="postMessage" role="status">
                <h1><?= $postError === 'database' ? 'Siden kunne ikke indlæses' : 'Indlægget blev ikke fundet' ?></h1>
                <p><?= $postError === 'database' ? 'Der opstod en fejl ved forbindelsen til databasen.' : 'Indlægget findes ikke, eller det er blevet slettet.' ?></p>
                <a href="/categories/">Gå til kategorier</a>
            </section>
        <?php else: ?>
            <article class="postArticle">
                <header class="postHeading">
                    <?php if (!empty($post['category'])): ?>
                        <a class="postCategory" href="/categories/<?= $escape(rawurlencode($post['category'])) ?>"><?= $escape($post['category']) ?></a>
                    <?php endif; ?>
                    <h1><?= $escape($post['title']) ?></h1>
                    <p class="postMeta">
                        <span>Skrevet af <?= $escape($post['username']) ?></span>
                        <?php if (!empty($post['timestamp'])): ?>
                            <time datetime="<?= $escape($post['timestamp']) ?>"><?= $escape($formatTime($post['timestamp'])) ?></time>
                        <?php endif; ?>
                    </p>
                </header>
                <?php if ($post['body'] !== ''): ?>
                    <p class="postBody"><?= $escape($post['body']) ?></p>
                <?php endif; ?>
            </article>

            <section class="postComments" aria-labelledby="commentsHeading">
                <h2 id="commentsHeading">Kommentarer (<?= (int) $totalComments ?>)</h2>

                <?php if (empty($comments)): ?>
                    <p class="postEmptyState" id="commentsEmptyState">Der er endnu ingen kommentarer. Vær den første til at kommentere.</p>
                <?php endif; ?>

                <?php // Above the list, since new comments are added at the top. ?>
                <div aria-live="polite">
                    <button class="newCommentsButton" id="newCommentsButton" type="button" hidden></button>
                </div>

                <?php // Always rendered (hidden when empty) so commentPoller.js has a list to add new comments to. ?>
                <ol class="commentList" id="commentList" data-post-id="<?= (int) $post['id'] ?>" data-last-comment-id="<?= (int) $lastCommentId ?>" data-total-comments="<?= (int) $totalComments ?>" data-comment-limit="<?= COMMENT_DISPLAY_LIMIT ?>"<?= empty($comments) ? ' hidden' : '' ?>>
                    <?php foreach ($comments as $comment): ?>
                        <li class="commentEntry" id="comment-<?= (int) $comment['id'] ?>">
                            <p class="postMeta">
                                <span><?= $escape($comment['username']) ?></span>
                                <?php if (!empty($comment['timestamp'])): ?>
                                    <time datetime="<?= $escape($comment['timestamp']) ?>"><?= $escape($formatTime($comment['timestamp'])) ?></time>
                                <?php endif; ?>
                            </p>
                            <p class="commentContent"><?= $escape($comment['content']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <button class="loadMoreCommentsButton" id="loadMoreCommentsButton" type="button"<?= $totalComments > count($comments) ? '' : ' hidden' ?>>Vis flere kommentarer</button>

                <h3>Skriv en kommentar</h3>

                <?php if (!$newComment['is_registered']): ?>
                    <p class="postNotice" role="status">
                        Du skal have en registreret bruger for at skrive en kommentar. <a href="/userMgmt/onboarding">Opret en bruger her</a>.
                    </p>
                <?php endif; ?>

                <?php if (!empty($newComment['errors'])): ?>
                    <ul class="postErrors" role="alert">
                        <?php foreach ($newComment['errors'] as $error): ?>
                            <li><?= $escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form class="commentForm" action="/posts/<?= (int) $post['id'] ?>" method="post">
                    <?= csrfTokenField() ?>
                    <label class="commentFormLabel" for="newCommentContent">Kommentar: </label>
                    <textarea class="commentFormInput" name="newCommentContent" id="newCommentContent" rows="5" minlength="<?= (int) $commentLimits['content_min'] ?>" maxlength="<?= (int) $commentLimits['content_max'] ?>" required<?= $newComment['is_registered'] ? '' : ' disabled' ?>><?= $escape($commentValues['content']) ?></textarea>
                    <button class="commentFormSubmit" type="submit"<?= $newComment['is_registered'] ? '' : ' disabled' ?>>Send kommentar</button>
                </form>
            </section>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
