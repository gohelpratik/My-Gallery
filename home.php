<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['activeUser'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$currentUser = $_SESSION['activeUser'];
$user_id = $_SESSION['user_id'];

// check user admin
$checkAdmin = mysqli_query($conn, "SELECT is_admin FROM users WHERE id=$user_id");
$adminRow = mysqli_fetch_assoc($checkAdmin);
$isAdmin = ($adminRow && $adminRow['is_admin'] == 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Gallery | Home</title>
    <style>
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

        /* Navigation Styling */
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

        

        /* Main Content & Gallery Area */
        .main-content { flex: 1; }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }

        .search-section { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: space-between; 
            align-items: center; 
            gap: 1rem; 
            background: rgba(20,30,55,0.45); 
            backdrop-filter: blur(12px); 
            padding: 1rem 2rem; 
            border-radius: 60px; 
            margin-bottom: 2rem; 
        }

        #searchBox { 
            flex: 2; 
            padding: 0.8rem 1.5rem; 
            border-radius: 60px; 
            border: none; 
            background: rgba(0,0,0,0.6); 
            color: white; 
            font-size: 1rem; 
            outline: none; 
            min-width: 200px; 
        }

        #searchBox:focus { border: 1px solid #ff9966; }

        .counter { 
            background: rgba(0,0,0,0.5); 
            padding: 0.5rem 1.2rem; 
            border-radius: 60px; 
            font-weight: 600; 
            color: white; 
        }

        .gallery { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); 
            gap: 1.8rem; 
        }

        .card { 
            background: rgba(25, 35, 65, 0.55); 
            backdrop-filter: blur(12px); 
            border-radius: 28px; 
            overflow: hidden; 
            border: 1px solid rgba(255,255,255,0.2); 
            transition: 0.3s; 
            animation: fadeUp 0.5s ease-out; 
        }

        @keyframes fadeUp { 
            from { opacity: 0; transform: translateY(30px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        .card:hover { 
            transform: translateY(-10px) scale(1.02); 
            border-color: rgba(255,180,120,0.6); 
        }

        .card-img { width: 100%; aspect-ratio: 1/1; object-fit: cover; cursor: pointer; }
        .card-info { padding: 1rem; }
        .img-title { font-weight: 600; margin-bottom: 0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #ffe6c7; }

        .buttons { display: flex; gap: 0.8rem; margin-top: 0.5rem; }
        button { padding: 0.4rem 1rem; border-radius: 30px; border: none; font-weight: 600; cursor: pointer; transition: 0.2s; background: rgba(0,0,0,0.6); color: white; }

        .download { background: #2b9348; }
        .delete { background: #dc2f02; }
        button:hover { transform: scale(1.05); filter: brightness(1.1); }

        /* Modal View */
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; left: 0; 
            width: 100%; height: 100%; 
            background: rgba(0,0,0,0.85); 
            backdrop-filter: blur(20px); 
            justify-content: center; 
            align-items: center; 
            z-index: 1000; 
        }

        .modal img { max-width: 90%; max-height: 85%; border-radius: 28px; border: 2px solid rgba(255,200,100,0.6); }
        #closeModal { position: absolute; top: 30px; right: 40px; font-size: 3rem; color: white; cursor: pointer; }

        .empty-state { text-align: center; padding: 3rem; background: rgba(0,0,0,0.3); border-radius: 40px; color: #ddd; }
        .empty-state a { color: #ffb347; text-decoration: none; }

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

<div class="main-content">
    <div class="container">
        <div class="search-section">
            <input type="text" id="searchBox" placeholder="🔍 Search by image title..." onkeyup="filterGallery()">
            <button id="deleteAllBtn" class="delete" style="padding: 0.8rem 1.5rem;" onclick="deleteAllPhotos()">🗑 Delete All</button>
            <div class="counter" id="imageCount">✨ 0 memories</div>
        </div>

        <div class="gallery" id="galleryContainer"></div>
    </div>
</div>

<div id="imageModal" class="modal">
    <span id="closeModal">&times;</span>
    <img id="modalImage">
</div>

<footer>© 2026 My Gallery — every image tells a story</footer>

<script>
    async function fetchImages(search = "") {
        let url = "fetch_images.php";
        if (search) url += "?search=" + encodeURIComponent(search);
        let resp = await fetch(url);
        let data = await resp.json();
        return data;
    }

    async function renderGallery(filter = "") {
        let images = await fetchImages(filter);
        let container = document.getElementById("galleryContainer");
        let countSpan = document.getElementById("imageCount");

        countSpan.innerText = `✨ ${images.length} ${images.length === 1 ? 'masterpiece' : 'masterpieces'} ✨`;

        if (images.length === 0) {
            container.innerHTML = `<div class="empty-state">🌙 No images match — <a href="upload.php">upload new magic</a></div>`;
            return;
        }

        container.innerHTML = "";
        images.forEach((img, idx) => {
            let card = document.createElement("div");
            card.className = "card";
            card.style.animationDelay = `${idx * 0.03}s`;

            let imgEl = document.createElement("img");
            imgEl.src = img.file_path;
            imgEl.className = "card-img";
            imgEl.alt = img.title;
            imgEl.onclick = () => {
                document.getElementById("modalImage").src = img.file_path;
                document.getElementById("imageModal").style.display = "flex";
            };

            let info = document.createElement("div");
            info.className = "card-info";

            let titleSpan = document.createElement("div");
            titleSpan.className = "img-title";
            titleSpan.innerText = img.title;

            let btnDiv = document.createElement("div");
            btnDiv.className = "buttons";

            let downloadBtn = document.createElement("button");
            downloadBtn.innerText = "⬇ Download";
            downloadBtn.className = "download";
            downloadBtn.onclick = (e) => {
                e.stopPropagation();
                let a = document.createElement("a");
                a.href = img.file_path;
                a.download = img.title.replace(/\s/g, "_") + ".jpg";
                a.click();
            };

            let delBtn = document.createElement("button");
            delBtn.innerText = "🗑 Delete";
            delBtn.className = "delete";
            delBtn.onclick = async (e) => {
                e.stopPropagation();
                if (confirm(`Delete "${img.title}"?`)) {
                    let formData = new FormData();
                    formData.append('id', img.id);
                    await fetch('delete_image.php', { method: 'POST', body: formData });
                    renderGallery(document.getElementById("searchBox").value);
                }
            };

            btnDiv.appendChild(downloadBtn);
            btnDiv.appendChild(delBtn);
            info.appendChild(titleSpan);
            info.appendChild(btnDiv);
            card.appendChild(imgEl);
            card.appendChild(info);
            container.appendChild(card);
        });
    }

    async function deleteAllPhotos() {
        let images = await fetchImages();
        if (images.length === 0) {
            alert("Your gallery is already empty!");
            return;
        }
        let confirmAction = confirm("🚨 ARE YOU SURE? 🚨\nThis will delete ALL your photos forever. This cannot be undone!");
        if (confirmAction) {
            await fetch('delete_all.php', { method: 'POST' });
            renderGallery(document.getElementById("searchBox").value);
            alert("All photos have been successfully deleted.");
        }
    }

    function filterGallery() {
        let searchTerm = document.getElementById("searchBox").value;
        renderGallery(searchTerm);
    }

    window.onload = () => {
        renderGallery("");
    };

    document.getElementById("closeModal").onclick = () => {
        document.getElementById("imageModal").style.display = "none";
    };

    window.onclick = (e) => {
        if (e.target === document.getElementById("imageModal")) {
            document.getElementById("imageModal").style.display = "none";
        }
    };
</script>
</body>
</html>