<?php
include 'database.php';

// Clean signup.php: accepts optional numeric UserID, first/last name, address, password
// On success redirects to index.php

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $requestedId = isset($_POST['userid']) && $_POST['userid'] !== '' ? trim($_POST['userid']) : '';

    if ($firstname === '' || $lastname === '' || $address === '' || $password === '') {
        $error = 'First name, last name, address and password are required.';
    } else {
        // Determine UserID: use requested if provided and available, otherwise MAX(UserID)+1
        $newId = 0;

        if ($requestedId !== '') {
            if (!ctype_digit($requestedId)) {
                $error = 'Requested User ID must be a positive integer.';
            } else {
                $candidate = (int) $requestedId;
                $checkRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM `User` WHERE UserID = $candidate");
                $checkRow = $checkRes ? mysqli_fetch_assoc($checkRes) : null;
                $exists = $checkRow && isset($checkRow['cnt']) && $checkRow['cnt'] > 0;
                if ($exists) {
                    $error = 'Requested User ID is already taken. Please choose a different ID or leave blank.';
                } else {
                    $newId = $candidate;
                }
                if ($checkRes)
                    mysqli_free_result($checkRes);
            }
        }

        if ($error === '') {
            if ($newId === 0) {
                $res = mysqli_query($conn, "SELECT MAX(UserID) AS maxid FROM `User`");
                $row = $res ? mysqli_fetch_assoc($res) : null;
                $maxid = $row && isset($row['maxid']) ? (int) $row['maxid'] : 0;
                $newId = $maxid + 1;
                if ($res)
                    mysqli_free_result($res);
            }

            // Escape inputs
            $firstEsc = mysqli_real_escape_string($conn, $firstname);
            $lastEsc = mysqli_real_escape_string($conn, $lastname);
            $addrEsc = mysqli_real_escape_string($conn, $address);
            $passEsc = mysqli_real_escape_string($conn, $password);

            // Insert into User (includes FirstName and LastName)
            $sql = "INSERT INTO `User` (UserID, Address, Password, FirstName, LastName) VALUES ($newId, '$addrEsc', '$passEsc', '$firstEsc', '$lastEsc')";
            if (mysqli_query($conn, $sql)) {
                // create Member entry (ignore errors)
                mysqli_query($conn, "INSERT INTO `Member` (UserID) VALUES ($newId)");
                mysqli_close($conn);
                header('Location: index.php');
                exit;
            } else {
                $error = 'Could not create account: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Disruptive Library</title>
    <style>
        * {
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
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .welcome-section {
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }

        .welcome-section h1 {
            font-size: 2.5em;
            font-weight: 700;
        }

        .signup-section {
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-container {
            width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95em;
            font-family: inherit;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .error {
            color: #b00020;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <h1>Create Account</h1>
        </div>
        <div class="signup-section">
            <div class="form-container">
                <?php if ($error !== ''): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form id="signupForm" method="post" action="signup.php">
                    <div class="form-group">
                        <label for="userid">User ID (optional)</label>
                        <input type="number" id="userid" name="userid" min="1"
                            placeholder="Leave blank to auto-generate">
                    </div>

                    <div class="form-group">
                        <label for="firstname">First Name *</label>
                        <input type="text" id="firstname" name="firstname" maxlength="30" required>
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name *</label>
                        <input type="text" id="lastname" name="lastname" maxlength="30" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address *</label>
                        <input type="text" id="address" name="address" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit">Create Account</button>
                </form>

                <div class="login-link">Already have an account? <a href="index.php">Log in here</a></div>
            </div>
        </div>
    </div>
</body>

</html>