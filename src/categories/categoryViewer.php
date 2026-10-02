<?php
require_once __DIR__ . '/../assets/php/userCookieHandeling.php';
$categoryViewer = require __DIR__ . '/../assets/php/categoryViewer.php';
$category = $categoryViewer['category'] ?? null;
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$postSlug = static function ($content): string {
    $title = trim(explode("\n", (string) $content, 2)[0]);
    $title = function_exists('mb_substr') ? mb_substr($title, 0, 80, 'UTF-8') : substr($title, 0, 80);
    $title = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
    return trim(preg_replace('/[^\pL\pN]+/u', '-', $title) ?? '', '-');
};
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title><?php echo $category ? $escape($category['name']) : 'Kategori ikke fundet' ?> - Pellicula Film Forum</title>
    <meta name="description" content="<?php echo $category ? $escape($category['description'] ?? '') : 'Kategorien kunne ikke findes.' ?>">
    <link rel="stylesheet" href="../assets/css/categoryViewer.css">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <?php if ($category === null): ?>
            <section class="category-message" role="status">
                <h1><?php echo ($categoryViewer['error'] ?? '') === 'database' ? 'Siden kunne ikke indlæses' : 'Kategorien blev ikke fundet' ?></h1>
                <p><?php echo ($categoryViewer['error'] ?? '') === 'database' ? 'Der opstod en fejl ved forbindelsen til databasen.' : 'Kontrollér adressen, eller vælg en kategori fra oversigten.' ?></p>
            </section>
        <?php else: ?>
            <header class="category-heading">
                <p class="category-eyebrow">Kategori</p>
                <h1><?php echo $escape($category['name']) ?></h1>
                <?php if (!empty($category['description'])): ?>
                    <p><?php echo $escape($category['description']) ?></p>
                <?php endif; ?>
            </header>

            <div class="category-layout<?= $category['is_top'] ? ' has-subcategories' : '' ?>">
                <section class="post-list" aria-labelledby="posts-heading">
                    <h2 id="posts-heading">Indlæg</h2>
                    <?php if (empty($categoryViewer['posts'])): ?>
                        <p class="empty-state">Der er endnu ingen indlæg i denne kategori.</p>
                    <?php else: ?>
                        <?php foreach ($categoryViewer['posts'] as $post): ?>
                            <article class="post-entry">
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
                        <h2 id="subcategories-heading">Underkategorier</h2>
                        <?php if (empty($categoryViewer['subcategories'])): ?>
                            <p class="empty-state">Der er endnu ingen underkategorier.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($categoryViewer['subcategories'] as $subcategory): ?>
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