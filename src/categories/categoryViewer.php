<?php require_once __DIR__ . '/../assets/php/categoryViewer.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title><?= $escape($pageTitle) ?> - Pellicula Film Forum</title>
    <meta name="description" content="<?= $escape($pageDescription) ?>">
    <link rel="stylesheet" href="../assets/css/categoryViewer.css?v=<?= filemtime(__DIR__ . '/../css/categoryViewer.css') ?>">
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
                <a class="new-post-link" href="https://forum.noahgajnielsen.dk/categories/<?= $escape(rawurlencode($category['name'])) ?>?newPost=true">Lav et nyt indlæg</a>
            </header>

            <div class="category-layout<?= $category['is_top'] ? ' has-subcategories' : '' ?>">
                <section class="post-list" aria-labelledby="posts-heading">
                    <h2 id="posts-heading">Indlæg</h2>
                    <?php if (empty($posts)): ?>
                        <p class="empty-state">Der er endnu ingen indlæg i denne kategori.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <article class="post-entry">
                                <h3 class="post-title"><?= $escape($post['title']) ?></h3>
                                <p class="post-content"><?= nl2br($escape($post['content'])) ?></p>
                                <footer class="post-meta">
                                    <span>Skrevet af <?= $escape($post['username']) ?></span>
                                    <span><?= (int) $post['comment_count'] ?> kommentarer</span>
                                    <?php if (!empty($post['timestamp'])): ?>
                                        <time datetime="<?= $escape($post['timestamp']) ?>"><?= $escape($post['timestamp']) ?></time>
                                    <?php endif; ?>
                                </footer>
                                <a class="post-link" href="https://forum.noahgajnielsen.dk/posts/<?= (int) $post['id'] ?>">Gå til indlæg</a>
                            </article>
                        <?php endforeach; ?>
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
