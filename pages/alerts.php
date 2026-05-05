<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$stmt=$pdo->prepare("SELECT id,message,is_read,created_at FROM alerts WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$uid]); $alerts=$stmt->fetchAll();
$pdo->prepare("UPDATE alerts SET is_read=1 WHERE user_id=?")->execute([$uid]);
$unread=count(array_filter($alerts,fn($a)=>!$a['is_read']));
$pageTitle='Alerts'; $pageSubtitle='System notifications';
include '../components/header.php';
?>
<div class="main-content">
  <div class="page-header">
    <div class="page-title">Alerts <span style="font-size:0.6em;color:var(--text-muted)"><?= count($alerts) ?> total</span></div>
    <div class="page-subtitle">Your system notifications and warnings</div>
  </div>
  <?php if(!$alerts): ?>
    <div class="card text-center" style="padding:60px"><div style="font-size:3em;margin-bottom:16px">🔔</div><div style="color:var(--text-muted)">No alerts yet. You're all good!</div></div>
  <?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
      <?php foreach($alerts as $a): ?>
      <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:16px;<?= !$a['is_read']?'background:rgba(59,130,246,0.04)':'' ?>">
        <div style="width:8px;height:8px;border-radius:50%;background:<?= !$a['is_read']?'var(--blue-500)':'var(--text-muted)' ?>;flex-shrink:0"></div>
        <div style="flex:1">
          <div style="font-size:0.9em;color:var(--text-primary);font-weight:<?= !$a['is_read']?'600':'400' ?>"><?= htmlspecialchars($a['message']) ?></div>
          <div style="font-size:0.76em;color:var(--text-muted);margin-top:4px"><?= date('d M Y, H:i',strtotime($a['created_at'])) ?></div>
        </div>
        <?php if(!$a['is_read']): ?><span class="badge badge-blue">New</span><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include '../components/footer.php'; ?>
