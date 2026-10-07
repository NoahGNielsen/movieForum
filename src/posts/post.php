<?php require_once __DIR__ . '/../assets/php/postViewer.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title><?= $escape($pageTitle) ?> - Pellicula Film Forum</title>
    <meta name="description" content="<?= $escape($pageDescription) ?>">
    <link rel="stylesheet" href="/assets/css/post.css?v=<?= filemtime(__DIR__ . '/../assets/css/post.css') ?>">
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
            <?php if (!empty($post['category'])): ?>
                <nav class="postBreadcrumb" aria-label="Brødkrumme">
                    <a href="/categories/">Kategorier</a>
                    <span aria-hidden="true">/</span>
                    <a href="/categories/<?= $escape(rawurlencode($post['category'])) ?>"><?= $escape($post['category']) ?></a>
                </nav>
            <?php endif; ?>

            <article class="card post-card postArticle">
                <header class="post-card-head">
                    <?= viewAvatar($post['username']) ?>
                    <p class="post-card-byline">
                        <?= viewUserName($post['username']) ?>
                        <?= viewTime($post['timestamp']) ?>
                    </p>
                </header>
                <h1 class="postTitle"><?= $escape($post['title']) ?></h1>
                <?php if ($post['body'] !== ''): ?>
                    <p class="post-card-body postBody"><?= $escape($post['body']) ?></p>
                <?php endif; ?>
                <footer class="post-card-actions">
                    <a class="pill" href="#comments"><?= viewReplyIcon() ?><span id="replyCountLabel"><?= $escape(viewReplyLabel((int) $totalComments)) ?></span></a>
                    <?= viewShareButton('/posts/' . (int) $post['id'], $post['title']) ?>
                </footer>
            </article>

            <section class="postComments" id="comments" aria-labelledby="commentsHeading">
                <h2 class="visually-hidden" id="commentsHeading">Kommentarer (<?= (int) $totalComments ?>)</h2>

                <?php if (!empty($newComment['errors'])): ?>
                    <ul class="postErrors" role="alert">
                        <?php foreach ($newComment['errors'] as $error): ?>
                            <li><?= $escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!$newComment['is_registered']): ?>
                    <p class="postNotice" role="status">
                        Du skal have et brugernavn for at skrive en kommentar. <a href="/userMgmt/onboarding">Opret et her</a>.
                    </p>
                <?php else: ?>
                    <form class="card commentForm" action="/posts/<?= (int) $post['id'] ?>" method="post">
                        <?= csrfTokenField() ?>
                        <label class="visually-hidden" for="newCommentContent">Skriv en kommentar</label>
                        <textarea class="commentFormInput" name="newCommentContent" id="newCommentContent" rows="3" placeholder="Skriv en kommentar" minlength="<?= (int) $commentLimits['content_min'] ?>" maxlength="<?= (int) $commentLimits['content_max'] ?>" required><?= $escape($commentValues['content']) ?></textarea>
                        <button class="btn commentFormSubmit" type="submit">Send kommentar</button>
                    </form>
                <?php endif; ?>

                <?php if (empty($comments)): ?>
                    <p class="empty-state" id="commentsEmptyState">Der er endnu ingen kommentarer. Vær den første til at kommentere.</p>
                <?php endif; ?>

                <?php // Above the list, since new comments are added at the top. ?>
                <div class="newCommentsRegion" aria-live="polite">
                    <button class="newCommentsButton" id="newCommentsButton" type="button" hidden></button>
                </div>

                <?php // Always rendered (hidden when empty) so commentPoller.js has a list to add new comments to.
                      // commentPoller.js builds the same card markup in buildComment(); keep the two in sync. ?>
                <ol class="card-list commentList" id="commentList" data-post-id="<?= (int) $post['id'] ?>" data-last-comment-id="<?= (int) $lastCommentId ?>" data-total-comments="<?= (int) $totalComments ?>" data-comment-limit="<?= COMMENT_DISPLAY_LIMIT ?>"<?= empty($comments) ? ' hidden' : '' ?>>
                    <?php foreach ($comments as $comment): ?>
                        <li class="card commentEntry" id="comment-<?= (int) $comment['id'] ?>">
                            <header class="post-card-head">
                                <?= viewAvatar($comment['username'], 'sm') ?>
                                <p class="post-card-byline">
                                    <?= viewUserName($comment['username']) ?>
                                    <a class="commentPermalink" href="#comment-<?= (int) $comment['id'] ?>"><?= viewTime($comment['timestamp']) ?></a>
                                </p>
                            </header>
                            <p class="commentContent"><?= $escape($comment['content']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <button class="pill loadMoreCommentsButton" id="loadMoreCommentsButton" type="button"<?= $totalComments > count($comments) ? '' : ' hidden' ?>>Vis flere kommentarer</button>
            </section>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
