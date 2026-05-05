<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])) die('Unauthorized');
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=meru_payments.csv');
$out=fopen('php://output','w');
fputcsv($out,['ID','Amount (KES)','Method','Reference','Status','Date']);
$stmt=$pdo->prepare("SELECT id,amount,payment_method,payment_reference,status,created_at FROM payments WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
while($r=$stmt->fetch()) fputcsv($out,[$r['id'],$r['amount'],$r['payment_method'],$r['payment_reference']??'-',$r['status'],date('d M Y H:i',strtotime($r['created_at']))]);
fclose($out);
