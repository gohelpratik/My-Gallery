<?php

// DATABASE CONNECTION
require_once 'db.php';

$error = '';

// FORM SUBMISSION HANDLING
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // INPUT SANITIZATION
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // USERNAME VALIDATION
    if (!preg_match('/^[A-Za-z0-9]{3,20}$/', $username)) {
        $error = "❌ Username must be 3-20 letters/numbers";
    } 
    // EMAIL VALIDATION
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Invalid email address";
    } 
    // PASSWORD VALIDATION
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{6,10}$/', $password)) {
        $error = "❌ Password must be 6-10 chars with upper, lower, digit & special";
    } 
    else {
        
        // CHECK EXISTING USERNAME AND EMAIL
        $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

        if (mysqli_num_rows($checkUser) > 0) {
            $error = "❌ Username already taken. Choose another.";
        } 
        elseif (mysqli_num_rows($checkEmail) > 0) {
            $error = "❌ Email already registered. Use another email.";
        } 
        else {
            
            // PASSWORD HASHING
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            // INSERT NEW USER
            $query = "INSERT INTO users (username, email, password, name) VALUES ('$username', '$email', '$hashed', '$username')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: login.php?msg=registered");
                exit;
            } 
            else {
                $error = "❌ Database error. Please try again.";
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
    <title>Register | My Gallery</title>

    <style>
        /* CSS RESET AND GLOBAL STYLES */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body { 
            font-family: 'Poppins', system-ui, sans-serif; 
            background: radial-gradient(circle at 10% 30%, #0a0f1e, #03050b); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }

        /* ANIMATED BACKGROUND ORBS */
        .orb { 
            position: fixed; 
            border-radius: 50%; 
            filter: blur(80px); 
            opacity: 0.5; 
            z-index: -2; 
            animation: floatOrb 18s infinite alternate; 
        }

        .orb1 { 
            width: 60vw; 
            height: 60vw; 
            background: radial-gradient(circle, #ff3c6e, #ff9a3c); 
            top: -20vh; 
            left: -20vw; 
        }

        .orb2 { 
            width: 70vw; 
            height: 70vw; 
            background: radial-gradient(circle, #3b82f6, #a855f7); 
            bottom: -30vh; 
            right: -30vw; 
            animation-duration: 25s; 
        }

        @keyframes floatOrb { 
            0% { transform: translate(0,0) scale(1); } 
            100% { transform: translate(8%,8%) scale(1.2); } 
        }

        /* HEADER STYLING */
        .glass-header { 
            background: rgba(15, 25, 45, 0.55); 
            backdrop-filter: blur(16px); 
            padding: 1.5rem; 
            text-align: center; 
            border-bottom: 1px solid rgba(255,255,255,0.2); 
        }

        h1 { 
            background: linear-gradient(135deg, #fff, #a0c0ff, #ffb8d4); 
            background-clip: text; 
            -webkit-background-clip: text; 
            color: transparent; 
            font-size: 2rem; 
        }

        /* CARD CONTAINER */
        .container { 
            flex: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 2rem; 
        }

        /* GLASS CARD DESIGN */
        .glass-card { 
            background: rgba(20, 30, 55, 0.5); 
            backdrop-filter: blur(16px); 
            border-radius: 2rem; 
            padding: 2.5rem; 
            width: 100%; 
            max-width: 400px; 
            border: 1px solid rgba(255,255,255,0.2); 
        }

        .glass-card h2 { 
            color: white; 
            margin-bottom: 1.5rem; 
            text-align: center; 
        }

        /* INPUT FIELDS */
        input { 
            width: 100%; 
            padding: 0.9rem 1rem; 
            margin-bottom: 1rem; 
            background: rgba(0,0,0,0.5); 
            border: 1px solid rgba(255,255,255,0.2); 
            border-radius: 2rem; 
            color: white; 
            font-size: 1rem; 
            outline: none; 
        }

        input:focus { 
            border-color: #ff9966; 
        }

        /* BUTTON STYLING */
        button { 
            width: 100%; 
            padding: 0.8rem; 
            background: linear-gradient(95deg, #ff7e5e, #feb47b); 
            border: none; 
            border-radius: 3rem; 
            font-weight: bold; 
            cursor: pointer; 
        }

        /* MESSAGES AND LINKS */
        .info { 
            font-size: 0.7rem; 
            text-align: center; 
            margin-top: 0.5rem; 
            color: #ccc; 
        }

        .error-msg { 
            color: #ffaa88; 
            text-align: center; 
            margin-top: 0.5rem; 
        }

        .links { 
            text-align: center; 
            margin-top: 1rem; 
        }

        .links a { 
            color: #ffb347; 
            text-decoration: none; 
        }

        /* FOOTER */
        footer { 
            text-align: center; 
            padding: 1.2rem; 
            background: rgba(0,0,0,0.6); 
            color: #f0f0f0; 
            font-size: 0.8rem; 
        }
    </style>
</head>

<body>

    <div class="orb orb1"></div>
    <div class="orb orb2"></div>

    <header class="glass-header">
        <h1>✨ My Gallery ✨</h1>
    </header>

    <div class="container">
        <div class="glass-card">
            <h2>🚀 Create Account</h2>
            
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username (letters, 3-20)" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password (6-10 chars, 1 upper, 1 lower, 1 digit, 1 special)" required>
                
                <button type="submit">✦ Register ✦</button>
                
                <div class="info">Password example: Aa1!abc</div>
                
                <?php if($error): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
            
            <div class="links">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <footer>© 2026 My Gallery — join the visual journey</footer>

</body>
</html>