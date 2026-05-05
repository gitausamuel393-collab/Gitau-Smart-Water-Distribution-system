<?php
require '../db.php';
header('Content-Type: application/json');
$API_KEY=getenv('IOT_API_KEY')?:'meru_iot_2024';
if(($_GET['key']??'')!==$API_KEY){ http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
$uid=$_GET['user_id']??null; $flow=floatval($_GET['flow_rate']??0);
$duration=floatval($_GET['duration']??0); $device_id=$_GET['device_id']??null;
if(!$uid||!$flow||!$duration){ echo json_encode(['error'=>'Missing parameters']); exit; }
$litres=($flow*$duration)/60;
$units=$litres/2;
$stmt=$pdo->prepare("INSERT INTO water_usage (user_id,device_id,litres,units_used,flow_rate,created_at) VALUES (?,?,?,?,?,NOW())");
$stmt->execute([$uid,$device_id,$litres,$units,$flow]);
// Auto-close valve if balance runs out
$paid=$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND status='SUCCESS'");
$paid->execute([$uid]); $totalPaid=(float)$paid->fetchColumn();
$used=$pdo->prepare("SELECT COALESCE(SUM(units_used),0) FROM water_usage WHERE user_id=?");
$used->execute([$uid]); $totalUsed=(float)$used->fetchColumn();
$available=($totalPaid*2)-$totalUsed;
if($available<=0){
  $pdo->prepare("UPDATE devices SET valve_status='CLOSE',command='CLOSE' WHERE user_id=?")->execute([$uid]);
  $pdo->prepare("INSERT INTO alerts (user_id,message) VALUES (?,?)")->execute([$uid,'⚠️ Water balance depleted. All valves closed automatically.']);
}
echo json_encode(['success'=>true,'litres'=>round($litres,3),'units'=>round($units,3),'available'=>max(0,$available)]);
