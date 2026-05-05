<?php
session_start(); require '../db.php';
header('Content-Type: application/json');
if(empty($_SESSION['user_id'])){ echo json_encode(['error'=>'Unauthorized']); exit; }
$uid=$_SESSION['user_id']; $timeline=$_GET['timeline']??'daily'; $rate=2;

// Valves
$sv=$pdo->prepare("SELECT id,name,valve_status FROM devices WHERE user_id=?");
$sv->execute([$uid]); $valves=$sv->fetchAll(PDO::FETCH_ASSOC);

// Flow rate (latest sensor reading)
$sf=$pdo->prepare("SELECT flow_rate FROM water_usage WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$sf->execute([$uid]); $flowRate=(float)($sf->fetchColumn()??0);

// Total paid → available units
$sp=$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND status='SUCCESS'");
$sp->execute([$uid]); $totalPaid=(float)$sp->fetchColumn();
$totalUnitsBought=$totalPaid*$rate;

// Total used
$su=$pdo->prepare("SELECT COALESCE(SUM(units_used),0) FROM water_usage WHERE user_id=?");
$su->execute([$uid]); $totalUsed=(float)$su->fetchColumn();
$availableUnits=max(0,$totalUnitsBought-$totalUsed);

// Chart + usage calculations
$chartLabels=[]; $chartData=[];
$dailyUsage=0; $weeklyUsage=0; $monthlyUsage=0; $monthlyAvg=0;

if($timeline==='hourly'){
  $s=$pdo->prepare("SELECT HOUR(created_at) as hr,COALESCE(SUM(units_used),0) as total FROM water_usage WHERE user_id=? AND DATE(created_at)=CURDATE() GROUP BY hr");
  $s->execute([$uid]); $rows=$s->fetchAll(PDO::FETCH_ASSOC);
  $map=array_column($rows,'total','hr');
  for($h=0;$h<=23;$h++){ $chartLabels[]=$h.':00'; $v=(float)($map[$h]??0); $chartData[]=$v; $dailyUsage+=$v; }
  $weeklyUsage=$dailyUsage; $monthlyUsage=$dailyUsage;
} elseif($timeline==='daily'){
  $s=$pdo->prepare("SELECT DATE(created_at) as dt,COALESCE(SUM(units_used),0) as total FROM water_usage WHERE user_id=? AND DATE(created_at)>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY dt");
  $s->execute([$uid]); $rows=$s->fetchAll(PDO::FETCH_ASSOC);
  $map=array_column($rows,'total','dt');
  for($i=6;$i>=0;$i--){ $d=date('Y-m-d',strtotime("-$i days")); $chartLabels[]=date('d M',strtotime($d)); $v=(float)($map[$d]??0); $chartData[]=$v; if($i==0)$dailyUsage=$v; $weeklyUsage+=$v; }
} elseif($timeline==='weekly'){
  for($i=3;$i>=0;$i--){
    $start=date('Y-m-d',strtotime('-'.(($i+1)*7).' days')); $end=date('Y-m-d',strtotime('-'.($i*7).' days'));
    $s=$pdo->prepare("SELECT COALESCE(SUM(units_used),0) FROM water_usage WHERE user_id=? AND DATE(created_at) BETWEEN ? AND ?");
    $s->execute([$uid,$start,$end]); $v=(float)$s->fetchColumn();
    $chartLabels[]='Week '.(4-$i); $chartData[]=$v; $weeklyUsage+=$v;
  }
  $dailyUsage=$weeklyUsage/7;
} elseif($timeline==='monthly'){
  for($i=11;$i>=0;$i--){
    $month=date('Y-m',strtotime("-$i months"));
    $s=$pdo->prepare("SELECT COALESCE(SUM(units_used),0) FROM water_usage WHERE user_id=? AND DATE_FORMAT(created_at,'%Y-%m')=?");
    $s->execute([$uid,$month]); $v=(float)$s->fetchColumn();
    $chartLabels[]=date('M Y',strtotime($month)); $chartData[]=$v; $monthlyUsage+=$v;
  }
  $monthlyAvg=$monthlyUsage/30; $weeklyUsage=$monthlyUsage/4; $dailyUsage=$weeklyUsage/7;
}

echo json_encode(['flowRate'=>$flowRate,'availableWater'=>$availableUnits,'dailyUsage'=>$dailyUsage,'weeklyUsage'=>$weeklyUsage,'monthlyUsage'=>$monthlyUsage,'monthlyAvg'=>$monthlyAvg,'chartData'=>['labels'=>$chartLabels,'usage'=>$chartData],'valves'=>$valves]);
