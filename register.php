<?php

// Konfiguracia PDO
require_once('config.php');
require_once('GoogleAuthenticator.php');
// Kniznica pre 2FA
$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ------- Pomocne funkcie -------
function checkEmpty($field) {
    // Funkcia pre kontrolu, ci je premenna po orezani bielych znakov prazdna.
    // Metoda trim() oreze a odstrani medzery, tabulatory a ine "whitespaces".
    if (empty(trim($field))) {
        return true;
    }
    return false;
}

function checkLength($field, $min, $max) {
    // Funkcia, ktora skontroluje, ci je dlzka retazca v ramci "min" a "max".
    // Pouzitie napr. pre "login" alebo "password" aby mali pozadovany pocet znakov.
    $string = trim($field);     // Odstranenie whitespaces.
    $length = strlen($string);      // Zistenie dlzky retazca.
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}

function checkUsername($username) {
    // Funkcia pre kontrolu, ci username obsahuje iba velke, male pismena, cisla a podtrznik.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}

function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function userExist($db, $login, $email) {
    // Funkcia pre kontrolu, ci pouzivatel s "login" alebo "email" existuje.
    $exist = false;

    $param_login = trim($login);
    $param_email = trim($email);

    $sql = "SELECT id FROM users WHERE login = :login OR email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $exist = true;
    }

    unset($stmt);

    return $exist;
}

// ------- ------- ------- -------



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errmsg = "";

    // Validacia username
    if (checkEmpty($_POST['login']) === true) {
        $errmsg .= "<p>Enter your login.</p>";
    } elseif (checkLength($_POST['login'], 6,32) === false) {
        $errmsg .= "<p>Login must have  min. 6 and max. 32 symbols.</p>";
    } elseif (checkUsername($_POST['login']) === false) {
        $errmsg .= "<p>Login can contain only upper/lower case letters, numbers and _ .</p>";
    }

    // Kontrola pouzivatela
    if (userExist($pdo, $_POST['login'], $_POST['email']) === true) {
        $errmsg .= "User with this e-mail / login is already registered.</p>";
    }

    // Validacia mailu
    if (checkGmail($_POST['email'])) {
        $errmsg .= "Please, register non-gmail account";
        // Ak pouziva google mail, presmerujem ho na prihlasenie cez Google.
        // header("Location: google_login.php");
    }

    if (checkEmpty($_POST['password']) === true) {
        $errmsg .= "<p>Enter your password.</p>";
    } elseif (checkLength($_POST['password'], 6,32) === false) {
        $errmsg .= "<p>Password must have  min. 6 and max. 32 symbols.</p>";
    } 

    if (empty($errmsg)) {
        $sql = "INSERT INTO users (fullname, login, email, password, 2fa_code) VALUES (:fullname, :login, :email, :password, :2fa_code)";

        $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        // 2FA pomocou PHPGangsta kniznice: https://github.com/PHPGangsta/GoogleAuthenticator
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);

        // Bind parametrov do SQL
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
        } else {
            echo "Something went wrong";
        }

        unset($stmt);
    }
  
    unset($pdo);
}

?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login/register with 2FA - Register</title>

    <style>
        html {
            max-width: 70ch;
            padding: 3em 1em;
            margin: auto;
            line-height: 1.75;
            font-size: 1.25em;
        }

        h1,h2,h3,h4,h5,h6 {
            margin: 3em 0 1em;
        }

        p,ul,ol {
            margin-bottom: 2em;
            color: #1d1d1d;
            font-family: sans-serif;
        }
    </style>
</head>
<body>
<header>
    <hgroup>
        <h1>Registration</h1>
        <h2>New login</h2>
    </hgroup>
</header>
<main>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <label for="firstname">
            Name:
            <input type="text" name="firstname" value="" id="firstname" placeholder="eg. Jonatan" required>
        </label>

        <label for="lastname">
            Surname:
            <input type="text" name="lastname" value="" id="lastname" placeholder="eg. Petrzlen" required>
        </label>

        <br>

        <label for="email">
            E-mail:
            <input type="email" name="email" value="" id="email" placeholder="eg. jpetrzlen@example.com" required>
        </label>

        <label for="login">
            Login:
            <input type="text" name="login" value="" id="login" placeholder="eg. jperasin" required>
        </label>

        <br>

        <label for="password">
            Password:
            <input type="password" name="password" value="" id="password" required>
        </label>

        <button type="submit">Register your account</button>

        <?php
        if (!empty($errmsg)) {
            // Tu vypis chybne vyplnene polia formulara.
            echo $errmsg;
        }
        if (isset($qrcode)) {
            // Pokial bol vygenerovany QR kod po uspesnej registracii, zobraz ho.
            $message = '<p>Scan QR code with Authenticator for 2FA: <br><img src="'.$qrcode.'" alt="qr kod for application Authenticator"></p>';

            echo $message;
            echo '<p>Now you can login: <a href="login.php" role="button">Login</a></p>';
        }
        ?>

    </form>
    <p>Already have account? <a href="login.php">Login here</a></p>
</main>
</body>
</html>