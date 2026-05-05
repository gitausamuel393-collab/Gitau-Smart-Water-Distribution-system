<?php
require '../db.php';
header('Content-Type: application/json');
$device_id=$_GET['device_id']??0;
$stmt=$pdo->prepare("SELECT command FROM devices WHERE id=?"); $stmt->execute([$device_id]);
$command=$stmt->fetchColumn();
$pdo->prepare("UPDATE devices SET command='NONE' WHERE id=?")->execute([$device_id]);
echo json_encode(['command'=>$command?:'NONE']);
