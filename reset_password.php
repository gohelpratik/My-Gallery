<?php
require_once 'db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $newPass = $_POST['new_password'];
    $passPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{6,10}$/';

    if (!preg_match($passPattern, $newPass)) {
        $msg = "❌ Password must be 6-10 chars with upper, lower, digit & special";
    } else {

        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        
        
        $query = "UPDATE users SET password='$hashed' WHERE username='$username'";
        
        if (mysqli_query($conn, $query) && mysqli_affected_rows($conn) > 0) {
            $msg = "✅ Password changed! Redirecting...";
            echo '<meta http-equiv="refresh" content="2;url=login.php">';
            
        } else {
            $msg = "❌ Username not found or update failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | My Gallery</title>
  
    <style>
        
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
    
        /* Background Orbs */
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

        .glass-header { 
            background: rgba(15, 25, 45, 0.55); 
            backdrop-filter: blur(16px); 
            padding: 1.5rem; 
            text-align: center; 
        }
        
        h1 { 
            background: linear-gradient(135deg, #fff, #a0c0ff, #ffb8d4); 
            background-clip: text; 
            -webkit-background-clip: text; 
            color: transparent; 
        }
    
        .container { 
            flex: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 2rem; 
        }
    
        /* Glass Card Effect */
        .glass-card { 
            background: rgba(20, 30, 55, 0.5); 
            backdrop-filter: blur(16px); 
            border-radius: 2rem; 
            padding: 2rem; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.5); 
            border: 1px solid rgba(255,255,255,0.1); 
        }
    
        input { 
            width: 100%; 
            padding: 0.9rem; 
            margin-bottom: 1rem; 
            background: rgba(0,0,0,0.5); 
            border-radius: 2rem; 
            border: 1px solid rgba(255,255,255,0.2); 
            color: white; 
            outline: none; 
        }
    
        button { 
            width: 100%; 
            padding: 0.8rem; 
            background: linear-gradient(95deg, #ff7e5e, #feb47b); 
            border-radius: 3rem; 
            border: none; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        
        button:hover { 
            transform: scale(1.05); 
        }

        .msg { 
            text-align: center; 
            margin-top: 1rem; 
            color: #aaffdd; 
        }
        
        footer { 
            text-align: center; 
            padding: 1.2rem; 
            background: rgba(0,0,0,0.6); 
            color: #f0f0f0; 
            font-size: 0.8rem; 
        }
        
        .back-link { 
            text-align: center; 
            margin-top: 1rem; 
        }
        
        .back-link a { 
            color: #ffb347; 
            text-decoration: none; 
            font-size: 0.9rem; 
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
            <h2 style="color:white; margin-bottom:1rem; text-align:center;">🔐 Reset Password</h2>
        
            <form method="POST">
                <input type="text" name="username" placeholder="Your username" required>
                <input type="password" name="new_password" placeholder="New password (6-10 chars, Aa1!)" required>
                <button type="submit">⟳ Reset Password</button>
          
                <div class="msg">
                    <?php echo $msg; ?>
                </div>
            </form>
        
            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <footer>© 2026 My Gallery — secure your memories</footer>

</body>
</html>