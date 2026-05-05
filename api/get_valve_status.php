<?php
require '../db.php';
header('Content-Type: application/json');
$API_KEY=getenv('IOT_API_KEY')?:'meru_iot_2024';
if(($_GET['key']??'')!==$API_KEY){ http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
$uid=$_GET['user_id']??null;
if(!$uid){ echo json_encode(['error'=>'Missing user_id']); exit; }
$stmt=$pdo->prepare("SELECT id,name,valve_status,command FROM devices WHERE user_id=? ORDER BY name ASC");
$stmt->execute([$uid]); echo json_encode($stmt->fetchAll());
