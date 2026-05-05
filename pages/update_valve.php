<?php
session_start(); require '../db.php';
header('Content-Type: application/json');
if(empty($_SESSION['user_id'])){ echo json_encode(['error'=>'Unauthorized']); exit; }
if($_SERVER['REQUEST_METHOD']!=='POST'){ echo json_encode(['error'=>'Invalid request']); exit; }
$device_id=$_POST['device_id']??null; $action=$_POST['action']??null;
if(!$device_id||!in_array($action,['OPEN','CLOSE'])){ echo json_encode(['error'=>'Invalid input']); exit; }
$s=$pdo->prepare("SELECT id,name FROM devices WHERE id=? AND user_id=? LIMIT 1");
$s->execute([$device_id,$_SESSION['user_id']]); $device=$s->fetch();
if(!$device){ echo json_encode(['error'=>'Unauthorized device']); exit; }
$pdo->prepare("UPDATE devices SET valve_status=?,command=?,updated_at=NOW() WHERE id=?")->execute([$action,$action,$device_id]);
$pdo->prepare("INSERT INTO device_logs (device_id,action,triggered_by) VALUES (?,?,?)")->execute([$device_id,$action,$_SESSION['user_id']]);
$dir=__DIR__.'/../iot_commands'; if(!is_dir($dir)) mkdir($dir,0755,true);
file_put_contents("$dir/valve_$device_id.txt",$action);
echo json_encode(['success'=>true,'device_id'=>$device_id,'status'=>$action]);
