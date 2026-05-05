<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$from=$_GET['from']??''; $to=$_GET['to']??'';
$q="SELECT id,litres,units_used,flow_rate,created_at FROM water_usage WHERE user_id=?"; $p=[$uid];
if($from&&$to){ $q.=" AND DATE(created_at) BETWEEN ? AND ?"; $p[]=$from; $p[]=$to; }
$q.=" ORDER BY created_at DESC LIMIT 500";
$stmt=$pdo->prepare($q); $stmt->execute($p); $records=$stmt->fetchAll();
$totalUnits=array_sum(array_column($records,'units_used'));
$totalLitres=array_sum(array_column($records,'litres'));
$pageTitle='Usage History'; $pageSubtitle='Water consumption records';
include '../components/header.php';
?>
<div class="main-content">
  <div class="page-header flex justify-between items-center">
    <div><div class="page-title">Usage History</div><div class="page-subtitle">All water consumption from your devices</div></div>
    <a href="export_pdf.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&type=usage" class="btn btn-danger btn-sm">📄 Export PDF</a>
  </div>
  <!-- SUMMARY STATS -->
  <div class="grid grid-3 mb-24">
    <div class="stat-card blue"><div class="stat-icon blue">📊</div><div class="stat-value"><?= number_format($totalUnits,2) ?></div><div class="stat-label">Total Units Used</div></div>
    <div class="stat-card green"><div class="stat-icon green">💧</div><div class="stat-value"><?= number_format($totalLitres,1) ?></div><div class="stat-label">Total Litres</div></div>
    <div class="stat-card amber"><div class="stat-icon amber">💸</div><div class="stat-value">KES <?= number_format($totalUnits*0.5,2) ?></div><div class="stat-label">Estimated Cost</div></div>
  </div>
  <!-- FILTER -->
  <div class="card mb-24">
    <form method="GET" class="flex gap-12 items-center flex-wrap">
      <div><label class="form-label">From</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>" style="width:160px"></div>
      <div><label class="form-label">To</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>" style="width:160px"></div>
      <div style="margin-top:22px;display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="usage_history.php" class="btn btn-ghost btn-sm">Reset</a>
      </div>
    </form>
  </div>
  <!-- TABLE -->
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Units Used</th><th>Litres</th><th>Flow Rate</th><th>Recorded At</th></tr></thead>
      <tbody>
        <?php if($records): foreach($records as $r): ?>
        <tr>
          <td class="text-muted"><?= $r['id'] ?></td>
          <td style="font-weight:600;color:var(--blue-400)"><?= number_format($r['units_used'],3) ?></td>
          <td><?= number_format($r['litres'],2) ?> L</td>
          <td><?= number_format($r['flow_rate'],2) ?> L/min</td>
          <td class="text-muted"><?= date('d M Y H:i:s',strtotime($r['created_at'])) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center text-muted" style="padding:40px">No usage records found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../components/footer.php'; ?>
