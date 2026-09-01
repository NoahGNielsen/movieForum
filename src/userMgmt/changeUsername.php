<?php
require_once '../assets/php/userCookieHandeling.php';
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username'])) {
            $newUsernameInput = trim($_POST['new_username']);

            // Validate username
            if (empty($newUsernameInput)) {
                $message = 'Brugernavnet kan ikke være tomt.';
                $messageType = 'error';
            } elseif (!preg_match('/^[A-Za-z]+$/', $newUsernameInput)) {
                $message = 'Brugernavnet må kun indeholde bogstaver.';
                $messageType = 'error';
            } elseif (strlen($newUsernameInput) < 3) {
                $message = 'Brugernavnet skal være mindst 3 tegn langt.';
                $messageType = 'error';
            } elseif (strlen($newUsernameInput) > 20) {
                $message = 'Brugernavnet må maksimalt være 20 tegn langt.';
                $messageType = 'error';
            } else {
                // Check if username already exists
                $newUsername = $newUsernameInput . '#' . substr($userId, -5);
                $checkUsernameQuery = $conn->prepare('SELECT userId FROM Users WHERE userName = ? AND userId != ? LIMIT 1');
                $checkUsernameQuery->bind_param('ss', $newUsername, $userId);
                $checkUsernameQuery->execute();
                $checkUsernameQuery->store_result();

                if ($checkUsernameQuery->num_rows > 0) {
                    $message = 'Dette brugernavn er allerede i brug af en anden bruger med samme ID.';
                    $messageType = 'error';
                } else {
                    // Update username
                    $updateQuery = $conn->prepare('UPDATE Users SET userName = ? WHERE userId = ?');
                    $updateQuery->bind_param('ss', $newUsername, $userId);

                    if ($updateQuery->execute()) {
                        $currentUsername = $newUsername;
                        $message = 'Dit brugernavn blev ændret til: ' . htmlspecialchars($newUsername, ENT_QUOTES, 'UTF-8');
                        $messageType = 'success';
                    } else {
                        $message = 'Der opstod en fejl ved ændring af brugernavnet. Prøv igen senere.';
                        $messageType = 'error';
                    }
                    $updateQuery->close();
                }
                $checkUsernameQuery->close();
            }
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Brugernavns ændring - Pellicula Film Forum</title>
    <meta name="description" content="Ændring af brugernavn for eksisterende brugere.">
    <link rel="stylesheet" href="../assets/css/userMgmt.css">
    <?php include '../assets/php/header.php'; ?>
</head>
<body>
    <main>
        <div class="change-username-container">
            <h1>Ændring af brugernavn</h1>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($currentUsername) && preg_match('/^[A-Za-z]{8}_[0-9]{3}_[0-9]{5}$/', $userId)): ?>
                <div class="current-username-display">
                    <p><strong>Dit nuværende brugernavn:</strong></p>
                    <p class="username-text"><?php echo htmlspecialchars($currentUsername, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <form method="POST" class="change-username-form">
                    <div class="form-group">
                        <label for="new_username">Nyt brugernavn:</label>
                        <input 
                            type="text" 
                            id="new_username" 
                            name="new_username"
                            minlength="3" 
                            maxlength="20" 
                            placeholder="Indtast nyt brugernavn"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-submit">Ændre brugernavn</button>
                </form>

                <div class="info-box">
                    <p><strong>Vigtig information:</strong></p>
                    <ul>
                        <li>Dit brugernavn kan kun indeholde bogstaver (A-Z, a-z)</li>
                        <li>Dit unikke ID-nummer (#xxxxx) bliver automatisk tilføjet til dit brugernavn</li>
                        <li>Brugernavnet skal være mellem 3 og 20 tegn langt</li>
                        <small>Du kan ikke vælge et brugernavn, hvis en anden bruger med samme ID allerede har det.</small>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include '../assets/php/footer.php'; ?>
</body>
</html>