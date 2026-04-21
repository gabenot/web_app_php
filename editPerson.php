<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

//var_dump($_POST["person_id"]);


if (!isset($_GET['id'])) {
    exit("id not exist");
}
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: login.php");
    exit;
}

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($_POST) && !empty($_POST['name'])) {
        //var_dump($_POST);
        $sql = "UPDATE person SET name=?, surname=?, birth_day=?, birth_place=?, birth_country=? where id=?";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], intval($_POST['person_id'])]);

        $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

                $fullname = $_SESSION["fullname"];
                $login = $_SESSION['login'];
                $action = "Edit person";
                $dataTable = "person";

                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
                $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
                $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
                $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
                $stmt1->execute();
                unset($stmt1);
        echo "<script>alert('Successfully edited!')</script>";
        header("Refresh:0");
    }

    $query = "SELECT * FROM person where id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);

    
    $query = "select placement.*, game.city from placement join game on placement.games_id = game.id where placement.person_id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(isset($_POST['del_placement_id'])){
        $sql = "DELETE FROM placement WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([intval($_POST['del_placement_id'])]);
        $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

                $fullname = $_SESSION["fullname"];
                $login = $_SESSION['login'];
                $action = "Delete placement";
                $dataTable = "placement";

                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
                $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
                $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
                $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
                $stmt1->execute();
                unset($stmt1);
        echo "<script>alert('Succesfully deleted placement')</script>";
        header("Refresh:0");
    }
    if(isset($_POST['edit_placement_id'])){
        
        echo "<script>     
        window.location.replace('https://site189.webte.fei.stuba.sk/oh/editPlacement.php?id=" . $person["id"] . "&p_id=" . $_POST['edit_placement_id'] . "');
    </script>";
     
    }
    if(isset($_POST['del_person_id'])){
        foreach($placements as $placement)
        {
            if ($placement['person_id'] == $_POST['del_person_id'])
            {
                $sql2 = "DELETE FROM placement WHERE id=?";
                $stmt2 = $db->prepare($sql2);
                $stmt2->execute([$placement['id']]);
                
            }
        }
        $sql = "DELETE FROM person WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([intval($_POST['del_person_id'])]);
        $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

                $fullname = $_SESSION["fullname"];
                $login = $_SESSION['login'];
                $action = "Delete person";
                $dataTable = "person & placement";

                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
                $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
                $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
                $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
                $stmt1->execute();
                unset($stmt1);
       
        $message = 'Succesfully deleted Athlete!';

        echo "<script> 
            alert('$message');
            window.location.replace('https://site189.webte.fei.stuba.sk/oh/admin.php');
        </script>";   
    }
    if(isset($_POST['add_placement'])){
     echo "<script> 
            window.location.replace('https://site189.webte.fei.stuba.sk/oh/addPlacement.php?id=" . $person["id"] . "');
        </script>"; 
    }


} catch (PDOException $e) {
    echo $e->getMessage();
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
      <li class="nav-item">
        <a class="nav-link" href="admin.php">Back to Admin Panel</a>
      </li>
      <span class="navbar-text">
      <?php echo "User: " . $_SESSION['fullname'] . " Login: " . $_SESSION['login']; ?>
    </span>
      
      

    </ul>
    
  </div>
</nav>

    <div class="container-md">
        <h1>Admin panel: Edit Person</h1>
        <br>
        <h3>Athlete info</h3>
        <form action="#" method="post">
            <input type="hidden" name="person_id" value="<?php echo $person['id']; ?>">
            <div class="mb-3">
                <label for="InputName" class="form-label">Name:</label>
                <input type="text" name="name" class="form-control" id="InputName" value="<?php echo $person['name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="InputSurname" class="form-label">Surname:</label>
                <input type="text" name="surname" class="form-control" id="InputSurname" value="<?php echo $person['surname']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="InputDate" class="form-label">birth day:</label>
                <input type="date" name="birth_day" class="form-control" id="InputDate" value="<?php echo $person['birth_day']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="InputbrPlace" class="form-label">birth place:</label>
                <input type="text" name="birth_place" class="form-control" id="InputBrPlace" value="<?php echo $person['birth_place']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="InputBrCountry" class="form-label">birth country:</label>
                <input type="text" name="birth_country" class="form-control" id="InputBrCountry" value="<?php echo $person['birth_country']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Edit</button>
            </form>
            <form action="#" method="post">
                <br>
            <?php
            echo '<input type="hidden" name="del_person_id" value="' . $person['id'] . '">';
            ?>
            <button type="submit" class="btn btn-primary">Delete</button>
        </form>
        <form action="#" method="post">
                <br>
            <?php
            echo '<input type="hidden" name="add_placement" value="' . '">';
            ?>
            <button type="submit" class="btn btn-primary">Add placement</button>
            <br>
        </form>

        <br>
        <h2>Edit placement</h2>
        <table class="table">
            <thead>
                <tr>
                    <td>Placement</td>
                    <td>Discipline</td>
                    <td>City</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                <?php //var_dump($results) 
                foreach ($placements as $placement) {
                    //var_dump($placement);
                    echo '<tr><td>' . $placement['placing'] . '</td><td>' . $placement['discipline'] . '</td><td>' . $placement['city'] . '</td><td>';
                    echo '<form action="#" method="post"><input type="hidden" name="del_placement_id" value="' . $placement['id'] . '"><button type="submit" class="btn btn-primary">Delete</button></form>&nbsp;&nbsp;&nbsp;&nbsp;<form action="#" method="post"><input type="hidden" name="edit_placement_id" value="' . $placement['id'] . '"><button type="submit" class="btn btn-primary">Edit</button></form>';
                    echo '</td></tr>';
                }
                ?>
            </tbody>
        </table>



        </table>
    </div>
</body>

</html>