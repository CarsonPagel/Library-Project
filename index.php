<?php include 'database.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Disruptive Library</title>
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

        .login-section {
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

        .signup-link {
            margin-top: 20px;
            text-align: center;
            color: #666;
        }

        .signup-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .signup-link a:hover {
            color: #764ba2;
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
                <form id="loginForm" action="welcome.php" method="post">
                    <div class="form-group">
                        <label for="userid">User ID *</label>
                        <input type="number" id="userid" name="userid" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit">Log In</button>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleSubmit(event) {
            event.preventDefault();

            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            };

            console.log('Login attempt with data:', formData);
            // TODO: Add actual login validation via PHP
            document.getElementById('loginForm').reset();
        }
    </script>
</body>

</html>