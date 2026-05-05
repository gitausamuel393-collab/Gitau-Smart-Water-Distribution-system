<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$from=$_GET['from']??''; $to=$_GET['to']??'';
$q="SELECT id,units_used,litres,created_at FROM water_usage WHERE user_id=?"; $p=[$uid];
if($from&&$to){ $q.=" AND DATE(created_at) BETWEEN ? AND ?"; $p[]=$from; $p[]=$to; }
$q.=" ORDER BY created_at ASC";
$stmt=$pdo->prepare($q); $stmt->execute($p); $records=$stmt->fetchAll();
$totalUnits=array_sum(array_column($records,'units_used'));
$totalLitres=array_sum(array_column($records,'litres'));
$totalCost=$totalUnits*0.5;
$chartLabels=array_map(fn($r)=>date('d M H:i',strtotime($r['created_at'])),$records);
$chartData=array_map(fn($r)=>(float)$r['units_used'],$records);
$pageTitle='Reports'; $pageSubtitle='Usage analytics';
include '../components/header.php';
?>
<div class="main-content">
  <div class="page-header flex justify-between items-center">
    <div><div class="page-title">Water Usage Report</div><div class="page-subtitle">Detailed analytics and export</div></div>
    <a href="export_pdf.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-danger btn-sm">📄 Export PDF</a>
  </div>
  <!-- FILTER -->
  <div class="card mb-24">
    <form method="GET" class="flex gap-12 items-center flex-wrap">
      <div><label class="form-label">From</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>" style="width:160px"></div>
      <div><label class="form-label">To</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>" style="width:160px"></div>
      <div style="margin-top:22px;display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
        <a href="report.php" class="btn btn-ghost btn-sm">Reset</a>
      </div>
    </form>
  </div>
  <!-- STATS -->
  <div class="grid grid-3 mb-24">
    <div class="stat-card blue"><div class="stat-icon blue">📊</div><div class="stat-value"><?= number_format($totalUnits,3) ?></div><div class="stat-label">Total Units Used</div></div>
    <div class="stat-card green"><div class="stat-icon green">💧</div><div class="stat-value"><?= number_format($totalLitres,2) ?> L</div><div class="stat-label">Total Litres</div></div>
    <div class="stat-card amber"><div class="stat-icon amber">💸</div><div class="stat-value">KES <?= number_format($totalCost,2) ?></div><div class="stat-label">Estimated Cost</div></div>
  </div>
  <!-- CHART -->
  <div class="card mb-24">
    <div class="card-header"><div class="card-title">Usage Over Time</div></div>
    <div class="chart-container"><canvas id="reportChart"></canvas></div>
  </div>
  <!-- TABLE -->
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Units Used</th><th>Litres</th><th>Recorded At</th></tr></thead>
      <tbody>
        <?php if($records): foreach($records as $i=>$r): ?>
        <tr>
          <td class="text-muted"><?= $i+1 ?></td>
          <td style="font-weight:600;color:var(--blue-400)"><?= number_format($r['units_used'],3) ?></td>
          <td><?= number_format($r['litres'],2) ?> L</td>
          <td class="text-muted"><?= date('d M Y H:i:s',strtotime($r['created_at'])) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4" class="text-center text-muted" style="padding:40px">No records found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('reportChart').getContext('2d'),{
  type:'bar',
  data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{label:'Units Used',data:<?= json_encode($chartData) ?>,backgroundColor:'rgba(59,130,246,0.5)',borderColor:'#3b82f6',borderWidth:1,borderRadius:4}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#4b5563',font:{size:10},maxTicksLimit:12}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#4b5563'},beginAtZero:true}}}
});
</script>
<?php include '../components/footer.php'; ?>
