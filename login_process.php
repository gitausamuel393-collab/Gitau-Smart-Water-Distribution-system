<?php
session_start(); require 'db.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: login.php'); exit; }
$ue=trim($_POST['username_or_email']??''); $pw=$_POST['password']??'';
if(!$ue||!$pw){ $_SESSION['error']='Please fill in both fields.'; header('Location: login.php'); exit; }
$stmt=$pdo->prepare('SELECT id,username,email,password,role,status,created_at FROM users WHERE username=:u OR email=:e LIMIT 1');
$stmt->execute([':u'=>$ue,':e'=>$ue]); $user=$stmt->fetch();
if(!$user||!password_verify($pw,$user['password'])){ $_SESSION['error']='Invalid credentials.'; header('Location: login.php'); exit; }
if($user['status']==='suspended'){ $_SESSION['error']='Your account has been suspended.'; header('Location: login.php'); exit; }
$pdo->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);
session_regenerate_id(true);
$_SESSION['user_id']=$user['id']; $_SESSION['username']=$user['username'];
$_SESSION['email']=$user['email']; $_SESSION['role']=$user['role'];
$_SESSION['created_at']=$user['created_at'];
header('Location: pages/dashboard.php'); exit;
