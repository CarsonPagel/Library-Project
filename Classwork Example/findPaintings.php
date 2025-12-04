<html lang="en">
<head>
    <title>Paintings by an Artist</title>
</head>
<body>
<?php

session_start();
if (isset($_SESSION["userid"]) && $_SESSION["userid"]!="")
{

    if(isset($_GET["id"]))
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
            $artistid = $_GET["id"];
            $firstname = $_GET["fname"];
            $lastname = $_GET["lname"];
            $oldSearchString = $_SESSION["searchString"];
            $_SESSION["searchString"] = "?id=".$artistid."&fname=".$firstname."&lname=".$lastname;
            echo "<h3>Works by ".$firstname." ".$lastname." in our gallery:";
            //Generate query
            $sql = "select workid, title, copy, medium, description from work";
            $sql = $sql. " where artistid=".$artistid;
            $sql = $sql. " order by workid";
            //echo $sql;
            //Run query
            $result = $conn->query($sql);
            if($result->num_rows>0)
            {   echo "<table border=\"1\">";
                echo "<tr>";
                echo "<th>Work ID</th>";
                echo "<th>Title</th>";
                echo "<th>Copy</th>";
                echo "<th>Medium</th>";
                echo "<th>Description</th>";
                echo "<th>&nbsp;</th>";
                echo "</tr>";
                while($row = $result->fetch_assoc())
                {
                    echo "<tr>";
                    echo "<td>".$row["workid"]."</td>";
                    echo "<td>".$row["title"]."</td>";
                    echo "<td>".$row["copy"]."</td>";
                    echo "<td>".$row["medium"]."</td>";
                    echo "<td>".$row["description"]."</td>";
                    echo "<td>";
                    echo "<form method=\"get\" action=\"buyPainting.php\">";
                    echo "<input type=\"hidden\" name=\"workid\" value=\"".$row["workid"] ."\">";
                    echo "&nbsp;<input type=\"Submit\" value=\"Buy\">&nbsp;" ;
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";

            }
            else
            {
                echo "No records found!";
            }
            echo "<br><br><a href=\"searchArtist.php" . $oldSearchString . "\">Back to Search</a>";

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