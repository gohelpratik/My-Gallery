<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['activeUser'])) {
    header("Location: login.php");
    exit();
}

// Check if the logged-in user has admin privileges
$user_id = $_SESSION['user_id'];
$checkAdmin = mysqli_query($conn, "SELECT is_admin FROM users WHERE id=$user_id");
$adminRow = mysqli_fetch_assoc($checkAdmin);

// Set $isAdmin variable for the header logic
$isAdmin = ($adminRow && $adminRow['is_admin'] == 1);

if (!$isAdmin) {
    echo "Access denied. You are not an admin.";
    exit();
}

// Logic: Delete user and their associated images
if (isset($_GET['delete_user'])) {
    $delId = intval($_GET['delete_user']);
    
    // Delete physical image files from the server first
    $imgRes = mysqli_query($conn, "SELECT file_path FROM images WHERE user_id=$delId");
    while ($img = mysqli_fetch_assoc($imgRes)) {
        if (file_exists($img['file_path'])) {
            unlink($img['file_path']);
        }
    }
    
    // Delete records from database
    mysqli_query($conn, "DELETE FROM images WHERE user_id=$delId");
    mysqli_query($conn, "DELETE FROM users WHERE id=$delId");
    
    header("Location: admin.php");
    exit();
}

// Fetch all users for the management table
$usersResult = mysqli_query($conn, "SELECT id, username, email, name, is_admin FROM users ORDER BY id DESC");

// Fetch all photos from all users for the gallery grid
$allImages = mysqli_query($conn, "SELECT images.id, images.title, images.file_path, images.user_id, users.username FROM images JOIN users ON images.user_id = users.id ORDER BY images.uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | My Gallery</title>
    <style>
        /* Base Reset - Matching Home and others exactly */
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

        /* HEADER (Exact same padding and background as home.php) */
        .glass-header { 
            background: rgba(15, 25, 45, 0.55); 
            backdrop-filter: blur(16px); 
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

        /* NAVIGATION (Exact same style) */
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

        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; flex: 1; }

        /* Section Headings */
        .section-heading { margin: 2rem 0 1rem; font-size: 1.5rem; color: #ffb347; border-left: 4px solid #ffb347; padding-left: 15px; }
        
        /* Table Card Styling */
        .card { 
            background: rgba(25, 35, 65, 0.55); 
            backdrop-filter: blur(12px); 
            padding: 1.5rem; 
            border-radius: 28px; 
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 3rem; 
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td { padding: 1.2rem; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.05); color: white; }
        th { background: rgba(255, 255, 255, 0.1); color: #ffb347; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }

        /* Buttons */
        .delete-btn { 
            background: #dc2f02; 
            color: white; 
            border: none; 
            padding: 0.5rem 1.2rem; 
            border-radius: 30px; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.2s;
            display: inline-block;
        }
        .delete-btn:hover { transform: scale(1.05); filter: brightness(1.2); }

        /* Global Photo Stream Grid */
        .gallery-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 1.8rem; 
        }

        .img-card { 
            background: rgba(25, 35, 65, 0.55); 
            backdrop-filter: blur(12px); 
            border-radius: 28px; 
            padding: 1rem; 
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: 0.3s;
            text-align: center;
        }
        .img-card:hover { transform: translateY(-8px); border-color: rgba(255,180,120,0.6); }
        
        .img-card img { 
            width: 100%; 
            height: 180px; 
            object-fit: cover; 
            border-radius: 18px; 
            margin-bottom: 1rem;
        }

        .img-info { margin-bottom: 1rem; }
        .img-info strong { display: block; color: #ffe6c7; margin-bottom: 4px; font-size: 1.1rem; }
        .img-info span { font-size: 0.85rem; color: #a0c0ff; opacity: 0.8; }

        footer { text-align: center; padding: 1.5rem; background: rgba(0,0,0,0.6); color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-top: auto; }
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
        <a href="admin.php" style="background: linear-gradient(135deg, #ff7e5e, #feb47b); color: #0a0f1e;">🔧 Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php">🚪 Logout</a>
</nav>

<div class="container">
    
    <h2 class="section-heading">👥 User Management</h2>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = mysqli_fetch_assoc($usersResult)): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo $u['is_admin'] ? '<span style="color:#ffb347;">Admin</span>' : 'User'; ?></td>
                    <td>
                        <a href="?delete_user=<?php echo $u['id']; ?>" 
                           class="delete-btn" 
                           onclick="return confirm('🚨 Are you sure? This will delete the user and all their photos forever!')">
                           Delete User
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <h2 class="section-heading">🖼️ Global Photo Stream</h2>
    <div class="gallery-grid">
        <?php while($img = mysqli_fetch_assoc($allImages)): ?>
        <div class="img-card">
            <img src="<?php echo $img['file_path']; ?>" alt="Gallery Image">
            <div class="img-info">
                <strong><?php echo htmlspecialchars($img['title']); ?></strong>
                <span>👤 By: <?php echo htmlspecialchars($img['username']); ?></span>
            </div>
            <a href="delete_image.php?id=<?php echo $img['id']; ?>&admin=1" 
               class="delete-btn" 
               onclick="return confirm('Delete this specific photo?')">
               Remove Photo
            </a>
        </div>
        <?php endwhile; ?>
    </div>

</div>

<footer>
    © 2026 My Gallery — Administrative Access Only
</footer>

</body>
</html>