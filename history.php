<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

//var_dump($_POST["person_id"]);



session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: login.php");
    exit;
}



try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM history where login = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['login']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    /*$query= "SELECT * FROM history";
    $stmt = $db->query($query);
   
    $history = $stmt->fetch(PDO::FETCH_ASSOC);*/
    

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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script type="text/javascript" src="script.js"></script>
<script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    
<script>
$(document).ready(function () {
    // Setup - add a text input to each footer cell
    
    $('#example tfoot th').each(function () {
        var title = $(this).text();
        $(this).html('<input type="text" placeholder="Search ' + title + '" />');
    });
 
    // DataTable
    var table = $('#example').DataTable({
        initComplete: function () {
            // Apply the search
            this.api()
                .columns()
                .every(function () {
                    var that = this;
 
                    $('input', this.footer()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
        },
        order:[[0,'asc']],
    });
    
});
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
      <span class="navbar-text">
      <?php echo "User: " . $_SESSION['fullname'] . " Login: " . $_SESSION['login']; ?>
    </span>
      
      

    </ul>
    
  </div>
</nav>

    <div class="container-md">
        <h1>Admin panel: History</h1>
        <br>
        
        <h3>History</h3>
        <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>login</th>
                <th>fullname</th>
                <th>date </th>
                <th>action_type</th>
                <th>database_table</th>
          
        </tr>
        </thead>
        <tbody>
        <?php 
       
       foreach($history as $his)
       {
            
           
             if($his['login'] == $_SESSION['login']){
            $date = new DateTimeImmutable($his["date"]);
            echo "<tr><td>" . $his["login"] . "</td><td>" . $his["fullname"] . "</td><td>" . $date->format("Y-m-d H:i:s") . "</td><td>". $his["action_type"] . "</td><td>" . $his["database_table"] . "</td></tr>";
             }
            
       }
        ?>
        </tbody>
        <tfoot>
   
                <th>login</th>
                <th>fullname</th>
                <th>date </th>
                <th>action_type</th>
                <th>database_table</th>
          
                
            </tr>
        </tfoot>
</table>

        
       


        </table>
    </div>
</body>

</html>