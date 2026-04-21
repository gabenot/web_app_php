<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

//var_dump($_POST["person_id"]);

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM person";
    $stmt = $db->query($query); 
    $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo $e->getMessage();
}

function userExist($db, $name, $surname) {
    

    $query_person = "SELECT * FROM person";
   
    $stmt = $db->query($query_person);
   
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($results as $result)
    {
        if ($name == $result['name'] && $surname == $result['surname'])
        {
            return true;
        }
    }
    return false;
}


?>
<?php

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: login.php");
    exit;
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Logout</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="history.php">History</a>
      </li>
      <span class="navbar-text">
      <?php echo "User: " . $_SESSION['fullname'] . " &nbsp&nbsp Login: " . $_SESSION['login']; ?>
    </span>

    </ul>
    
  </div>
</nav>
    <div class="container-md">
    <h1>Admin panel</h1>
    <br>
    
    <h3>Add athlete</h3>

        <form action="#" method="post">
            <div class="mb-3">
                <label for="InputName" class="form-label">Name:</label>
                <input type="text" name="name" class="form-control" id="InputName" required>
            </div>
            <div class="mb-3">
                <label for="InputSurname" class="form-label">Surname:</label>
                <input type="text" name="surname" class="form-control" id="InputSurname" required>
            </div>
            <div class="mb-3">
                <label for="InputDate" class="form-label">birth day:</label>
                <input type="date" name="birth_day" class="form-control" id="InputDate" required>
            </div>
            <div class="mb-3">
                <label for="InputbrPlace" class="form-label">birth place:</label>
                <input type="text" name="birth_place" class="form-control" id="InputBrPlace" required>
            </div>
            <div class="mb-3">
                <label for="InputBrCountry" class="form-label">birth country:</label>
                <input type="text" name="birth_country" class="form-control" id="InputBrCountry" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
       

        <table class="table">
        <thead>
            <tr><td>Name</td><td>Surname</td><td>Birthday</td></tr>
        </thead>
        <tbody>
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
            if (userExist($db, $_POST['name'], $_POST['surname']) === true ) {
                echo "<script>alert('You cannot create same athlete again!')</script>";
                header("Refresh:0");
                
            }
            else if(!empty($_POST) && !empty($_POST['name'])){
                //var_dump($_POST);
                echo "<script>alert('Successfully created!')</script>";
                header("Refresh:0");
                $sql = "INSERT INTO person (name, surname, birth_day, birth_place, birth_country) VALUES (?,?,?,?,?)";
                $stmt = $db->prepare($sql);
                $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country']]);

                $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

                $fullname = $_SESSION["fullname"];
                $login = $_SESSION['login'];
                $action = "Add new person";
                $dataTable = "person";

                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
                $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
                $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
                $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
                $stmt1->execute();
                unset($stmt1);
               
            }
        } 
        foreach($persons as $person){
            $date = new DateTimeImmutable($person["birth_day"]);
            echo "<tr><td><a href='editPerson.php?id=" .  $person["id"] . "'>" . $person["name"] . "</a></td><td>" . $person["surname"] . "</td><td>" . $date->format("d.m.Y") . "</td></tr>";
        }
        ?> 
        </tbody>
    </table>
    </div>
</body>
</html>