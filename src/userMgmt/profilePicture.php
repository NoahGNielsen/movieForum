<?php
require_once __DIR__ . '/../assets/php/viewHelpers.php';

$message = '';
$messageType = '';
$currentUser = viewCurrentUser();

$statusMessages = [
    'saved' => 'Dit profilbillede er gemt.',
    'removed' => 'Dit profilbillede er fjernet.',
];
$status = $_GET['status'] ?? '';
if (is_string($status) && isset($statusMessages[$status])) {
    $message = $statusMessages[$status];
    $messageType = 'success';
}

if ($currentUser !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messageType = 'error';

    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // Above post_max_size PHP drops the whole request body, the CSRF token included.
        $message = 'Billedet må højst være 4 MB.';
    } elseif (!isValidCsrfRequest()) {
        $message = 'Formularen er udløbet. Prøv igen.';
    } elseif ($action === 'remove' || $action === 'upload') {
        $picture = $action === 'upload' ? profilePictureFromUpload($_FILES['profile_picture'] ?? null) : null;

        if (isset($picture['error'])) {
            $message = $picture['error'];
        } elseif (($conn = viewDbConnect()) === null) {
            $message = 'Databasefejl: Kan ikke forbinde til databasen.';
        } else {
            $done = $action === 'upload'
                ? saveProfilePicture($conn, $currentUser['id'], $picture['data'])
                : removeProfilePicture($conn, $currentUser['id']);
            $conn->close();

            if ($done) {
                // Redirect so a refresh doesn't upload again, and the navbar shows the new picture.
                header('Location: /userMgmt/profilePicture?status=' . ($action === 'upload' ? 'saved' : 'removed'), true, 303);
                exit;
            }
            $message = 'Der opstod en fejl ved at gemme dit profilbillede. Prøv igen senere.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Profilbillede - Pellicula Film Forum</title>
    <meta name="description" content="Upload eller fjern dit profilbillede.">
    <link rel="stylesheet" href="/assets/css/userMgmt.css?v=<?= filemtime(__DIR__ . '/../assets/css/userMgmt.css') ?>">
    <?php include __DIR__ . '/../assets/php/header.php'; ?>
    <script src="/assets/js/profilePicture.js?v=<?= filemtime(__DIR__ . '/../assets/js/profilePicture.js') ?>" defer></script>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <section class="profile-picture-page">
            <h1>Profilbillede</h1>

            <?php if ($message !== ''): ?>
                <p class="message <?= viewEscape($messageType) ?>" role="<?= $messageType === 'error' ? 'alert' : 'status' ?>"><?= viewEscape($message) ?></p>
            <?php endif; ?>

            <?php if ($currentUser === null): ?>
                <p>Vælg et brugernavn, før du tilføjer et profilbillede.</p>
                <a class="btn" href="/userMgmt/onboarding">Opret dit brugernavn</a>
            <?php else: ?>
                <div class="card profile-picture-card">
                    <div class="profile-picture-preview" data-profile-picture-preview>
                        <?= viewAvatar($currentUser['name'], 'lg', $currentUser['avatar_id']) ?>
                    </div>

                    <form method="post" enctype="multipart/form-data" class="profile-picture-form">
                        <?= csrfTokenField() ?>
                        <input type="hidden" name="action" value="upload">
                        <label for="profile_picture">Vælg et billede</label>
                        <input
                            type="file"
                            id="profile_picture"
                            name="profile_picture"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            data-max-bytes="<?= PROFILE_PICTURE_MAX_BYTES ?>"
                            aria-describedby="profilePictureHint"
                            required
                        >
                        <p class="form-hint" id="profilePictureHint">JPG, PNG, GIF eller WebP på højst 4 MB. Billedet bliver gjort mindre, så det højst er <?= PROFILE_PICTURE_MAX_SIDE ?> × <?= PROFILE_PICTURE_MAX_SIDE ?> px.</p>
                        <button type="submit" class="btn">Gem profilbillede</button>
                    </form>

                    <?php if ($currentUser['avatar_id'] !== null): ?>
                        <form method="post" class="profile-picture-remove">
                            <?= csrfTokenField() ?>
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="btn btn-ghost">Fjern profilbillede</button>
                        </form>
                    <?php endif; ?>
                </div>

                <p><a href="/profile">Tilbage til din profil</a></p>
            <?php endif; ?>
        </section>
    </main>
    <?php include __DIR__ . '/../assets/php/footer.php'; ?>
</body>
</html>
