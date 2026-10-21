<?php
require_once __DIR__ . '/../assets/php/userCookieHandeling.php';
$newPost = require __DIR__ . '/../assets/php/newPost.php';
$category = $newPost['category'];
$values = $newPost['values'];
$limits = $newPost['limits'];
if ($category === null) {
    http_response_code($newPost['error'] === 'database' ? 500 : 404);
}
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Lav et nyt indlæg<?= $category ? ' i ' . $escape($category['name']) : '' ?> - Pellicula Film Forum</title>
    <meta name="description" content="På denne side kan du oprette et nyt indlæg i forumet. Udfyld de nødvendige oplysninger og del dine tanker med andre filmelskere. Hvorefter du kan deltage i diskussioner og få feedback på dine indlæg.">
    <link rel="stylesheet" href="/assets/css/newPost.css?v=<?= filemtime(__DIR__ . '/../assets/css/newPost.css') ?>">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
    <script src="/assets/js/newPostImages.js?v=<?= filemtime(__DIR__ . '/../assets/js/newPostImages.js') ?>" defer></script>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <?php if ($category === null): ?>
            <section class="newPostMessage" role="status">
                <h1><?= $newPost['error'] === 'database' ? 'Siden kunne ikke indlæses' : 'Kategorien blev ikke fundet' ?></h1>
                <p><?= $newPost['error'] === 'database' ? 'Der opstod en fejl ved forbindelsen til databasen.' : 'Du kan kun oprette indlæg i en kategori, der findes. Vælg en kategori fra oversigten.' ?></p>
                <a href="/categories/">Gå til kategorier</a>
            </section>
        <?php else: ?>
            <h1>Lav et nyt indlæg</h1>
            <p class="newPostCategory">
                I kategorien <a href="/categories/<?= $escape(rawurlencode($category['name'])) ?>"><?= $escape($category['name']) ?></a>
            </p>

            <?php if (!$newPost['is_registered']): ?>
                <p class="newPostNotice" role="status">
                    Du skal have en registreret bruger for at oprette et indlæg. <a href="/userMgmt/onboarding">Opret en bruger her</a>.
                </p>
            <?php endif; ?>

            <?php if (!empty($newPost['errors'])): ?>
                <ul class="newPostErrors" role="alert">
                    <?php foreach ($newPost['errors'] as $error): ?>
                        <li><?= $escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form class="newPostForm" action="" method="post" enctype="multipart/form-data">
                <?= csrfTokenField() ?>
                <label class="newPostFormLabel" for="newPostTitle">Titel: </label>
                <input class="newPostFormInput" type="text" name="newPostTitle" id="newPostTitle" minlength="<?= (int) $limits['title_min'] ?>" maxlength="<?= (int) $limits['title_max'] ?>" value="<?= $escape($values['title']) ?>" required>

                <label class="newPostFormLabel" for="newPostContent">Indhold: </label>
                <textarea class="newPostFormInput newPostFormTextarea" name="newPostContent" id="newPostContent" rows="10" minlength="<?= (int) $limits['content_min'] ?>" maxlength="<?= (int) $limits['content_max'] ?>" required><?= $escape($values['content']) ?></textarea>

                <label class="newPostFormLabel" for="newPostImages">Billeder (valgfrit): </label>
                <input class="newPostFormInput" type="file" name="newPostImages[]" id="newPostImages" accept="image/jpeg,image/png,image/gif,image/webp" multiple data-max-count="<?= (int) $limits['images_max'] ?>" data-max-bytes="<?= (int) $limits['image_bytes_max'] ?>" aria-describedby="newPostImagesHint">
                <p class="newPostFormHint" id="newPostImagesHint">Op til <?= (int) $limits['images_max'] ?> billeder i JPG, PNG, GIF eller WebP på højst 4 MB hver.<?= empty($newPost['errors']) ? '' : ' Vælg billederne igen, hvis du havde valgt nogen.' ?></p>
                <div class="newPostImagePreview" id="newPostImagePreview" hidden></div>

                <div class="newPostFormTerms">
                    <input type="checkbox" name="acceptTerms" id="acceptTerms" value="1" required>
                    <label for="acceptTerms">Jeg afgiver herved tro og love på, at dette indlæg opfylder forumets retningslinjer og ikke indeholder ulovligt eller stødende indhold.</label>
                </div>

                <button class="newPostFormSubmit" type="submit"<?= $newPost['is_registered'] ? '' : ' disabled' ?>>Opret indlæg</button>
            </form>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
