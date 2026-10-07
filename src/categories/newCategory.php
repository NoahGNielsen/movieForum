<?php include __DIR__ . '/../assets/php/userCookieHandeling.php'; ?>
<?php require_once __DIR__ . '/../assets/php/viewHelpers.php'; $newCategoryUser = viewCurrentUser(); ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Ny underkategori - Pellicula Film Forum</title>
    <meta name="description" content="Opret en ny underkategori på Pellicula Film Forum, så andre filmelskere kan finde og deltage i diskussionen.">
    <link rel="stylesheet" href="/assets/css/newCategory.css?v=<?= filemtime(__DIR__ . '/../assets/css/newCategory.css') ?>">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <section class="form-page">
            <nav class="form-breadcrumb" aria-label="Brødkrumme">
                <a href="/categories/">Kategorier</a>
                <span aria-hidden="true">/</span>
                <span>Ny underkategori</span>
            </nav>

            <h1>Ny underkategori</h1>
            <p class="form-intro">Giv emnet et kort navn og en beskrivelse, så andre kan se, hvad der skal diskuteres her.</p>

            <?php if ($newCategoryUser === null): ?>
                <p class="form-notice" role="status">Du skal have et brugernavn for at oprette en underkategori. <a href="/userMgmt/onboarding">Opret et her</a>.</p>
            <?php else: ?>
                <form class="card form-stack newCategoriForm" action="../assets/php/newCategoryHandler" method="post">
                    <?= csrfTokenField() ?>

                    <div class="field">
                        <label class="field-label" for="newCategoriFormListSelect">Hovedkategori</label>
                        <select class="newCategoriFormListSelect" name="newCategoriFormListSelect" id="newCategoriFormListSelect" required>
                            <?php include __DIR__ . '/../assets/php/allCategoriesList.php'?>
                        </select>
                    </div>

                    <div class="field">
                        <label class="field-label" for="newCategoriName">Navn</label>
                        <input class="newCategoriFormInput" type="text" name="newCategoriName" id="newCategoriName" minlength="3" maxlength="35" pattern="[\p{L}\p{N} .,_@:!?\(\)+&'&quot;#%*=\-]+" placeholder="Fx Christopher Nolan" aria-describedby="newCategoriNameHint" required>
                        <p class="field-hint" id="newCategoriNameHint">3-35 tegn.</p>
                    </div>

                    <div class="field">
                        <label class="field-label" for="newCategoriDescription">Beskrivelse</label>
                        <input class="newCategoriFormInput" type="text" name="newCategoriDescription" id="newCategoriDescription" minlength="10" maxlength="254" pattern="[\p{L}\p{N} .,_@:!?\(\)+&'&quot;#%*=\/;€$\-]+" placeholder="Hvad handler underkategorien om?" aria-describedby="newCategoriDescriptionHint" required>
                        <p class="field-hint" id="newCategoriDescriptionHint">10-254 tegn, på én linje.</p>
                    </div>

                    <label class="check-row" for="acceptTerms">
                        <input type="checkbox" name="acceptTerms" id="acceptTerms" value="1" required>
                        <span>Jeg bekræfter, at kategorien følger forumets <a href="/legal/terms" target="_blank">retningslinjer</a>, ikke indeholder ulovligt eller stødende indhold, og at der ikke allerede findes en lignende kategori.</span>
                    </label>

                    <div class="form-actions">
                        <button class="btn newCategoriFormSubmit" type="submit">Opret underkategori</button>
                        <a class="form-cancel" href="/categories/">Annullér</a>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
