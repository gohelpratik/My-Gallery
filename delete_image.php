<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) exit;
$id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT file_path FROM images WHERE id=$id AND user_id=$user_id");
if ($row = mysqli_fetch_assoc($res)) {
    if (file_exists($row['file_path'])) unlink($row['file_path']);
    mysqli_query($conn, "DELETE FROM images WHERE id=$id");
}
?>