<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$from=$_GET['from']??''; $to=$_GET['to']??'';
$query="SELECT * FROM payments WHERE user_id=?"; $params=[$uid];
if($from&&$to){ $query.=" AND DATE(created_at) BETWEEN ? AND ?"; $params[]=$from; $params[]=$to; }
$query.=" ORDER BY created_at DESC";
$stmt=$pdo->prepare($query); $stmt->execute($params); $payments=$stmt->fetchAll();
$totalPaid=array_sum(array_column($payments,'amount'));
$pageTitle='Payment History'; $pageSubtitle='All your transactions';
include '../components/header.php';
?>
<div class="main-content">
  <div class="page-header flex justify-between items-center">
    <div><div class="page-title">Payment History</div><div class="page-subtitle">View all your wallet transactions</div></div>
    <div class="flex gap-12">
      <a href="export_pdf.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-danger btn-sm">📄 PDF</a>
      <a href="export_csv.php" class="btn btn-ghost btn-sm">📊 CSV</a>
    </div>
  </div>
  <!-- FILTER -->
  <div class="card mb-24">
    <form method="GET" class="flex gap-12 items-center flex-wrap">
      <div><label class="form-label">From</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>" style="width:160px"></div>
      <div><label class="form-label">To</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>" style="width:160px"></div>
      <div style="margin-top:22px;display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="payment_history.php" class="btn btn-ghost btn-sm">Reset</a>
      </div>
      <div style="margin-left:auto;margin-top:22px">
        <span style="font-size:0.85em;color:var(--text-muted)">Total: </span>
        <span style="font-weight:700;color:var(--green-500)">KES <?= number_format($totalPaid,2) ?></span>
      </div>
    </form>
  </div>
  <!-- TABLE -->
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php if($payments): foreach($payments as $p): ?>
        <tr>
          <td class="text-muted"><?= $p['id'] ?></td>
          <td style="font-weight:700;color:var(--green-500)">KES <?= number_format($p['amount'],2) ?></td>
          <td><?= $p['payment_method']==='MPesa'?'📱 M-Pesa':($p['payment_method']==='Cash'?'💵 Cash':'🏦 Bank') ?></td>
          <td class="text-muted"><?= htmlspecialchars($p['payment_reference']??'-') ?></td>
          <td><span class="badge <?= $p['status']==='SUCCESS'?'badge-green':'badge-amber' ?>"><?= $p['status'] ?></span></td>
          <td class="text-muted"><?= date('d M Y, H:i',strtotime($p['created_at'])) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:40px">No payment records found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../components/footer.php'; ?>
