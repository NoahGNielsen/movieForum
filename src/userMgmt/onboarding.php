<?php include '../assets/php/userCookieHandeling.php'; ?>
<!DOCTYPE html>
<html lang="da">
<head>
    <title>Onboarding - Pellicula Film Forum</title>
    <meta name="description" content="Onboarding side for nye brugere på Pellicula Film Forum. Her kan nye brugere oprette et brugernavn til brug på siden.">
    <link rel="stylesheet" href="../assets/css/userMgmt.css">
    <?php include '../assets/php/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../assets/php/navBar.php'; ?>
    <main>
        <h1>
            Onboarding
        </h1>
        <p>
            Velkommen til Pellicula Film Forum! For at få adgang til alle funktioner på vores forum, skal du oprette et brugernavn. Udfyld formularen nedenfor for at vælge dit brugernavn og begynde din rejse som en del af vores filmelskende fællesskab.
        </p>
        <form action="../assets/php/newUserHandeling" method="post" class="newUserForm">
            <label for="username">Brugernavn:</label>
            <input type="text" name="username" placeholder="Brugernavn" class ="newUserUsernameInput" required minlength="3" maxlength="25">
            
            <label for="remember">Husk Mig</label>
            <input type="checkbox" name="remember" class ="newUserRememberCheckBox">

            <label for="terms">Jeg accepterer <a href="https://forum.noahgajnielsen.dk/legal/terms" target="_blank">vilkårene og betingelserne</a></label>
            <input type="checkbox" name="terms" class ="newUserTOSCheckBox"required>

            <button type="submit">Send</button>
            <div class="onboarding-info-box">
                        <p><strong>Vigtig information:</strong></p>
                        <ul class="changeUsername-info-list">
                            <li>Dit brugernavn kan kun indeholde bogstaver (A-Z, a-z - ikke Æ, Ø og Å)</li>
                            <li>Dit unikke ID-nummer (#xxxxx) bliver automatisk tilføjet til dit brugernavn</li>
                            <li>Brugernavnet skal være mellem 3 og 20 tegn langt</li>
                            <small>Du kan ikke vælge et brugernavn, hvis en anden bruger med samme ID allerede har det.</small>
                        </ul>
                    </div>
        </form>
        <small>Har du allerede et brugernavn? <a href="https://forum.noahgajnielsen.dk/userMgmt/changeUsername">Ændre det her</a>.</small>
    </main>
    <?php include '../assets/php/footer.php'; ?>
</body>
</html>