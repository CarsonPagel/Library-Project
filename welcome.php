<?php
// welcome.php - process login POST, start session, and show welcome page
include 'database.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        // Use prepared statement to avoid SQL injection
        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id, $db_username, $db_password_hash);
            if (mysqli_stmt_fetch($stmt)) {
                // If your DB stores hashed passwords (recommended), use password_verify
                $passwordOk = false;
                if (password_verify($password, $db_password_hash)) {
                    $passwordOk = true;
                } else {
                    // Fallback: if passwords were stored unhashed (not recommended)
                    if ($password === $db_password_hash) {
                        $passwordOk = true;
                    }
                }

                if ($passwordOk) {
                    // Successful login: set session and redirect (POST->GET)
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $id; // integer user id
                    $_SESSION['username'] = $db_username;
                    header('Location: welcome.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database error.';
        }
    }
}

// If user already authenticated via session, show welcome; otherwise show login error and link back.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome - Disruptive Library</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            padding: 40px;
        }

        .card {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .error {
            color: #b00020;
            margin-bottom: 12px;
        }

        a {
            color: #667eea;
        }
    </style>
</head>

<body>
    <div class="card">
        <?php if (isset($_SESSION['user_id'])): ?>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Your user id is <?php echo (int) $_SESSION['user_id']; ?>.</p>
            <p><a href="logout.php">Log out</a></p>
        <?php else: ?>
            <h1>Login Result</h1>
            <?php if ($error !== ''): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <p><a href="index.php">Back to login</a></p>
        <?php endif; ?>
    </div>
</body>

</html>
<?php include 'database.php'; ?>

<!DOCTYPE html>
<html lang="en">
<?php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Hello Loser";
}
?>

</html>