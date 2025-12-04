<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Disruptive Library</title>
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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .welcome-section {
            padding: 60px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .welcome-section h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .welcome-section p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .features {
            list-style: none;
        }

        .features li {
            margin-bottom: 15px;
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .features li:before {
            content: "✓";
            font-size: 1.5em;
            margin-right: 12px;
            font-weight: bold;
        }

        .signup-section {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .signup-section h2 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #333;
        }

        .signup-section p {
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.95em;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95em;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .success-message {
            display: none;
            padding: 15px;
            background: #4caf50;
            color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .welcome-section,
            .signup-section {
                padding: 40px 30px;
            }

            .welcome-section h1 {
                font-size: 2em;
            }

            .signup-section h2 {
                font-size: 1.5em;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <h1>Welcome to Disruptive Library</h1>
            <p>Revolutionizing how you discover, access, and share knowledge. Join our community of learners and
                innovators.</p>
            <ul class="features">
                <li>Access thousands of digital resources</li>
                <li>Connect with like-minded learners</li>
                <li>Personalized reading recommendations</li>
                <li>Participate in exclusive book clubs</li>
                <li>Download and share content easily</li>
            </ul>
        </div>

        <div class="signup-section">
            <h2>Get Started Today</h2>
            <p>Join thousands of members already exploring new ideas</p>

            <div class="success-message" id="successMessage">
                Thank you for signing up! We'll be in touch shortly.
            </div>

            <form id="signupForm" onsubmit="handleSubmit(event)">
                <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>

                <div class="form-group">
                    <label for="interests">Interests *</label>
                    <textarea id="interests" name="interests"
                        placeholder="Tell us about your interests and what you'd like to read..." required></textarea>
                </div>

                <button type="submit">Create My Account</button>
            </form>
        </div>
    </div>

    <script>
        function handleSubmit(event) {
            event.preventDefault();

            const formData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                interests: document.getElementById('interests').value
            };

            // Log form data (in a real application, this would be sent to a server)
            console.log('Form submitted with data:', formData);

            // Show success message
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'block';

            // Reset form
            document.getElementById('signupForm').reset();

            // Hide success message after 5 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }
    </script>
</body>

</html>