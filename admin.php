<?php
session_start();
// Admin-only page
if (empty($_SESSION['userid']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7fafc;
            margin: 0;
            padding: 40px
        }

        .wrap {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            padding: 28px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06)
        }

        h1 {
            margin-top: 0
        }

        .meta {
            color: #555;
            margin-bottom: 18px
        }

        a.button {
            display: inline-block;
            padding: 8px 12px;
            background: #2b6cb0;
            color: #fff;
            border-radius: 6px;
            text-decoration: none
        }

        <?php
        session_start();
        // Admin-only page
        if (empty($_SESSION['userid']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            header('Location: index.php');
            exit;
        }
        ?>
        < !DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Dashboard - Disruptive Library</title><style>* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .welcome-section {
            padding: 28px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }

        .welcome-section h1 {
            font-size: 2em;
            font-weight: 700;
        }

        .login-section {
            padding: 32px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-container {
            width: 100%;
        }

        .wrap {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        h2.page-title {
            margin-top: 0;
            font-size: 1.25rem
        }

        .meta {
            color: #555;
            margin-bottom: 12px
        }

        a.button {
            display: inline-block;
            padding: 8px 12px;
            background: #2b6cb0;
            color: #fff;
            border-radius: 6px;
            text-decoration: none
        }

        a.button.secondary {
            background: #e53e3e
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <h1>Disruptive Library</h1>
        </div>

        <div class="login-section">
            <div class="form-container">
                <div class="wrap">
                    <h2 class="page-title">Admin Dashboard</h2>
                    <div class="meta">Signed in as:
                        <?php echo htmlspecialchars($_SESSION['FullName'] ?? ('User #' . (int) ($_SESSION['userid'] ?? 0))); ?>
                    </div>

                    <p>Administrative actions go here. This is a placeholder dashboard for admin-only features.</p>

                    <p style="margin-top:18px;">
                        <a class="button" href="welcome.php">Main</a>
                        <a class="button secondary" href="logout.php" style="margin-left:8px">Log out</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>