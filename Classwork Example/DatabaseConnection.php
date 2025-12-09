<?php
    $host = "localhost";
    $user = "root";      // default XAMPP username
    $pass = "";          // default XAMPP password (empty)
    $db   = "vrg";
    $conn = "";

    try {
        $conn = mysqli_connect($host, $user, $pass, $db);
    } catch(mysqli_sql_exception $ex) {
        echo "Could not connect to database! <br>";
    }

?>