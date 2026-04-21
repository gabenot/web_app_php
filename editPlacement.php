<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

//var_dump($_POST["person_id"]);


if (!isset($_GET['id'])) {
    exit("id not exist");
}
if (!isset($_GET['p_id'])) {
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

    

    if (!empty($_POST) && !empty($_POST['placing']) && !empty($_POST['discipline'])) {
        $sql = "UPDATE placement SET placing=?, discipline=? where id=?";
        
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_POST['placing'], $_POST['discipline'], intval($_POST['placement_id'])]);
        $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

        $fullname = $_SESSION["fullname"];
        $login = $_SESSION['login'];
        $action = "Edit placement";
        $dataTable = "placement";

        $stmt1 = $db->prepare($sql1);
        $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
        $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
        $stmt1->execute();
        unset($stmt1); 
        echo "<script>alert('Placement was successfully edited!')</script>";
        header("Refresh:0");
    }
    $query_person = "SELECT * FROM person where id=?";
    $stmt = $db->prepare($query_person);
    $stmt->execute([$_GET['id']]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);
   
    $query_placement = "SELECT * FROM placement where id=?";
    $stmp = $db->prepare($query_placement);
    $stmp->execute([$_GET['p_id']]);
    $placements = $stmp->fetch(PDO::FETCH_ASSOC);
    //echo json_encode($placements);
    //echo $placements['games_id'];

    //$query_game = "SELECT * FROM game where id=?";
    //$stgm = $db->prepare($query_game);
    //$stgm->execute([$placements['games_id']]);
    //$games = $stgm->fetch(PDO::FETCH_ASSOC);
    //$game_city = $games['city'];
    if(isset($_POST['del_placement_id'])){
        
        $sql = "DELETE FROM placement WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([intval($_POST['del_placement_id'])]);
        $message = 'Succesfully deleted Placement!';
        
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
        echo "<script> 
            alert('$message');
            window.location.replace('https://site189.webte.fei.stuba.sk/oh/editPerson.php?id=" .  $person["id"] . "');
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
      <?php 
        echo "<li><a class = 'nav-link' href='editPerson.php?id=" .  $person["id"] . "'>Back to Edit Person</a></li>";
        ?>
        
      
        <span class="navbar-text">
      <?php echo "User: " . $_SESSION['fullname'] . " Login: " . $_SESSION['login']; ?>
    </span>

    </ul>
    
  </div>
</nav>

    <div class="container-md">
        <h1>Admin panel: Edit Placement of person</h1>
        <br>
        
        <h3>Edit placement</h3>
        <form action="#" method="post">
            <input type="hidden" name="placement_id" value="<?php echo $placements['id'];?>">
            <div class="mb-3">
                <label for="InputPlacing" class="form-label">Placing:</label>
                <input type="number" name="placing" class="form-control" id="InputPlacing" value="<?php echo $placements['placing'];?>" required>
            </div>
            <div class="mb-3">
                <label for="InputDiscipline" class="form-label">Discipline:</label>
                <input type="text" name="discipline" class="form-control" id="InputDiscipline" value="<?php echo $placements['discipline'];?>" required>
            </div>
            
           
            <button type="submit" class="btn btn-primary">Edit</button>
            </form>
            <form action="#" method="post">
                <br>
            <?php
            echo '<input type="hidden" name="del_placement_id" value="' . $_GET['p_id'] . '">';
            ?>
            <button type="submit" class="btn btn-primary">Delete</button>
        </form>


        
       


        </table>
    </div>
</body>

</html>