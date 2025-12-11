<?php
// Simple welcome/login handler modeled after the Classwork Example welcome.php
include 'database.php';
// Show errors temporarily to diagnose blank page issues (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Read posted credentials (adapted to `User` table)
$userid = isset($_POST['userid']) ? trim($_POST['userid']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$fullname = '';

if (!$conn) {
    die("Database connection error.");
}

if ($userid !== '' && $password !== '') {
    // Simple check using current `User` table: UserID and Password
    $uid = (int) $userid;
    $sql = "SELECT UserID, Address, Password, FirstName, LastName, is_admin FROM `User` WHERE UserID = $uid AND Password = '" . mysqli_real_escape_string($conn, $password) . "' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $fullname = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? '')) ?: ('User #' . $row['UserID']);

        // store session values
        $_SESSION['userid'] = $row['UserID'];
        $_SESSION['firstname'] = $row['FirstName'] ?? '';
        $_SESSION['lastname'] = $row['LastName'] ?? '';
        $_SESSION['FullName'] = $fullname;
        $_SESSION['address'] = $row['Address'];
        $_SESSION['is_admin'] = $row['is_admin'];
        // If user is NOT an admin, redirect immediately to member.php
        if (empty($row['is_admin']) || $row['is_admin'] != 1) {
            if ($result)
                mysqli_free_result($result);
            if ($conn)
                mysqli_close($conn);
            header('Location: member.php');
            exit;
        }
    } else {
        // Login failed: keep user on index.php and show an error there
        if ($result && $result !== false) {
            mysqli_free_result($result);
        }
        if ($conn) {
            mysqli_close($conn);
        }
        // set a session flash message and redirect back to login
        $_SESSION['login_error'] = 'Invalid User ID or password.';
        header('Location: index.php');
        exit;
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
    <style>
        html,
        body {
            height: 100%;
            margin: 0
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            font-family: Arial, Helvetica, sans-serif
        }

        .box {
            background: white;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            text-align: center
        }

        .name {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 8px
        }

        .small {
            color: #555
        }
    </style>
</head>

<body>
    <div class="box">
        <?php
        if (!empty($_SESSION['userid'])) {
            // Authenticated
            echo '<div class="name">Welcome ' . htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']) . '</div>';
            echo '<div class="small">User ID: ' . (int) $_SESSION['userid'] . '</div>';
            if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                echo '<p style="margin-top:18px"><a href="admin.php">Go to Admin Dashboard</a> | <a href="member.php">Reader Functions</a> | <a href="logout.php">Log out</a></p>';
            } else {
                echo '<p style="margin-top:18px"><a href="member.php">Reader Functions</a> | <a href="logout.php">Log out</a></p>';
            }
        } else {
            // Not authenticated
            echo '<h3>Sorry! Login failed!</h3>';
            echo '<p>Go back to the <a href="index.php">login page</a> and try again.</p>';
        }
        ?>
    </div>
</body>

</html>