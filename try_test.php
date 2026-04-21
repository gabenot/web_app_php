<?php 
require_once('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors',1 );
error_reporting(E_ALL);
try
{
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query_person = "SELECT * FROM person";
    $query_placement = "SELECT * FROM placement";
    $query_game  = "SELECT * FROM game";
    $stmt = $db->query($query_person);
    $stpl = $db->query($query_placement);
    $stgm = $db->query($query_game);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $placements = $stpl->fetchAll(PDO::FETCH_ASSOC);
    $games = $stgm->fetchAll(PDO::FETCH_ASSOC);

    $query_goldmedal = "SELECT person_id, COUNT(CASE WHEN placing = 1 THEN 1 END) AS count FROM placement GROUP BY person_id LIMIT 10";
    $stgl = $db->query($query_goldmedal);
    $goldmedals = $stgl->fetchAll(PDO::FETCH_ASSOC);

}catch (PDOException $e){
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


</head>
<body>
<style>
   
tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
   
    </style>
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
        order:[[4,'desc']],
    });
    $('#example tbody').on('click', 'tr', function () {
    //$(this).toggleClass('selected');
    $('#person_name').val(table.rows(this).data()[0][0]);
    $('#person_surname').val(table.rows(this).data()[0][1]);
    
        $('#person_details').trigger("click");
    
    

    //alert(table.rows(this).data()[0][0]);
    
    
     //x = String(table.rows('.selected').data()[0][0]);
     //alert(x);
    

});
});

    </script>
    
        <h1 >Zadanie 1</h1>
        <form method="POST" action="person_details.php">
     
            
            <input type="text" name = "person_name" id="person_name" style="visibility:hidden; display:none"> 
            <input type="text" name = "person_surname" id="person_surname" style="visibility:hidden; display:none"> 
    

        <input type="submit" id = "person_details" style="visibility:hidden; display:none">
        
        </form>
        <table id="example" class="display" style="width:100%">
        
        <thead>
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Birthday</th>
                <th>Birth country</th>
                <th>Number of gold medals</th>
            
            </tr>
        </thead>
        <tbody>
    <?php  

    foreach($results as $result)
    {

        foreach ($goldmedals as $goldmedal)
        {
            if ($result["id"] == $goldmedal["person_id"] && $goldmedal["count"] > 0)
            {
            
                $date = new DateTimeImmutable($result["birth_day"]);
                echo "<tr><td>" . $result["name"] . "</td><td>" . $result["surname"] . "</td><td>" . $date->format("d.m.Y") . "</td><td>" . $result["birth_country"] . "</td><td>" . $goldmedal["count"] . "</td></tr>";
            
            
        }
        }
        //$date = new DateTimeImmutable($result["birth_day"]);
        //echo "<tr><td>" . $result["name"] . "</td><td>" . $result["surname"] ;
    }
   

    // echo "<tr><td>" . $result["name"] . "</td><td>" . $result["surname"] . "</td><td>". $date->format("d.m.Y") . "</td></tr>" ;
    ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Birthday</th>
                <th>Birth country</th>
                <th>Number of gold medals</th>
                
            </tr>
        </tfoot>
    </table>
</body>
</html>