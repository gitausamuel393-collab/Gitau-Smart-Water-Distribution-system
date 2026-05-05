<?php
session_start(); require '../db.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin') exit();
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=meru_payments_admin.csv');
$out=fopen('php://output','w');
fputcsv($out,['ID','User','Amount','Method','Reference','Status','Date']);
$stmt=$pdo->query("SELECT p.id,u.username,p.amount,p.payment_method,p.payment_reference,p.status,p.created_at FROM payments p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC");
while($r=$stmt->fetch()) fputcsv($out,[$r['id'],$r['username'],$r['amount'],$r['payment_method'],$r['payment_reference']??'-',$r['status'],date('d M Y H:i',strtotime($r['created_at']))]);
fclose($out);
