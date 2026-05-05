<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??'');
  if(!$name){ $_SESSION['error']='Please enter a valve name.'; header('Location: add_valve.php'); exit; }
  $pdo->prepare("INSERT INTO devices (user_id,name) VALUES (?,?)")->execute([$_SESSION['user_id'],$name]);
  $_SESSION['success']="Valve '$name' added successfully."; header('Location: dashboard.php'); exit;
}
$pageTitle='Add Valve'; $pageSubtitle='Register a new valve';
include '../components/header.php';
?>
<div class="main-content" style="max-width:560px">
  <div class="page-header">
    <div class="page-title">Add New Valve</div>
    <div class="page-subtitle">Register a new IoT valve to your account</div>
  </div>
  <?php if(!empty($_SESSION['error'])): ?><div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>
  <div class="card">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Valve Name</label>
        <input type="text" name="name" class="form-control" placeholder="e.g., Kitchen Valve, Main Supply" required>
      </div>
      <div class="flex gap-12">
        <button type="submit" class="btn btn-primary">Add Valve</button>
        <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php include '../components/footer.php'; ?>
