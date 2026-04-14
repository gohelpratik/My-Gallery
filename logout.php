<?php

session_start();

// LOGOUT CONFIRMATION LOGIC
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    
    session_destroy();
    
  
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout | My Gallery</title>

    <style>
        /* CSS RESET AND GLOBAL STYLES */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: radial-gradient(circle at 10% 30%, #0a0f1e, #03050b); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
        }

        /* GLASS CARD DESIGN */
        .glass-card { 
            background: rgba(20, 30, 55, 0.5); 
            backdrop-filter: blur(16px); 
            border-radius: 2rem; 
            padding: 2rem; 
            text-align: center; 
            max-width: 400px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* BUTTON STYLING */
        button { 
            background: linear-gradient(95deg, #ff7e5e, #feb47b); 
            border: none; 
            padding: 0.7rem 1.5rem; 
            border-radius: 2rem; 
            margin: 0.5rem; 
            cursor: pointer; 
            font-weight: bold; 
            transition: 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }

        /* TEXT STYLING */
        h2 { 
            color: white; 
            margin-bottom: 1rem; 
        }

        p { 
            color: #ddd; 
            margin-bottom: 1.5rem; 
        }

        a { 
            text-decoration: none; 
            color: white; 
        }

        /* FOOTER STYLING */
        footer { 
            text-align: center; 
            padding: 1.2rem; 
            background: rgba(0,0,0,0.6); 
            color: #f0f0f0; 
            width: 100%; 
            position: fixed; 
            bottom: 0; 
            font-size: 0.85rem; 
        }
    </style>
</head>

<body>

    <div class="glass-card">
        
        <h2>🌙 Ready to leave?</h2>
        <p>Your gallery will wait for you.</p>

        /* ACTION BUTTONS */
        <button onclick="window.location.href='logout.php?confirm=yes'">🚪 Yes, Logout</button>
        <button onclick="window.location.href='home.php'">✨ Stay</button>

    </div>

    <footer>© 2026 My Gallery — see you soon</footer>

</body>
</html>