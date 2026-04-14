<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        
        // PASSWORD VERIFICATION
        if (password_verify($password, $row['password'])) {
            
            $_SESSION['activeUser'] = $row['username'];
            $_SESSION['user_id'] = $row['id'];
            
            header("Location: home.php");
            exit;
            
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | My Gallery</title>
  
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
            color: #000; 
            cursor: pointer; 
            transition: 0.3s ease;
        }

        button:hover {
            transform: scale(1.02);
            opacity: 0.9;
        }

        /* MESSAGES AND LINKS */
        .error-msg { 
            color: #ff8a7a; 
            text-align: center; 
            margin-top: 0.8rem; 
        }
        
        .links { 
            margin-top: 1.5rem; 
            text-align: center; 
        }
        
        .links a { 
            color: #ffb347; 
            text-decoration: none; 
            margin: 0 0.5rem; 
            font-size: 0.9rem;
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
            <h2>🌙 Welcome Back</h2>
            
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                
                <input type="password" name="password" placeholder="Password" required>
                
                <button type="submit">✦ Login ✦</button>
                
                <?php if($error): ?>
                    <div class="error-msg">❌ <?php echo $error; ?></div>
                <?php endif; ?>
            </form>

            <div class="links">
                <a href="reset_password.php">Forgot password?</a> | 
                <a href="register.php">Create account</a>
            </div>
        </div>
    </div>

    <footer>© 2026 My Gallery — where memories glow</footer>

</body>
</html>