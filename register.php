<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — Meru Smart Water</title>
<link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">💧</div>
      <h1>Create Account</h1>
      <p>Join Meru Smart Water System</p>
    </div>
    <?php if(!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger">⚠ <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="POST" action="register_process.php">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Create a password" required>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
    </form>
    <div class="auth-footer">Already have an account? <a href="login.php">Sign in</a></div>
  </div>
</div>
</body></html>
