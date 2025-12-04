<?php
// Simple welcome/login handler modeled after the Classwork Example welcome.php
include 'database.php';
// Show errors temporarily to diagnose blank page issues (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Read posted credentials
$userid = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$fullname = '';

if (!$conn) {
    die("Database connection error.");
}

if ($userid !== '' && $password !== '') {
    // NOTE: This is intentionally simple (no prepared statements) to match the class example
    $sql = "SELECT firstname, lastname, username, is_admin, id FROM users WHERE username='" . $userid . "' AND password='" . $password . "'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $fullname = $row['firstname'] . ' ' . $row['lastname'];

        // store session values
        $_SESSION['userid'] = $row['id'];
        $_SESSION['FullName'] = $fullname;
        $_SESSION['username'] = $row['username'];
        $_SESSION['is_admin'] = $row['is_admin'];
    }

    if (isset($result) && $result !== false) {
        mysqli_free_result($result);
    }

    if ($conn) {
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
</head>

<body>
    <?php
    if (!empty($_SESSION['username'])) {
        echo "<h1>Welcome to the Main Menu " . htmlspecialchars($_SESSION['FullName']) . "</h1>";

        if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
            echo "<h3>You're an admin, so you'll see the special menu.</h3>";
            echo "<br><a href=\"seeSalesFigures.php\">See Sales Numbers</a>";
        } else {
            echo "<h3>You're not an admin, so you'll see regular menu.</h3>";
        }

        echo "<br><a href=\"searchArtist.php\">Search for an artist</a>";
        echo "<br><a href=\"searchWork.php\">Search for a work of art</a>";
        echo "<br><a href=\"searchCustomer.php\">Search for a customer</a>";
    } else {
        echo "<h3>Sorry! Login failed!</h3>";
        echo "Go back to the <a href=\"index.php\">login page</a> and try again.";
    }

    echo "<br><br><a href=\"logout.php\">Log out</a>";
    ?>
</body>

</html>