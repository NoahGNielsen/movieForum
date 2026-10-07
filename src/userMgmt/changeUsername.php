<?php
require_once '../assets/php/userCookieHandeling.php';
require_once __DIR__ . '/../assets/php/viewHelpers.php';
require '../../config.php';

$message = '';
$messageType = '';
$currentUsername = '';

// Check if user has valid session
$userId = $_COOKIE['user_session_cookie'] ?? '';

if (!preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $userId) || !isUserSessionCookieInUse($userId)) {
    $message = 'Du har ingen aktiv session. Venligst opret en bruger først.';
    $messageType = 'error';
} else {
    // Get database connection
    $servername = $GLOBALS['db_config']['servername'];
    $username = $GLOBALS['db_config']['username'];
    $password = $GLOBALS['db_config']['password'];
    $dbname = $GLOBALS['db_config']['dbname'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $message = 'Databasefejl: Kan ikke forbinde til databasen.';
        $messageType = 'error';
    } else {
        // Get current username
        $getUserQuery = $conn->prepare('SELECT userName FROM Users WHERE userId = ? LIMIT 1');
        $getUserQuery->bind_param('s', $userId);
        $getUserQuery->execute();
        $result = $getUserQuery->get_result();

        if ($result->num_rows === 0) {
            $message = 'Bruger ikke fundet. Venligst opret en ny bruger.';
            $messageType = 'error';
        } else {
            $row = $result->fetch_assoc();
            $currentUsername = $row['userName'];
        }
        $getUserQuery->close();

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username']) && is_string($_POST['new_username'])) {
            $newUsernameInput = trim($_POST['new_username']);

            // Validate username
            if (!isValidCsrfRequest()) {
                $message = 'Formularen er udløbet. Prøv igen.';
                $messageType = 'error';
            } elseif (empty($newUsernameInput)) {
                $message = 'Brugernavnet kan ikke være tomt.';
                $messageType = 'error';
            } elseif (!preg_match('/^[A-Za-z0-9.,_@:!?()+&-]+$/', $newUsernameInput)) {
                $message = 'Brugernavnet indeholder ugyldige tegn.';
                $messageType = 'error';
            } elseif (strlen($newUsernameInput) < 3) {
                $message = 'Brugernavnet skal være mindst 3 tegn langt.';
                $messageType = 'error';
            } elseif (strlen($newUsernameInput) > 20) {
                $message = 'Brugernavnet må maksimalt være 20 tegn langt.';
                $messageType = 'error';
            } elseif (stripos($newUsernameInput, 'G-') === 0) {
                // "G-" marks guests, so users may not type it themselves.
                $message = 'Brugernavnet må ikke starte med "G-".';
                $messageType = 'error';
            } else {
                // Check if username already exists
                $userPrefix = strncmp($currentUsername, 'G-', 2) === 0 ? 'G-' : '';
                $newUsername = $userPrefix . $newUsernameInput . '#' . substr($userId, -5);

                if (isUsernameTaken($conn, $newUsernameInput, substr($userId, -5), $userId)) {
                    $message = 'Dette brugernavn er allerede i brug af en anden bruger med samme ID.';
                    $messageType = 'error';
                } else {
                    // Update username
                    $updateQuery = $conn->prepare('UPDATE Users SET userName = ? WHERE userId = ?');
                    $updateQuery->bind_param('ss', $newUsername, $userId);

                    if ($updateQuery->execute()) {
                        $currentUsername = $newUsername;
                        $message = 'Dit brugernavn blev ændret til: ' . $newUsername;
                        $messageType = 'success';
                    } else {
                        $message = 'Der opstod en fejl ved ændring af brugernavnet. Prøv igen senere.';
                        $messageType = 'error';
                    }
                    $updateQuery->close();
                }
            }
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Skift brugernavn - Pellicula Film Forum</title>
    <meta name="description" content="Skift dit brugernavn på Pellicula Film Forum.">
    <link rel="stylesheet" href="/assets/css/userMgmt.css?v=<?= filemtime(__DIR__ . '/../assets/css/userMgmt.css') ?>">
    <?php include '../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <section class="form-page change-username-container">
            <h1>Skift brugernavn</h1>
            <p class="form-intro">Dit nye navn bliver vist på alle dine opslag og kommentarer, også de gamle.</p>

            <?php if (!empty($message)): ?>
                <p class="message <?= viewEscape($messageType) ?>" role="<?= $messageType === 'error' ? 'alert' : 'status' ?>"><?= viewEscape($message) ?></p>
            <?php endif; ?>

            <?php if (!empty($currentUsername) && preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $userId)): ?>
                <?php $changeUser = viewCurrentUser(); ?>
                <div class="card form-stack">
                    <div class="current-username-display">
                        <?= viewAvatar($currentUsername, 'md', $changeUser['avatar_id'] ?? null) ?>
                        <div>
                            <p class="current-username-label">Nuværende brugernavn</p>
                            <p class="username-text"><?= viewUserName($currentUsername) ?></p>
                        </div>
                    </div>

                    <form method="POST" class="form-stack change-username-form">
                        <?= csrfTokenField() ?>
                        <div class="field">
                            <label class="field-label" for="new_username">Nyt brugernavn</label>
                            <?php // The #id suffix is added by the server; shown here so users see the final name. ?>
                            <div class="input-group">
                                <?php if (strncmp($currentUsername, 'G-', 2) === 0): ?>
                                    <span class="input-addon" aria-hidden="true">G-</span>
                                <?php endif; ?>
                                <input
                                    type="text"
                                    id="new_username"
                                    name="new_username"
                                    minlength="3"
                                    maxlength="20"
                                    pattern="[A-Za-z0-9.,_@:!?()+&-]+"
                                    title="Brug bogstaver, tal eller tegnene . , _ @ : ! ? ( ) + & -"
                                    placeholder="Indtast nyt brugernavn"
                                    aria-describedby="newUsernameHint"
                                    autocomplete="off"
                                    required
                                >
                                <span class="input-addon" aria-hidden="true">#<?= viewEscape(substr($userId, -5)) ?></span>
                            </div>
                            <p class="field-hint" id="newUsernameHint">3-20 tegn: A-Z, a-z, 0-9 og . , _ @ : ! ? ( ) + &amp; - (ikke æ, ø og å). Dit ID-nummer tilføjes automatisk.</p>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-submit">Gem brugernavn</button>
                            <a class="form-cancel" href="/profile">Annullér</a>
                        </div>
                    </form>
                </div>

                <aside class="changeUsername-info-box">
                    <h2>Godt at vide</h2>
                    <ul class="changeUsername-info-list">
                        <li>Dit ID-nummer (#<?= viewEscape(substr($userId, -5)) ?>) følger med dig og kan ikke ændres.</li>
                        <li>To brugere kan godt hedde det samme, så længe de har forskellige ID-numre.</li>
                        <?php if (strncmp($currentUsername, 'G-', 2) === 0): ?>
                            <li>Du er gæst, derfor beholder navnet G- foran.</li>
                        <?php endif; ?>
                    </ul>
                </aside>
            <?php else: ?>
                <a class="btn" href="/userMgmt/onboarding">Opret dit brugernavn</a>
            <?php endif; ?>
        </section>
    </main>
    <?php include '../assets/php/footer.php'; ?>
</body>
</html>