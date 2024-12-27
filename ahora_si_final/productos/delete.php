<?php
require '../database/connection.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
$stmt->execute([$id]);
header('Location: index.php');
exit;
?>
