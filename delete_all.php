<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT file_path FROM images WHERE user_id=$user_id");
while ($row = mysqli_fetch_assoc($result)) {
    if (file_exists($row['file_path'])) unlink($row['file_path']);
}
mysqli_query($conn, "DELETE FROM images WHERE user_id=$user_id");
?>