<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$stmt=$pdo->prepare("SELECT username,email,created_at,wallet_balance,role,status,last_login FROM users WHERE id=?");
$stmt->execute([$uid]); $u=$stmt->fetch();
$totalUsed=$pdo->prepare("SELECT COALESCE(SUM(units_used),0) FROM water_usage WHERE user_id=?"); $totalUsed->execute([$uid]); $totalUsed=$totalUsed->fetchColumn();
$totalPaid=$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND status='SUCCESS'"); $totalPaid->execute([$uid]); $totalPaid=$totalPaid->fetchColumn();
$pageTitle='Profile'; $pageSubtitle='Account information';
include '../components/header.php';
?>
<div class="main-content" style="max-width:800px">
  <div class="page-header"><div class="page-title">My Profile</div><div class="page-subtitle">Your account details and statistics</div></div>
  <div class="grid grid-2">
    <div class="card">
      <div class="card-header"><div class="card-title">Account Details</div></div>
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
        <div style="width:64px;height:64px;background:var(--blue-600);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-size:1.8em;font-weight:800;color:#fff"><?= strtoupper(substr($u['username'],0,1)) ?></div>
        <div><div style="font-size:1.2em;font-weight:700"><?= htmlspecialchars($u['username']) ?></div><div class="text-muted" style="font-size:0.85em"><?= htmlspecialchars($u['email']) ?></div></div>
      </div>
      <div style="display:grid;gap:12px">
        <?php $rows=[['Member Since',date('d M Y',strtotime($u['created_at']))],['Last Login',$u['last_login']?date('d M Y H:i',strtotime($u['last_login'])):'Never'],['Account Status',ucfirst($u['status'])],['Role',ucfirst($u['role'])]]; foreach($rows as [$label,$val]): ?>
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
          <span class="text-muted" style="font-size:0.85em"><?= $label ?></span>
          <span style="font-size:0.85em;font-weight:500"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <div class="stat-card green mb-16"><div class="stat-icon green">💰</div><div class="stat-value">KES <?= number_format($u['wallet_balance'],2) ?></div><div class="stat-label">Wallet Balance</div><div class="stat-sub"><a href="topup.php" class="text-green">Top up →</a></div></div>
      <div class="stat-card blue mb-16"><div class="stat-icon blue">💧</div><div class="stat-value"><?= number_format($totalUsed,2) ?></div><div class="stat-label">Total Units Used</div></div>
      <div class="stat-card amber"><div class="stat-icon amber">💳</div><div class="stat-value">KES <?= number_format($totalPaid,2) ?></div><div class="stat-label">Total Amount Paid</div></div>
    </div>
  </div>
</div>
<?php include '../components/footer.php'; ?>
