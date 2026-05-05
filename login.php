<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Meru Smart Water</title>
<link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">💧</div>
      <h1>Welcome Back</h1>
      <p>Sign in to Meru Smart Water System</p>
    </div>
    <?php if(!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger">⚠ <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✓ <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form action="login_process.php" method="post" autocomplete="off" novalidate>
      <div class="form-group">
        <label class="form-label">Username or Email</label>
        <input type="text" name="username_or_email" class="form-control" placeholder="Enter username or email" required autocomplete="off">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
    </form>
    <div class="auth-footer">
      Don't have an account? <a href="register.php">Create one</a>
    </div>
  </div>
</div>
</body></html>
