<?php if(session_status()===PHP_SESSION_NONE) session_start();
$memberSince='';
if(!empty($_SESSION['created_at'])) $memberSince=date("M j, Y",strtotime($_SESSION['created_at']));
if(!isset($pdo)){ require __DIR__.'/../db.php'; }
$unreadAlerts=0; $userRole='';
if(isset($_SESSION['user_id'])){
    $s=$pdo->prepare("SELECT COUNT(*) FROM alerts WHERE user_id=? AND is_read=0"); $s->execute([$_SESSION['user_id']]); $unreadAlerts=$s->fetchColumn();
    $s=$pdo->prepare("SELECT role FROM users WHERE id=?"); $s->execute([$_SESSION['user_id']]); $userRole=$s->fetchColumn();
}
$currentPage=basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Meru Smart Water</title>
<link rel="stylesheet" href="../assets/styles.css">
</head>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.getElementById('menuToggle');
  const sidebar = document.querySelector('.sidebar');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });
  }
});
</script>
<body>
<div class="app-layout">
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">💧</div>
    <div class="brand-name">Meru SWD</div>
    <div class="brand-sub">Smart Water Distribution</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="nav-item <?= $currentPage=='dashboard.php'?'active':'' ?>">
      <span class="nav-icon">⊞</span> Dashboard
    </a>
    <a href="alerts.php" class="nav-item <?= $currentPage=='alerts.php'?'active':'' ?>">
      <span class="nav-icon">🔔</span> Alerts
      <?php if($unreadAlerts>0): ?><span class="nav-badge"><?= $unreadAlerts ?></span><?php endif; ?>
    </a>
    <div class="nav-section-label">Water</div>
    <a href="usage_history.php" class="nav-item <?= $currentPage=='usage_history.php'?'active':'' ?>">
      <span class="nav-icon">📊</span> Usage History
    </a>
    <a href="report.php" class="nav-item <?= $currentPage=='report.php'?'active':'' ?>">
      <span class="nav-icon">📋</span> Reports
    </a>
    <div class="nav-section-label">Billing</div>
    <a href="topup.php" class="nav-item <?= $currentPage=='topup.php'?'active':'' ?>">
      <span class="nav-icon">💳</span> Top-Up Wallet
      <span class="nav-badge green">+</span>
    </a>
    <a href="payment_history.php" class="nav-item <?= $currentPage=='payment_history.php'?'active':'' ?>">
      <span class="nav-icon">🧾</span> Payments
    </a>
    <div class="nav-section-label">Account</div>
    <a href="profile.php" class="nav-item <?= $currentPage=='profile.php'?'active':'' ?>">
      <span class="nav-icon">👤</span> Profile
    </a>
    <?php if($userRole==='admin'): ?>
    <a href="../admin/dashboard.php" class="nav-item">
      <span class="nav-icon">🛠</span> Admin Panel
    </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="s-avatar"><?= !empty($_SESSION['username']) ? strtoupper(substr($_SESSION['username'],0,1)) : 'U' ?></div>
      <div style="flex:1;min-width:0">
        <div class="user-name"><?= htmlspecialchars($_SESSION['username']??'User') ?></div>
        <div class="user-role"><?= $memberSince ?: 'Member' ?></div>
      </div>
      <a href="../logout.php" class="topbar-btn" title="Logout" style="width:28px;height:28px;font-size:0.9em">🚪</a>
    </div>
  </div>
</aside>
<div class="main-wrapper">
<header class="topbar">
  <!-- MOBILE MENU BUTTON -->
  <button id="menuToggle" class="menu-btn">☰</button>
  <div class="topbar-left">
    <div class="topbar-title"><?= htmlspecialchars($pageTitle??'Dashboard') ?></div>
    <div class="topbar-subtitle"><?= htmlspecialchars($pageSubtitle??'Meru Smart Water Distribution') ?></div>
  </div>
  <div class="topbar-actions">
    <a href="alerts.php" class="topbar-btn">
      🔔<?php if($unreadAlerts>0): ?><span class="notif-dot"></span><?php endif; ?>
    </a>
    <a href="profile.php" class="topbar-btn">👤</a>
    <a href="../logout.php" class="topbar-btn">🚪</a>
  </div>
</header>