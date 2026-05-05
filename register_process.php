<?php
session_start(); require 'db.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: register.php'); exit; }
$username=trim($_POST['username']??''); $email=trim($_POST['email']??'');
$password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
if(!$username||!$email||!$password||!$confirm){ $_SESSION['error']='All fields required.'; header('Location: register.php'); exit; }
if($password!==$confirm){ $_SESSION['error']='Passwords do not match.'; header('Location: register.php'); exit; }
if(strlen($password)<6){ $_SESSION['error']='Password must be at least 6 characters.'; header('Location: register.php'); exit; }
$stmt=$pdo->prepare('SELECT id FROM users WHERE username=? OR email=? LIMIT 1');
$stmt->execute([$username,$email]);
if($stmt->fetch()){ $_SESSION['error']='Username or email already exists.'; header('Location: register.php'); exit; }
$hash=password_hash($password,PASSWORD_DEFAULT);
$stmt=$pdo->prepare('INSERT INTO users (username,email,password,created_at) VALUES (?,?,?,NOW())');
if($stmt->execute([$username,$email,$hash])){ $_SESSION['success']='Account created! Please login.'; header('Location: login.php'); exit; }
$_SESSION['error']='Registration failed. Try again.'; header('Location: register.php'); exit;
