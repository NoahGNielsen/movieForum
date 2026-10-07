<?php
require_once __DIR__ . '/viewHelpers.php';
$navUser = viewCurrentUser();
$navPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navCurrent = static fn (string $prefix): string => ($prefix === '/' ? $navPath === '/' : str_starts_with($navPath, $prefix)) ? ' aria-current="page"' : '';
?>
<?php include __DIR__ . '/newUserPopUp.php'; ?>
<header class="topbar">
    <div class="topbar-inner">
        <a class="topbar-brand" href="https://forum.noahgajnielsen.dk/">
            <img src="https://forum.noahgajnielsen.dk/assets/images/navBarLogo-dark.webp" alt="Pellicula Film Forum - forside" class="topbar-logo" width="175" height="40">
        </a>

        <nav class="topbar-nav" id="mainMenu" aria-label="Hovedmenu">
            <ul class="topbar-list">
                <li><a class="topbar-link" href="https://forum.noahgajnielsen.dk/"<?= $navCurrent('/') ?>>Forside</a></li>
                <li><a class="topbar-link" href="https://forum.noahgajnielsen.dk/categories/"<?= $navCurrent('/categories') ?>>Kategorier</a></li>
                <?php if ($navUser !== null): ?>
                    <li class="topbar-menu-only"><a class="topbar-link" href="https://forum.noahgajnielsen.dk/profile"<?= $navCurrent('/profile') ?>>Min profil</a></li>
                    <li class="topbar-menu-only"><a class="topbar-link" href="https://forum.noahgajnielsen.dk/userMgmt/changeUsername"<?= $navCurrent('/userMgmt/changeUsername') ?>>Skift brugernavn</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="topbar-actions">
            <?php if ($navUser !== null): ?>
                <a class="topbar-profile" href="https://forum.noahgajnielsen.dk/profile" title="Min profil">
                    <?= viewAvatar($navUser['name'], 'sm') ?>
                    <span class="visually-hidden">Min profil</span>
                </a>
            <?php else: ?>
                <a class="topbar-cta" href="https://forum.noahgajnielsen.dk/userMgmt/onboarding">Kom i gang</a>
            <?php endif; ?>
            <button class="topbar-menu-button" type="button" aria-expanded="false" aria-controls="mainMenu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <span class="visually-hidden">Menu</span>
            </button>
        </div>
    </div>
</header>
