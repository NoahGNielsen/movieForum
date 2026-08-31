<!DOCTYPE html>
<html lang="da">
<head>
    <title>Onboarding - Pellicula Film Forum</title>
    <meta name="description" content="Onboarding side for nye brugere på Pellicula Film Forum. Her kan nye brugere oprette et brugernavn og acceptere vilkår og betingelser.">
    <link rel="stylesheet" href="../assets/css/userMgmt.css">
    <?php include '../assets/php/header.php'; ?>
</head>
<body>
    <form action="newUserHandeling.php" method="post">
        <label for="username">Brugernavn:</label>
        <input type="text" name="username" placeholder="Brugernavn" class ="newUserUsernameInput" required minlength="3" maxlength="49">
        
        <label for="remember">Husk Mig</label>
        <input type="checkbox" name="remember" class ="newUserRememberCheckBox">

        <label for="terms">Jeg accepterer vilkårene og betingelserne</label>
        <input type="checkbox" name="terms" class ="newUserTOSCheckBox"required>

        <input type="submit">Send</input>
    </form>
    <?php include '../assets/php/footer.php'; ?>
</body>
</html>