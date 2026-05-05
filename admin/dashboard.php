<?php
session_start(); require '../db.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'){ header('Location: ../login.php'); exit; }
$totalUsers=$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPayments=$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='SUCCESS'")->fetchColumn();
$totalWater=$pdo->query("SELECT COALESCE(SUM(units_used),0) FROM water_usage")->fetchColumn();
$activeUsers=$pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$users=$pdo->query("SELECT id,username,email,wallet_balance,status,role,created_at FROM users ORDER BY id DESC")->fetchAll();
$valves=$pdo->query("SELECT d.id,d.name,d.valve_status,u.username FROM devices d JOIN users u ON d.user_id=u.id")->fetchAll();
$chartData=$pdo->query("SELECT DATE(created_at) as dt,COALESCE(SUM(amount),0) as total FROM payments WHERE status='SUCCESS' GROUP BY DATE(created_at) ORDER BY dt DESC LIMIT 7")->fetchAll();
$chartLabels=array_map(fn($r)=>date('d M',strtotime($r['dt'])),array_reverse($chartData));
$chartVals=array_map(fn($r)=>(float)$r['total'],array_reverse($chartData));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — Meru Smart Water</title>
<link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="app-layout">
<aside class="sidebar">
  <div class="sidebar-brand"><div class="brand-icon">🛠</div><div class="brand-name">Admin Panel</div><div class="brand-sub">Meru SWD System</div></div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Admin</div>
    <a href="dashboard.php" class="nav-item active"><span class="nav-icon">⊞</span> Dashboard</a>
    <a href="users.php" class="nav-item"><span class="nav-icon">👥</span> Users</a>
    <a href="export_csv.php" class="nav-item"><span class="nav-icon">📊</span> Export CSV</a>
    <a href="export_pdf.php" class="nav-item"><span class="nav-icon">📄</span> Export PDF</a>
    <div class="nav-section-label">System</div>
    <a href="../pages/dashboard.php" class="nav-item"><span class="nav-icon">←</span> User View</a>
    <a href="../logout.php" class="nav-item"><span class="nav-icon">🚪</span> Logout</a>
  </nav>
  <div class="sidebar-footer"><div class="sidebar-user"><div class="s-avatar"><?= strtoupper(substr($_SESSION['username'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></div><div class="user-role">Administrator</div></div></div></div>
</aside>
<div class="main-wrapper">
<header class="topbar">
  <div class="topbar-left"><div class="topbar-title">Admin Dashboard</div><div class="topbar-subtitle">System Overview</div></div>
  <div class="topbar-actions"><a href="../logout.php" class="topbar-btn">🚪</a></div>
</header>
<div class="main-content">
  <div class="grid grid-4 mb-24">
    <div class="stat-card blue"><div class="stat-icon blue">👥</div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Total Users</div><div class="stat-sub"><?= $activeUsers ?> active</div></div>
    <div class="stat-card green"><div class="stat-icon green">💰</div><div class="stat-value">KES <?= number_format($totalPayments,0) ?></div><div class="stat-label">Total Revenue</div></div>
    <div class="stat-card amber"><div class="stat-icon amber">💧</div><div class="stat-value"><?= number_format($totalWater,1) ?></div><div class="stat-label">Units Consumed</div></div>
    <div class="stat-card red"><div class="stat-icon red">🚰</div><div class="stat-value"><?= count($valves) ?></div><div class="stat-label">Total Valves</div></div>
  </div>
  <div class="grid grid-2 mb-24">
    <div class="card">
      <div class="card-header"><div class="card-title">📈 Revenue (Last 7 Days)</div></div>
      <div class="chart-container"><canvas id="adminChart"></canvas></div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">🚰 Valve Status</div></div>
      <?php foreach($valves as $v): ?>
      <div class="valve-item">
        <div><div class="valve-name"><?= htmlspecialchars($v['name']) ?></div><div class="valve-meta"><?= htmlspecialchars($v['username']) ?></div></div>
        <span class="badge <?= $v['valve_status']==='OPEN'?'badge-green':'badge-red' ?>"><?= $v['valve_status'] ?></span>
      </div>
      <?php endforeach; if(!$valves) echo '<p class="text-muted">No valves registered</p>'; ?>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <div class="card-title">👥 Users</div>
      <div class="flex gap-8">
        <a href="export_pdf.php" class="btn btn-danger btn-sm">📄 PDF</a>
        <a href="export_csv.php" class="btn btn-ghost btn-sm">📊 CSV</a>
      </div>
    </div>
    <div class="table-wrap" style="border:none">
      <table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Balance</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($users as $u): ?>
          <tr>
            <td class="text-muted"><?= $u['id'] ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($u['username']) ?></td>
            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
            <td style="color:var(--green-500)">KES <?= number_format($u['wallet_balance'],2) ?></td>
            <td><span class="badge <?= $u['status']==='active'?'badge-green':'badge-red' ?>"><?= $u['status'] ?></span></td>
            <td class="text-muted"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
            <td><a href="toggle_user.php?id=<?= $u['id'] ?>" class="btn btn-ghost btn-sm"><?= $u['status']==='active'?'Suspend':'Activate' ?></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<footer class="footer"><span>© <?= date('Y') ?> Meru SWD Admin</span><span>v2.0</span></footer>
</div></div>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('adminChart').getContext('2d'),{
  type:'bar',data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{label:'Revenue (KES)',data:<?= json_encode($chartVals) ?>,backgroundColor:'rgba(59,130,246,0.5)',borderColor:'#3b82f6',borderWidth:1,borderRadius:4}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#4b5563'}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#4b5563'},beginAtZero:true}}}
});
</script>
