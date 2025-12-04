<html lang="en">
<head>
    <title>Sign up for accessing the View Ridge Gallery</title>
</head>

<body>
<?php
    if(isset($_POST["firstname"]) && isset($_POST["lastname"]) &&
            isset($_POST["userid"]) && isset($_POST["password"]) &&
            $_POST["firstname"]!="")
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
            $firstname = $_POST["firstname"];
            $lastname = $_POST["lastname"];
            $userid = $_POST["userid"];
            $password = $_POST["password"];

            //Generate query
            $sql = "select username";
            $sql = $sql. " from user where username='".$userid."'";

            //Run query
            $result = $conn->query($sql);
            if($result->num_rows>0)
            {
                echo("<h4>Username ".$userid." already exists! Enter something else.<h4>");
                echo("<h4><a href=\"signup.php\">Try again!</a>.<h4>");

            }
            else
            {
                //Generate second query
                $sql = "insert into user(firstname, lastname, username, is_admin, password)";
                $sql = $sql. " values ('".$firstname."', '".$lastname."', '".$userid."', 0, '".$password."')";

                //Run query
                $result = $conn->query($sql);

                if($result)
                {
                    echo("<h4>Username ".$userid." successfully created.<h4>");
                    echo("<br> Go to <a href=\"index.php\">login page</a>.");
                }
                else
                {
                    echo("<h4>Some error occurred! <a href=\"signup.php\">Try again!</a>.<h4>");
                }
            }
        }
    }
    else
    {?>
    <h1>Create your profile for the View Ridge Gallery!</h1>
    <br>
    <form action="signup.php" method="post">
        First Name*: <input type="text" name="firstname"><br>
        Last Name*: <input type="text" name="lastname"><br>
        Username*: <input type="text" name="userid"><br>
        Password*: <input type="password" name="password"><br>
        Re-type Password*: <input type="password" name="password2"><br>
        <br>
        <input type="submit"> &nbsp;&nbsp;
        <input type="reset">

    </form>
    <?php
    }
    ?>
    <br>
    <br>
    <hr>
    Already have a username? Sign in <a href="index.php">here</a>.
</body>

</html>