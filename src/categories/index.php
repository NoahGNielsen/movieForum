<?php include __DIR__ . '/../assets/php/userCookieHandeling.php'; ?>
<?php require_once __DIR__ . '/../assets/php/categoryDirectory.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Kategorier - Pellicula Film Forum</title>
    <meta name="description" content="På denne side kan du finde forskellige kategorier, hvor du kan deltage i diskussioner om film. Vælg en kategori for at udforske de tilgængelige emner og deltage i samtaler med andre filmelskere.">
    <link rel="stylesheet" href="/assets/css/allCategories.css?v=<?= filemtime(__DIR__ . '/../assets/css/allCategories.css') ?>">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <header class="directory-heading">
            <h1>Kategorier</h1>
            <p>Vælg en kategori for at finde diskussioner, eller gå direkte til en underkategori.</p>
        </header>

        <?php if ($categoryDirectory === null): ?>
            <p class="empty-state" role="status">Kategorierne kunne ikke hentes lige nu. Prøv igen om lidt.</p>
        <?php elseif (empty($categoryDirectory)): ?>
            <p class="empty-state">Der er endnu ingen kategorier.</p>
        <?php else: ?>
            <div class="directory">
                <?php foreach ($categoryDirectory as $category): ?>
                    <section class="card directory-group" aria-labelledby="category-<?= $category['id'] ?>">
                        <header class="directory-group-head">
                            <div class="directory-group-titlebar">
                                <h2 class="directory-group-title" id="category-<?= $category['id'] ?>">
                                    <a href="<?= viewEscape(directoryCategoryUrl($category['name'])) ?>"><?= viewEscape($category['name']) ?></a>
                                </h2>
                                <a class="pill directory-add" href="/categories/newCategory?parentId=<?= $category['id'] ?>">
                                    <span aria-hidden="true">+</span> Ny underkategori<span class="visually-hidden"> i <?= viewEscape($category['name']) ?></span>
                                </a>
                            </div>
                            <p class="directory-group-meta">
                                <?= $category['posts'] === 1 ? '1 opslag' : $category['posts'] . ' opslag' ?>
                                · <?= count($category['subcategories']) === 1 ? '1 underkategori' : count($category['subcategories']) . ' underkategorier' ?>
                            </p>
                            <?php if ($category['description'] !== ''): ?>
                                <p class="directory-group-description"><?= viewEscape($category['description']) ?></p>
                            <?php endif; ?>
                        </header>

                        <?php if (empty($category['subcategories'])): ?>
                            <p class="empty-state directory-empty">Ingen underkategorier endnu.</p>
                        <?php else: ?>
                        <ul class="chip-grid">
                            <?php foreach ($category['subcategories'] as $subcategory): ?>
                                <li>
                                    <a class="chip" href="<?= viewEscape(directoryCategoryUrl($subcategory['name'])) ?>"<?= $subcategory['description'] !== '' ? ' title="' . viewEscape($subcategory['description']) . '"' : '' ?>>
                                        <?= viewEscape($subcategory['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
