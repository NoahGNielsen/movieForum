<?php include __DIR__ . '/assets/php/userCookieHandeling.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Hjem - Pellicula Film Forum</title>
    <meta name="description" content="Pellicula Film Forum: dansk forum hvor du kan diskutere film, dele anmeldelser og finde nye film at se.">
    <link rel="stylesheet" href="/assets/css/frontpage.css">
    <?php include __DIR__ . '/assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/assets/php/navBar.php'; ?>
    <main>
        <section class="hero">
            <h1>Snak film med andre filmelskere</h1>
            <p class="hero-lead">
                Pellicula er stedet, hvor du kan dele anmeldelser, starte diskussioner og finde nye film at se, sorteret i kategorier, så du hurtigt finder samtalen, du leder efter.
            </p>
            <div class="hero-actions">
                <a class="btn" href="/categories/">Udforsk kategorier</a>
                <a class="btn btn-ghost" href="/userMgmt/onboarding">Opret dit brugernavn</a>
            </div>
        </section>

        <section class="features" aria-labelledby="features-heading">
            <h2 id="features-heading">Sådan kommer du i gang</h2>
            <ol class="feature-grid">
                <li class="feature-card">
                    <span class="feature-step" aria-hidden="true">1</span>
                    <h3>Vælg et brugernavn</h3>
                    <p>Du behøver ingen lang tilmelding. Vælg et navn, og du er klar.</p>
                </li>
                <li class="feature-card">
                    <span class="feature-step" aria-hidden="true">2</span>
                    <h3>Find din kategori</h3>
                    <p>Gå på opdagelse i genrer og underkategorier, og find de emner, der interesserer dig.</p>
                </li>
                <li class="feature-card">
                    <span class="feature-step" aria-hidden="true">3</span>
                    <h3>Skriv og kommentér</h3>
                    <p>Del din mening i et indlæg, eller svar på andres i kommentarsporet.</p>
                </li>
            </ol>
        </section>

        <p class="project-note">Pellicula er et skoleprojekt, der er under udvikling.</p>
    </main>
    <?php include __DIR__ . '/assets/php/footer.php'; ?>
</body>
</html>
