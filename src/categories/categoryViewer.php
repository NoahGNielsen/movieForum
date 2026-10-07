<?php require_once __DIR__ . '/../assets/php/categoryViewer.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title><?= $escape($pageTitle) ?> - Pellicula Film Forum</title>
    <meta name="description" content="<?= $escape($pageDescription) ?>">
    <link rel="stylesheet" href="/assets/css/categoryViewer.css?v=<?= filemtime(__DIR__ . '/../assets/css/categoryViewer.css') ?>">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <?php if ($category === null): ?>
            <section class="category-message" role="status">
                <h1><?= $errorHeading ?></h1>
                <p><?= $errorMessage ?></p>
            </section>
        <?php else: ?>
            <header class="category-heading">
                <div class="category-heading-text">
                    <p class="category-eyebrow">Kategori</p>
                    <h1><?= $escape($category['name']) ?></h1>
                    <?php if (!empty($category['description'])): ?>
                        <p><?= $escape($category['description']) ?></p>
                    <?php endif; ?>
                </div>
                <a class="btn" href="https://forum.noahgajnielsen.dk/categories/<?= $escape(rawurlencode($category['name'])) ?>?newPost=true">Lav et nyt indlæg</a>
            </header>

            <div class="category-layout<?= $category['is_top'] ? ' has-subcategories' : '' ?>">
                <section class="post-list" aria-labelledby="posts-heading">
                    <h2 id="posts-heading">Indlæg</h2>
                    <?php if (empty($posts)): ?>
                        <p class="empty-state">Der er endnu ingen indlæg i denne kategori.</p>
                    <?php else: ?>
                        <ol class="card-list">
                            <?php foreach ($posts as $post): ?>
                                <li>
                                    <article class="card post-card">
                                        <header class="post-card-head">
                                            <?= viewAvatar($post['username'], 'md', $post['avatar_id']) ?>
                                            <p class="post-card-byline">
                                                <?= viewUserName($post['username']) ?>
                                                <?= viewTime($post['timestamp']) ?>
                                            </p>
                                        </header>
                                        <h3 class="post-card-title">
                                            <a href="/posts/<?= (int) $post['id'] ?>"><?= $escape($post['title']) ?></a>
                                        </h3>
                                        <?php if (trim((string) $post['content']) !== ''): ?>
                                            <p class="post-card-body"><?= $escape(viewBreakLongWords(viewExcerpt($post['content']))) ?></p>
                                        <?php endif; ?>
                                        <footer class="post-card-actions">
                                            <a class="pill" href="/posts/<?= (int) $post['id'] ?>#comments"><?= viewReplyIcon() ?><?= $escape(viewReplyLabel((int) $post['comment_count'])) ?></a>
                                            <?= viewShareButton('/posts/' . (int) $post['id'], (string) $post['title']) ?>
                                        </footer>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </section>

                <?php if ($category['is_top']): ?>
                    <aside class="subcategory-list" aria-labelledby="subcategories-heading">
                        <div class="subcategory-heading">
                            <h2 id="subcategories-heading">Underkategorier</h2>
                            <a class="new-subcategory-link" href="/categories/newCategory?parentId=<?= (int) $category['id'] ?>" aria-label="Lav en ny underkategori" title="Lav en ny underkategori">+</a>
                        </div>
                        <?php if (empty($subcategories)): ?>
                            <p class="empty-state">Der er endnu ingen underkategorier.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($subcategories as $subcategory): ?>
                                    <li>
                                        <a href="/categories/<?= $escape(rawurlencode($subcategory['name'])) ?>"><?= $escape($subcategory['name']) ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </aside>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
