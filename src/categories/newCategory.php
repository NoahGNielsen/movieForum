<?php include __DIR__ . '/../assets/php/userCookieHandeling.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Lav et nyt indlæg - Pellicula Film Forum</title>
    <meta name="description" content="På denne side kan du oprette et nyt indlæg i forumet. Udfyld de nødvendige oplysninger og del dine tanker med andre filmelskere. Hvorefter du kan deltage i diskussioner og få feedback på dine indlæg.">
    <link rel="stylesheet" href="../assets/css/newCategory.css">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
</head>
<body>
    <main>
        <h1>
            Lav en ny under kategori
        </h1>
        <Form class="newCategoriForm" action="../assets/php/newCategoryHandler.php" method="post">
            <label class="newCategoriFormLabel" for="newCategoriName">Navn: </label>
            <input class="newCategoriFormInput" type="text" name="newCategoriName" id="newCategoriName" minlength="3" maxlength="35">

            <label class="newCategoriFormLabel" for="newCategoriDescription">Beskrivelse: </label>
            <input class="newCategoriFormInput" type="text" name="newCategoriDescription" id="newCategoriDescription" minlength="10" maxlength="254">

            <div class="newCategoryCategoryList">
                <label class="newCategoriFormLabel" class="newCategoriesListLabel" for="newCategoriFormListSelect">Vælg over kategori</label>
                <select class="newCategoriFormListSelect" name="newCategoriFormListSelect" id="newCategoriFormListSelect">
                    <?php include __DIR__ . '/../assets/php/allCategoriesList.php'?>
                </select>
            </div>

            <label for="acceptTerms">Jeg afgivere herved lov & tro at denne kategori opfylder forumets retningslinjer og at den ikke indeholder ulovlige eller stødende indhold. Derudover så findes en lignene kategori ikke.</label>
            <input class="newCategoriFormSubmit" type="checkbox" id="acceptTerms"></input>

            <button class="newCategoriFormSubmit" type="submit">Opret ny under kategori</button>
        </Form>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
