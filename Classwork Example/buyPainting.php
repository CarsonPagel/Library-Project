<html lang="en">
<head>
    <title>Paintings by an Artist</title>
</head>
<body>
<?php

session_start();
if (isset($_SESSION["userid"]) && $_SESSION["userid"]!="")
{

    if(isset($_GET["workid"]))
    {
        $servername="localhost";
        $username="root";
        $password="";
        $dbname="vrg";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if($conn->connect_error)
        {
            die("Connection failed: ".$conn->connect_error);
        }
        else
        {   //Read the form data
            //echo $_SESSION["searchString"];
            $workid = $_GET["workid"];

            //Generate query
            $sql = "select workid, title, copy, medium, description from work";
            $sql = $sql. " where workid=".$workid;
            //echo $sql;
            //Run query
            $result = $conn->query($sql);
            echo "You want to buy the following painting:<br>";
            echo "<table border=\"1\">";
            echo "<tr>";
            echo "<th>Work ID</th>";
            echo "<th>Title</th>";
            echo "<th>Copy</th>";
            echo "<th>Medium</th>";
            echo "<th>Description</th>";
            echo "</tr>";
            while($row = $result->fetch_assoc())
            {
                echo "<tr>";
                echo "<td>".$row["workid"]."</td>";
                echo "<td>".$row["title"]."</td>";
                echo "<td>".$row["copy"]."</td>";
                echo "<td>".$row["medium"]."</td>";
                echo "<td>".$row["description"]."</td>";
                echo "</tr>";
            }
            echo "</table>";



            echo "<br><br><a href=\"findPaintings.php" . $_SESSION["searchString"] . "\">Back to Search</a>";

            $conn->close();
        }
    }

}
else
{
    echo "<h1> You have to log in to access this page!</h1>";
    echo "<a href=\"index.php\">Go to login page</a>";
}
?>

<br><br><a href="logout.php">Log out</a>
</body>
</html>