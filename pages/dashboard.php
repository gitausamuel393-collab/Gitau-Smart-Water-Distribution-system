<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$user=$pdo->prepare('SELECT id,username,wallet_balance FROM users WHERE id=? LIMIT 1');
$user->execute([$uid]); $user=$user->fetch();
$devices=$pdo->prepare("SELECT id,name,valve_status FROM devices WHERE user_id=? ORDER BY name ASC");
$devices->execute([$uid]); $devices=$devices->fetchAll();
$pageTitle='Dashboard'; $pageSubtitle='System Overview';
include '../components/header.php';
?>
<div class="main-content">

  <!-- LIVE BANNER -->
  <div class="live-banner" id="liveBanner">
    <div class="live-dot" id="liveDot"></div>
    <div class="live-stat">
      <div class="live-stat-value" id="flowRateVal">0.0</div>
      <div class="live-stat-label">L/MIN FLOW</div>
    </div>
    <div class="live-divider"></div>
    <div class="live-stat">
      <div class="live-stat-value" id="availWaterVal">—</div>
      <div class="live-stat-label">UNITS AVAILABLE</div>
    </div>
    <div class="live-divider"></div>
    <div class="live-stat">
      <div class="live-stat-value" id="valveCountVal">—</div>
      <div class="live-stat-label">VALVES OPEN</div>
    </div>
    <div class="live-divider"></div>
    <div class="live-stat">
      <div class="live-stat-value" id="systemStatus">Connecting...</div>
      <div class="live-stat-label">SYSTEM STATUS</div>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="grid grid-4 mb-24">
    <div class="stat-card blue">
      <div class="stat-icon blue">💧</div>
      <div class="stat-value" id="availWater">0</div>
      <div class="stat-label">Available Water (Units)</div>
      <div class="stat-sub">1 KES = 2 units</div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green">💰</div>
      <div class="stat-value">KES <?= number_format($user['wallet_balance'],2) ?></div>
      <div class="stat-label">Wallet Balance</div>
      <div class="stat-sub"><a href="topup.php" class="text-green">Top up →</a></div>
    </div>
    <div class="stat-card amber">
      <div class="stat-icon amber">📅</div>
      <div class="stat-value" id="dailyUsage">0</div>
      <div class="stat-label">Today's Usage (Units)</div>
      <div class="stat-sub" id="weeklyUsage">Weekly: 0</div>
    </div>
    <div class="stat-card red">
      <div class="stat-icon red">📆</div>
      <div class="stat-value" id="monthlyUsage">0</div>
      <div class="stat-label">Monthly Usage (Units)</div>
      <div class="stat-sub" id="monthlyAvg">Avg/day: 0</div>
    </div>
  </div>

  <div class="grid grid-2">
    <!-- VALVE CONTROL -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">🚰 Valve Control</div>
        <a href="add_valve.php" class="btn btn-primary btn-sm">+ Add Valve</a>
      </div>
      <?php if($devices): ?>
        <?php foreach($devices as $d): ?>
        <div class="valve-item" id="valve-<?= $d['id'] ?>">
          <div>
            <div class="valve-name"><?= htmlspecialchars($d['name']) ?></div>
            <div class="valve-meta">
              <span class="badge <?= $d['valve_status']==='OPEN'?'badge-green':'badge-red' ?>" id="vstatus-<?= $d['id'] ?>">
                <?= $d['valve_status']==='OPEN' ? '● OPEN' : '● CLOSED' ?>
              </span>
            </div>
          </div>
          <div class="valve-actions">
            <button class="btn btn-success btn-sm" onclick="controlValve(<?= $d['id'] ?>,'OPEN')" id="vopen-<?= $d['id'] ?>" <?= $d['valve_status']==='OPEN'?'disabled':'' ?>>Open</button>
            <button class="btn btn-danger btn-sm" onclick="controlValve(<?= $d['id'] ?>,'CLOSE')" id="vclose-<?= $d['id'] ?>" <?= $d['valve_status']==='CLOSE'?'disabled':'' ?>>Close</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-info">No valves found. <a href="add_valve.php">Add one →</a></div>
      <?php endif; ?>
    </div>

    <!-- CHART -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">📈 Water Usage</div>
        <select class="form-control" id="timeline" style="width:120px;padding:6px 10px;font-size:0.82em">
          <option value="hourly">Hourly</option>
          <option value="daily" selected>Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly">Monthly</option>
        </select>
      </div>
      <div class="chart-container">
        <canvas id="usageChart"></canvas>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chart;

function initChart(labels,data){
  const ctx=document.getElementById('usageChart').getContext('2d');
  if(chart){ chart.data.labels=labels; chart.data.datasets[0].data=data; chart.update(); return; }
  chart=new Chart(ctx,{
    type:'line',
    data:{labels,datasets:[{label:'Units Used',data,borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.1)',fill:true,tension:0.4,pointBackgroundColor:'#3b82f6',pointRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#4b5563',font:{size:11}}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#4b5563',font:{size:11}},beginAtZero:true}}}
  });
}

function controlValve(id,action){
  fetch('update_valve.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`device_id=${id}&action=${action}`})
  .then(r=>r.json()).then(()=>refresh());
}

function refresh(){
  const tl=document.getElementById('timeline').value;
  fetch(`get_usage.php?timeline=${tl}`)
  .then(r=>r.json()).then(d=>{
    if(d.error) return;
    initChart(d.chartData.labels,d.chartData.usage);
    document.getElementById('availWater').textContent=parseFloat(d.availableWater||0).toFixed(2);
    document.getElementById('availWaterVal').textContent=parseFloat(d.availableWater||0).toFixed(1);
    document.getElementById('dailyUsage').textContent=parseFloat(d.dailyUsage||0).toFixed(2);
    document.getElementById('weeklyUsage').textContent='Weekly: '+parseFloat(d.weeklyUsage||0).toFixed(2);
    document.getElementById('monthlyUsage').textContent=parseFloat(d.monthlyUsage||0).toFixed(2);
    document.getElementById('monthlyAvg').textContent='Avg/day: '+parseFloat(d.monthlyAvg||0).toFixed(2);
    document.getElementById('flowRateVal').textContent=parseFloat(d.flowRate||0).toFixed(1);

    const valves=d.valves||[];
    const openCount=valves.filter(v=>v.valve_status==='OPEN').length;
    document.getElementById('valveCountVal').textContent=openCount+'/'+valves.length;
    document.getElementById('systemStatus').textContent=openCount>0?'Running':'Idle';
    document.getElementById('liveDot').className='live-dot'+(openCount>0?'':' offline');

    valves.forEach(v=>{
      const sb=document.getElementById('vstatus-'+v.id);
      const ob=document.getElementById('vopen-'+v.id);
      const cb=document.getElementById('vclose-'+v.id);
      if(sb){ sb.textContent=v.valve_status==='OPEN'?'● OPEN':'● CLOSED'; sb.className='badge '+(v.valve_status==='OPEN'?'badge-green':'badge-red'); }
      if(ob) ob.disabled=v.valve_status==='OPEN';
      if(cb) cb.disabled=v.valve_status==='CLOSE';
    });

    // low water warning
    if(parseFloat(d.availableWater||0)<10){
      document.getElementById('liveBanner').style.borderLeftColor='#ef4444';
    }
  });
}

document.getElementById('timeline').addEventListener('change',refresh);
setInterval(refresh,5000);
refresh();
</script>

<?php include '../components/footer.php'; ?>