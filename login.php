<?php

session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome_page.php");
    exit;
}

require_once('config.php');
require_once('GoogleAuthenticator.php');
$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // TODO: Skontrolovat ci login a password su zadane (podobne ako v register.php).

    $sql = "SELECT fullname, email, login, password, created_at, 2fa_code FROM users WHERE login = :login";

    $stmt = $pdo->prepare($sql);

    // TODO: Upravit SQL tak, aby mohol pouzivatel pri logine zadat login aj email.
    $stmt->bindParam(":login", $_POST["login"], PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            // Uzivatel existuje, skontroluj heslo.
            $row = $stmt->fetch();
            $hashed_password = $row["password"];

            if (password_verify($_POST['password'], $hashed_password)) {
                // Heslo je spravne.
                $g2fa = new PHPGangsta_GoogleAuthenticator();
                if ($g2fa->verifyCode($row["2fa_code"], $_POST['2fa'], 2)) {
                    // Heslo aj kod su spravne, pouzivatel autentifikovany.

                    // Uloz data pouzivatela do session.
                    $_SESSION["loggedin"] = true;
                    $_SESSION["login"] = $row['login'];
                    $_SESSION["fullname"] = $row['fullname'];
                    $_SESSION["email"] = $row['email'];
                    $_SESSION["created_at"] = $row['created_at'];
                    


                    $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

                    $fullname = $_SESSION["fullname"];
                    $login = $_SESSION['login'];
                    $action = "login";
                    $dataTable = "-";

                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
                    $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
                    $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
                    $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
                    $stmt1->execute();
                    
                   
                    
                    // Presmeruj pouzivatela na zabezpecenu stranku.
                    header("location: welcome_page.php");
                }
                else {
                    echo "2FA code is wrong, try again.";
                }
            } else {
                echo "Login or password are wrong, try again.";
            }
        } else {
            echo "Login or password are wrong, try again.";
        }
    } else {
        echo "Something went wrong, try again";
    }

    unset($stmt);
    unset($stmt1);
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
    <title>Login/register with 2FA - Login</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="index.php">Back to Main page</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="players.php">All athletes <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="admin.php">Admin panel</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="top_winners.php">Top Winners</a>
      </li>
      

    </ul>
    
  </div>
</nav>
<header>
    <hgroup>
        <h1>Sign in</h1>
        <br>
        <h2>Registration is required</h2>
        <br>
    </hgroup>
</header>


    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

        <label for="login">
            Login:
            <input type="text" name="login" value="" id="login" required>
        </label>
        <br>
        <br>
        <label for="password">
            Password:
            <input type="password" name="password" value="" id="password" required>
        </label>
        <br>
        <br>
        <label for="2fa">
            2FA code:
            <input type="number" name="2fa" value="" id="2fa" required>
        </label>

        <button type="submit">Sign in</button>
    </form>
    <br>
    <p>Havent created account yet? -> <a href="register.php">Registration</a></p>

</body>
</html>
