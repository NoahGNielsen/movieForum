<?php include __DIR__ . '/assets/php/userCookieHandeling.php'; ?>
<?php require_once __DIR__ . '/assets/php/profileData.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Min profil - Pellicula Film Forum</title>
    <meta name="description" content="Din profil på Pellicula Film Forum med dine seneste opslag og kommentarer.">
    <link rel="stylesheet" href="/assets/css/userProfile.css?v=<?= filemtime(__DIR__ . '/assets/css/userProfile.css') ?>">
    <?php include __DIR__ . '/assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/assets/php/navBar.php'; ?>
    <main>
        <?php if ($profileUser === null): ?>
            <section class="profile-message">
                <h1>Du har ikke en profil endnu</h1>
                <p>Vælg et brugernavn for at skrive opslag og kommentarer. Så samles de her.</p>
                <a class="btn" href="/userMgmt/onboarding">Opret dit brugernavn</a>
            </section>
        <?php else: ?>
            <header class="card profile-header">
                <?= viewAvatar($profileUser['name'], 'lg') ?>
                <div class="profile-identity">
                    <h1 class="profile-name"><?= viewUserName($profileUser['name']) ?></h1>
                    <?php if (!empty($profileUser['last_seen'])): ?>
                        <p class="profile-meta">Sidst aktiv <?= viewTime($profileUser['last_seen']) ?></p>
                    <?php endif; ?>
                    <a class="pill" href="/userMgmt/changeUsername">Skift brugernavn</a>
                </div>
            </header>

            <?php if ($profile === null): ?>
                <p class="empty-state" role="status">Din aktivitet kunne ikke hentes lige nu. Prøv igen om lidt.</p>
            <?php else: ?>
                <dl class="profile-stats">
                    <div class="card profile-stat">
                        <dt>Opslag</dt>
                        <dd><?= (int) $profile['counts']['posts'] ?></dd>
                    </div>
                    <div class="card profile-stat">
                        <dt>Kommentarer</dt>
                        <dd><?= (int) $profile['counts']['comments'] ?></dd>
                    </div>
                </dl>

                <section aria-labelledby="activity-heading">
                    <h2 class="section-heading" id="activity-heading">Seneste opslag og kommentarer</h2>
                    <?php if (empty($profile['activity'])): ?>
                        <p class="empty-state">Du har ikke skrevet noget endnu. <a href="/categories/">Find en kategori</a> og kom i gang.</p>
                    <?php else: ?>
                        <ol class="card-list">
                            <?php foreach ($profile['activity'] as $item): ?>
                                <?php $itemUrl = '/posts/' . $item['post_id'] . ($item['kind'] === 'comment' ? '#comment-' . $item['comment_id'] : ''); ?>
                                <li class="card post-card profile-activity">
                                    <p class="post-card-byline">
                                        <span class="post-card-context"><?= $item['kind'] === 'post' ? 'Opslag' : 'Kommentar til' ?></span>
                                        <?= viewTime($item['timestamp']) ?>
                                    </p>
                                    <h3 class="post-card-title">
                                        <a href="<?= viewEscape($itemUrl) ?>"><?= viewEscape($item['post_title']) ?></a>
                                    </h3>
                                    <?php if (trim((string) $item['content']) !== ''): ?>
                                        <p class="post-card-body"><?= viewEscape(viewExcerpt($item['content'], 180)) ?></p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/assets/php/footer.php'; ?>
</body>
</html>
