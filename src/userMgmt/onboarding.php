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
    <form action="../assets/php/newUserHandeling" method="post">
        <label for="username">Brugernavn:</label>
        <input type="text" name="username" placeholder="Brugernavn" class ="newUserUsernameInput" required minlength="3" maxlength="25">
        
        <label for="remember">Husk Mig</label>
        <input type="checkbox" name="remember" class ="newUserRememberCheckBox">

        <label for="terms">Jeg accepterer <a href="https://forum.noahgajnielsen.dk/legal/terms" target="_blank">vilkårene og betingelserne</a></label>
        <input type="checkbox" name="terms" class ="newUserTOSCheckBox"required>

        <button type="submit">Send</button>
    </form>
    <?php include '../assets/php/footer.php'; ?>
</body>
</html>