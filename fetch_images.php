<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT id, title, file_path FROM images WHERE user_id = $user_id";
if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $query .= " AND title LIKE '%$search%'";
}
$query .= " ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $query);
$images = [];
while ($row = mysqli_fetch_assoc($result)) {
    $images[] = $row;
}
header('Content-Type: application/json');
echo json_encode($images);
?>