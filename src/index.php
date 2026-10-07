<?php include __DIR__ . '/assets/php/userCookieHandeling.php'; ?>
<?php require_once __DIR__ . '/assets/php/frontpage.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Hjem - Pellicula Film Forum</title>
    <meta name="description" content="Pellicula Film Forum: dansk forum hvor du kan diskutere film, dele anmeldelser og finde nye film at se.">
    <link rel="stylesheet" href="/assets/css/frontpage.css?v=<?= filemtime(__DIR__ . '/assets/css/frontpage.css') ?>">
    <?php include __DIR__ . '/assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/assets/php/navBar.php'; ?>
    <?php if (isset($_GET['onboarded'])): ?>
        <div class="success-toast" id="successToast" role="status">
            <p>Velkommen! Dit brugernavn er oprettet.</p>
            <button type="button" class="success-toast-close" aria-label="Luk besked">&times;</button>
        </div>
        <script src="/assets/js/successToast.js?v=<?= filemtime(__DIR__ . '/assets/js/successToast.js') ?>"></script>
    <?php endif; ?>
    <main>
        <section class="hero">
            <h1>Snak film med andre filmelskere</h1>
            <p class="hero-lead">
                Del anmeldelser, start diskussioner og find nye film at se, sorteret i kategorier, så du hurtigt finder samtalen, du leder efter.
            </p>
            <div class="hero-actions">
                <a class="btn" href="/categories/">Udforsk kategorier</a>
                <?php if ($frontpageUser === null): ?>
                    <a class="btn btn-ghost" href="/userMgmt/onboarding">Opret dit brugernavn</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="threads" aria-labelledby="threads-heading">
            <h2 class="section-heading" id="threads-heading">Aktive tråde</h2>
            <?php if ($activeThreads === null): ?>
                <p class="empty-state" role="status">Trådene kunne ikke hentes lige nu. Prøv igen om lidt.</p>
            <?php elseif (empty($activeThreads)): ?>
                <p class="empty-state">Der er endnu ingen tråde. <a href="/categories/">Vælg en kategori</a> og start den første.</p>
            <?php else: ?>
                <ol class="card-list">
                    <?php foreach ($activeThreads as $thread): ?>
                        <li class="card thread-row">
                            <div class="thread-main">
                                <h3 class="thread-title">
                                    <a href="/posts/<?= (int) $thread['id'] ?>"><?= viewEscape($thread['title']) ?></a>
                                </h3>
                                <p class="thread-meta">
                                    <?php if ($thread['category'] !== ''): ?>
                                        <a class="thread-category" href="/categories/<?= viewEscape(rawurlencode($thread['category'])) ?>"><?= viewEscape($thread['category']) ?></a>
                                    <?php endif; ?>
                                    <span><?= viewEscape(viewReplyLabel($thread['replies'])) ?></span>
                                    <span>Seneste aktivitet <?= viewTime($thread['last_activity']) ?></span>
                                </p>
                            </div>
                            <?= viewVoteScore((int) $thread['id'], $thread['upvotes'], $thread['downvotes']) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>

        <?php if ($frontpageUser === null): ?>
            <section class="features" aria-labelledby="features-heading">
                <h2 class="section-heading" id="features-heading">Sådan kommer du i gang</h2>
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
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/assets/php/footer.php'; ?>
</body>
</html>
