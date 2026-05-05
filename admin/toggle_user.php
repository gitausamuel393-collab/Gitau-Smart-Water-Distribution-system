<?php
session_start(); require '../db.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin') exit();
$id=intval($_GET['id']??0);
if($id) $pdo->prepare("UPDATE users SET status=IF(status='active','suspended','active') WHERE id=?")->execute([$id]);
header('Location: dashboard.php');
