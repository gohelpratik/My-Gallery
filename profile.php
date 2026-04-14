<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['activeUser'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Check user admin privileges for the nav link
$checkAdmin = mysqli_query($conn, "SELECT is_admin FROM users WHERE id=$user_id");
$adminRow = mysqli_fetch_assoc($checkAdmin);
$isAdmin = ($adminRow && $adminRow['is_admin'] == 1);

// Fetch current user data
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username, email, name, avatar FROM users WHERE id=$user_id"));

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $avatarPath = $user['avatar'];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarName = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $target = $uploadDir . $avatarName;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatarPath = $target;
        }
    }

    $query = "UPDATE users SET name='$name', email='$email', avatar='$avatarPath' WHERE id=$user_id";
    mysqli_query($conn, $query);
    $msg = "Profile updated!";
    
    // Refresh user data
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username, email, name, avatar FROM users WHERE id=$user_id"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | My Gallery</title>
    <style>
        /* Base Reset - Matching Home and Upload exactly */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 10% 30%, #0a0f1e, #03050b);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Animated Background Orbs */
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.4; z-index: -2; animation: floatOrb 18s infinite alternate; }
        .orb1 { width: 60vw; height: 60vw; background: radial-gradient(circle, #ff3c6e, #ff9a3c); top: -20vh; left: -20vw; }
        .orb2 { width: 70vw; height: 70vw; background: radial-gradient(circle, #3b82f6, #a855f7); bottom: -30vh; right: -30vw; animation-duration: 25s; }

        @keyframes floatOrb { 
            0% { transform: translate(0,0) scale(1); } 
            100% { transform: translate(8%,8%) scale(1.2); } 
        }

        /* HEADER SECTION (Consistent with Home.php) */
        .glass-header { 
            background: rgba(15, 25, 45, 0.55); 
            backdrop-filter: blur(166px); 
            padding: 1rem 2rem; 
            text-align: center; 
            border-bottom: 1px solid rgba(255,255,255,0.2); 
        }

        h1 { 
            background: linear-gradient(135deg, #fff, #a0c0ff, #ffb8d4); 
            background-clip: text; 
            -webkit-background-clip: text; 
            color: transparent; 
        }

        /* NAVIGATION (Consistent with Home.php) */
        .neo-nav { 
            display: flex; 
            justify-content: center; 
            gap: 1.5rem; 
            padding: 1rem; 
            background: rgba(5, 10, 25, 0.7); 
            backdrop-filter: blur(12px); 
            flex-wrap: wrap; 
        }

        .neo-nav a { 
            text-decoration: none; 
            padding: 0.5rem 1.8rem; 
            border-radius: 40px; 
            background: rgba(255,255,255,0.05); 
            color: #eef5ff; 
            transition: 0.2s; 
            border: 1px solid rgba(255,255,255,0.1); 
        }

        .neo-nav a:hover { 
            background: linear-gradient(135deg, #ff7e5e, #feb47b); 
            color: #0a0f1e; 
            transform: translateY(-3px); 
        }

        /* Profile Card Styling */
        .profile-container { flex: 1; display: flex; justify-content: center; align-items: center; padding: 2rem; }

        .profile-card { 
            background: rgba(25, 35, 65, 0.55); 
            backdrop-filter: blur(12px); 
            border-radius: 28px; 
            padding: 3rem; 
            width: 100%;
            max-width: 450px; 
            text-align: center; 
            border: 1px solid rgba(255,255,255,0.2); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .avatar { 
            width: 130px; 
            height: 130px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 4px solid #ffb347; 
            margin-bottom: 1.5rem; 
            box-shadow: 0 0 20px rgba(255, 179, 71, 0.3);
        }

        h3 { color: white; font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #ccc; margin-bottom: 1.5rem; }

        input { 
            width: 100%; 
            padding: 0.8rem 1.2rem; 
            margin: 0.6rem 0; 
            border-radius: 30px; 
            background: rgba(0,0,0,0.6); 
            border: 1px solid rgba(255,255,255,0.2); 
            color: white; 
            outline: none;
        }

        input:focus { border-color: #ff9966; }

        .btn { 
            padding: 0.8rem 2rem; 
            border-radius: 40px; 
            border: none; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.3s; 
            margin-top: 1rem;
        }

        #editBtn { background: linear-gradient(135deg, #ff7e5e, #feb47b); color: #0a0f1e; }
        .save-btn { background: #2b9348; color: white; width: 100%; }

        .btn:hover { transform: scale(1.05); filter: brightness(1.1); }

        footer { text-align: center; padding: 1.5rem; background: rgba(0,0,0,0.6); color: #f0f0f0; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>

<header class="glass-header">
    <h1>✨ My Gallery ✨</h1>
</header>

<nav class="neo-nav">
    <a href="home.php">🏠 Home</a>
    <a href="upload.php">📸 Upload</a>
    <a href="profile.php">👤 Profile</a>
    <?php if ($isAdmin): ?>
        <a href="admin.php" class="admin-link">🔧 Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php">🚪 Logout</a>
</nav>

<div class="profile-container">
    <div class="profile-card">
        <img id="avatarImg" class="avatar" src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/130?text=Avatar'; ?>">
        
        <h3 id="displayName"><?php echo htmlspecialchars($user['name'] ?: $user['username']); ?></h3>
        <p id="displayEmail"><?php echo htmlspecialchars($user['email']); ?></p>
        
        <button id="editBtn" class="btn">✏️ Edit Profile</button>

        <div id="editForm" style="display:none; margin-top:2rem;">
            <form method="POST" enctype="multipart/form-data">
                <p style="text-align:left; font-size:0.8rem; margin-bottom:5px; padding-left:15px;">Change Avatar:</p>
                <input type="file" name="avatar" accept="image/*">
                
                <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                
                <button type="submit" class="btn save-btn">💾 Save Changes</button>
                
                <?php if($msg): ?>
                    <p style="color: lightgreen; margin-top: 1rem; font-weight: bold;"><?php echo $msg; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<footer>© 2026 My Gallery — customize your identity</footer>

<script>
    // Logic to toggle the edit form
    document.getElementById("editBtn").onclick = function() {
        let form = document.getElementById("editForm");
        if (form.style.display === "none") {
            form.style.display = "block";
            this.style.display = "none"; // Hide edit button when form is open
        }
    };
</script>

</body>
</html>