<?php
// Session and Database initialization
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['activeUser'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check user admin status to show/hide Admin Panel link
$checkAdmin = mysqli_query($conn, "SELECT is_admin FROM users WHERE id=$user_id");
$adminRow = mysqli_fetch_assoc($checkAdmin);
$isAdmin = ($adminRow && $adminRow['is_admin'] == 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload | My Gallery</title>
    <style>
        /* Base Reset - Matches Home.php exactly */
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

        /* EXACT HEADER FROM HOME.PHP */
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

        /* EXACT NAVIGATION FROM HOME.PHP */
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

        /* Upload Content Area */
        .container { max-width: 1000px; margin: 3rem auto; padding: 0 2rem; flex: 1; }

        .upload-card { 
            background: rgba(25, 35, 65, 0.55); 
            backdrop-filter: blur(12px); 
            border-radius: 28px; 
            padding: 3rem; 
            text-align: center; 
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .upload-card h2 { color: #ffe6c7; margin-bottom: 2rem; font-weight: 600; }

        .file-input-label { 
            display: inline-block; 
            background: linear-gradient(135deg, #3b82f6, #a855f7); 
            padding: 0.8rem 2rem; 
            border-radius: 40px; 
            color: white; 
            cursor: pointer; 
            margin-bottom: 1.5rem; 
            font-weight: 600;
            transition: 0.3s;
        }
        
        .file-input-label:hover { transform: scale(1.05); filter: brightness(1.1); }

        input[type="file"] { display: none; }

        /* Preview Grid */
        .preview-grid { display: flex; flex-wrap: wrap; gap: 1.5rem; margin: 2rem 0; justify-content: center; }

        .preview-item { 
            position: relative; 
            width: 150px; 
            border-radius: 20px; 
            overflow: hidden; 
            border: 1px solid rgba(255,255,255,0.2); 
            background: rgba(0,0,0,0.4); 
            padding: 0.6rem; 
            transition: 0.3s; 
        }

        .preview-item:hover { transform: translateY(-5px); border-color: #ff9966; }
        .preview-item img { width: 100%; height: 110px; object-fit: cover; border-radius: 12px; }

        .remove-preview { 
            position: absolute; 
            top: 5px; right: 5px; 
            background: #dc2f02; 
            color: white; 
            border-radius: 50%; 
            width: 25px; height: 25px; 
            cursor: pointer; 
            text-align: center; 
            line-height: 25px; 
            font-weight: bold; 
            z-index: 5;
        }

        .title-input { 
            width: 100%; padding: 0.5rem; margin-top: 0.8rem; 
            border-radius: 20px; 
            background: rgba(0,0,0,0.6); 
            border: 1px solid rgba(255,255,255,0.3); 
            color: white; 
            text-align: center; 
            font-size: 0.8rem; 
            outline: none;
        }

        .upload-btn { 
            background: #2b9348; 
            padding: 0.8rem 2.5rem; 
            border-radius: 40px; 
            border: none; 
            font-weight: 600; 
            color: white; 
            cursor: pointer; 
            font-size: 1rem; 
            transition: 0.3s; 
        }

        .upload-btn:hover { transform: scale(1.05); filter: brightness(1.1); }

        /* Fullscreen Preview Modal */
        .modal { 
            display: none; position: fixed; top: 0; left: 0; 
            width: 100%; height: 100%; 
            background: rgba(0,0,0,0.9); 
            backdrop-filter: blur(20px); 
            justify-content: center; align-items: center; 
            z-index: 2000; 
        }

        .modal img { max-width: 90%; max-height: 85%; border-radius: 28px; border: 2px solid rgba(255,200,100,0.6); }
        #closeModal { position: absolute; top: 30px; right: 40px; font-size: 3.5rem; color: white; cursor: pointer; }

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

<div class="container">
    <div class="upload-card">
        <h2>✨ Add New Memories ✨</h2>
        
        <label class="file-input-label">📁 Select Multiple Images
            <input type="file" id="fileInput" accept="image/*" multiple>
        </label>

        <div class="preview-grid" id="previewGrid"></div>

        <button class="upload-btn" id="uploadBtn">🚀 Upload All Memories 🚀</button>
    </div>
</div>

<div id="imageModal" class="modal">
    <span id="closeModal">&times;</span>
    <img id="modalImage">
</div>

<footer>© 2026 My Gallery — every image tells a story</footer>

<script>
    let pendingImages = []; // Stores objects: { file, title, src }

    // Activity: Image modal display logic
    function openPreview(src) {
        document.getElementById("modalImage").src = src;
        document.getElementById("imageModal").style.display = "flex";
    }

    document.getElementById("closeModal").onclick = () => {
        document.getElementById("imageModal").style.display = "none";
    };

    window.onclick = (e) => {
        if (e.target === document.getElementById("imageModal")) {
            document.getElementById("imageModal").style.display = "none";
        }
    };

    // Activity: Handle multiple file selection and create previews
    document.getElementById("fileInput").addEventListener("change", function(e) {
        let files = Array.from(e.target.files);
        if (files.length === 0) return;
        
        pendingImages = []; 
        document.getElementById("previewGrid").innerHTML = "";

        files.forEach(file => {
            let reader = new FileReader();
            reader.onload = function(ev) {
                let src = ev.target.result;
                let defaultTitle = file.name.split('.')[0].slice(0, 25);
                pendingImages.push({ file: file, title: defaultTitle, src: src });
                renderPreviews();
            };
            reader.readAsDataURL(file);
        });
    });

    // Activity: Render preview UI with title inputs and delete options
    function renderPreviews() {
        let grid = document.getElementById("previewGrid");
        grid.innerHTML = "";
        pendingImages.forEach((img, idx) => {
            let div = document.createElement("div");
            div.className = "preview-item";
            
            let image = document.createElement("img");
            image.src = img.src;
            image.style.cursor = "pointer";
            image.onclick = () => openPreview(img.src);

            let remove = document.createElement("div");
            remove.innerText = "✖";
            remove.className = "remove-preview";
            remove.onclick = (e) => {
                e.stopPropagation();
                pendingImages.splice(idx, 1);
                renderPreviews();
            };

            let titleInput = document.createElement("input");
            titleInput.type = "text";
            titleInput.value = img.title;
            titleInput.placeholder = "Enter Title";
            titleInput.className = "title-input";
            titleInput.oninput = (e) => {
                pendingImages[idx].title = e.target.value;
            };

            div.appendChild(image);
            div.appendChild(remove);
            div.appendChild(titleInput);
            grid.appendChild(div);
        });
    }

    // Activity: Upload process using Fetch API and FormData
    document.getElementById("uploadBtn").addEventListener("click", async () => {
        if (pendingImages.length === 0) {
            alert("❌ Please select images first.");
            return;
        }
        
        const formData = new FormData();
        for (let i = 0; i < pendingImages.length; i++) {
            formData.append('images[]', pendingImages[i].file);
            formData.append('titles[]', pendingImages[i].title);
        }

        try {
            let resp = await fetch('upload_handler.php', { method: 'POST', body: formData });
            let result = await resp.text();
            alert(result);
            
            if (resp.ok) {
                window.location.href = "home.php"; // Redirect back to home
            }
        } catch (err) {
            alert("Connection error occurred.");
        }
    });
</script>
</body>
</html>