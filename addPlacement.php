<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

//var_dump($_POST["person_id"]);
if (!isset($_GET['id'])) {
    exit("id not exist");
}

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM person where id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);



    $query = "select placement.*, game.city from placement join game on placement.games_id = game.id where placement.person_id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    echo $e->getMessage();
}

function userExist($db, $placing, $discipline, $game_id) {
    

    $query_person = "SELECT * FROM placement";
   
    $stmt = $db->query($query_person);
    
    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($placements as $placement)
    {
        
        if ($game_id == $placement['games_id'] && $placing == $placement['placing'] && $discipline == $placement['discipline'])
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

<script>
    var subjectObject = {
  "LOH": {
    "Londýn": ["1948", "2012"],
    "Helsinki": ["1952"],
    "Melbourne/Štokholm": ["1956"],
    "Rím": ["1960"],
    "Tokio": ["1964, 2020"],
    "Mexiko": ["1968"],
    "Mníchov": ["1972"],
    "Montreal": ["1976"], 
    "Moskva": ["1980"],
    "Los Angeles": ["1984"],
    "Soul": ["1988"],
    "Barcelona": ["1992"],
    "Atlanta": ["1996"],
    "Sydney": ["2000"],
    "Atény": ["2004"],
    "Peking/Hongkong": ["2008"],
    "Rio de Janeiro": ["2016"]
  },
  "ZOH": {
    "Innsbruck": ["1964", "1976"],
    "Grenoble": ["1968"],
    "Sapporo": ["1972"],
    "Lake Placid": ["1980"],
    "Sarajevo": ["1984"],
    "Calgary": ["1988"],
    "Albertville": ["1992"],
    "Lillehammer": ["1994"],
    "Nagano": ["1998"],
    "Salt Lake City": ["2002"],
    "Turín": ["2006"],
    "Vancouver": ["2010"],
    "Soči": ["2014"],
    "Pjongčang": ["2018"],
    "Peking": ["2022"]
  }
}
window.onload = function() {
  var typeSel = document.getElementById("InputType");
  var citySel = document.getElementById("InputCity");
  var yearSel = document.getElementById("InputYear");
  
  for (var x in subjectObject) {
    typeSel.options[typeSel.options.length] = new Option(x, x);
  }
  typeSel.onchange = function() {
    
    yearSel.length = 1;
    citySel.length = 1;
    
    for (var y in subjectObject[this.value]) {
        citySel.options[citySel.options.length] = new Option(y, y);
    }
  }
  citySel.onchange = function() {
    //empty Chapters dropdown
    yearSel.length = 1;
    //display correct values
    var z = subjectObject[typeSel.value][this.value];
    for (var i = 0; i < z.length; i++) {
        yearSel.options[yearSel.options.length] = new Option(z[i], z[i]);
    }
}
  
}
</script>
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
    <h1>Admin panel: Add Placement for athlete</h1>
        <br>
       
        <h3>Placement info</h3>
        <form action="#" method="post">
            <div class="mb-3">
                <label for="InputPlacing" class="form-label">Placing:</label>
                <input type="number" name="placing" class="form-control" id="InputPlacing" required>
            </div>
            <div class="mb-3">
                <label for="InputDiscipline" class="form-label">Discipline:</label>
                <input type="text" name="discipline" class="form-control" id="InputDiscipline" required>
            </div>
            <div class="mb-3">
                <label for="InputType" class="form-label">Type:</label>
                <select name="type" class="form-control" id="InputType" required>
                <option value="" selected="selected">Select Type</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="InputCity" class="form-label">Select City:</label>
                <select name="city" class="form-control" id="InputCity" required>
                <option value="" selected="selected">Select Type first</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="InputYear" class="form-label">Select City:</label>
                <select name="year" class="form-control" id="InputYear" required>
                <option value="" selected="selected">Select City first</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
       
       

        <table class="table">
        <thead>
            <br>
            <tr><td>Placing</td><td>Discipline</td><td>City</td></tr>
        </thead>
        <tbody>
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
            
            if(!empty($_POST) && !empty($_POST['placing'])&& !empty($_POST['city']&& !empty($_POST['type']&& !empty($_POST['discipline']))) ){
                //var_dump($_POST);
                
                $query_game  = "SELECT * FROM game";
                $stgm = $db->query($query_game);
                $games = $stgm->fetchAll(PDO::FETCH_ASSOC);
                $games_id = 0;
                foreach ($games as $game)
                {
                    if ($game['city'] == $_POST['city'] && $game['type'] == $_POST['type'] && $game['year'] == $_POST['year'])
                    {
                        
                        $games_id = $game['id'];
                        break;
                    }
                }
                if (userExist($db, $_POST['placing'], $_POST['discipline'], $games_id) === true ) {
                    echo "<script> 
                    alert('You can not create same placement!')
                    window.location.replace('https://site189.webte.fei.stuba.sk/oh/addPlacement.php?id=" . $person["id"] . "');
                    </script>";
                    
                }
                else if ($games_id != 0)
                {
                $sql = "INSERT INTO placement (person_id, games_id, placing, discipline) VALUES (?,?,?,?)";
                
                $stmt = $db->prepare($sql);
                $success = $stmt->execute([$_GET['id'], $games_id, $_POST['placing'], $_POST['discipline']]);
                $sql1 = "INSERT INTO history (fullname, login, action_type, database_table) VALUES (:fullname, :login, :action, :dataTable)";

                $fullname = $_SESSION["fullname"];
                $login = $_SESSION['login'];
                $action = "Added placement";
                $dataTable = "placement";

                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam(":fullname", $fullname, PDO::PARAM_STR);
                $stmt1->bindParam(":login", $login, PDO::PARAM_STR);
                $stmt1->bindParam(":action", $action, PDO::PARAM_STR);
                $stmt1->bindParam(":dataTable", $dataTable, PDO::PARAM_STR);
                $stmt1->execute();
                unset($stmt1);
                echo "<script> 
                alert('Placement was successfully added!')
                window.location.replace('https://site189.webte.fei.stuba.sk/oh/addPlacement.php?id=" . $person["id"] . "');
                </script>";
                
                //echo "<script>alert('Successfully added!')</script>";
                //header("Refresh:0");
               
                }
            }
        } 
         //var_dump($results) 
        foreach ($placements as $placement) {
            //var_dump($placement);
            echo '<tr><td>' . $placement['placing'] . '</td><td>' . $placement['discipline'] . '</td><td>' . $placement['city'] . '</td><td>';
            
            echo '</td></tr>';
        }
        
        ?> 
        </tbody>
    </table>
    </div>
</body>
</html>