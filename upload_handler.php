<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in";
    exit;
}
$user_id = $_SESSION['user_id'];
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $titles = $_POST['titles'];
    $files = $_FILES['images'];
    $count = 0;
    for ($i = 0; $i < count($files['name']); $i++) {
        $originalName = $files['name'][$i];
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $uploadDir . $safeName;
        if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
            $title = mysqli_real_escape_string($conn, $titles[$i]);
            mysqli_query($conn, "INSERT INTO images (user_id, title, file_path) VALUES ($user_id, '$title', '$targetPath')");
            $count++;
        }
    }
    echo "✅ Successfully uploaded $count image(s)!";
} else {
    echo "No files received";
}
?>