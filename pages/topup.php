<?php
session_start(); require '../db.php';
if(empty($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
$uid=$_SESSION['user_id'];
$success=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $amount=floatval($_POST['amount']??0); $method=trim($_POST['method']??''); $ref=trim($_POST['mpesa_code']??'');
  if($amount<=0){ $error='Please enter a valid amount.'; }
  else {
    try {
      $pdo->beginTransaction();
      $pdo->prepare("INSERT INTO payments (user_id,amount,payment_method,payment_reference,status,created_at) VALUES (?,?,?,?,'SUCCESS',NOW())")->execute([$uid,$amount,$method,$ref?:null]);
      $pdo->prepare("UPDATE users SET wallet_balance=wallet_balance+? WHERE id=?")->execute([$amount,$uid]);
      $pdo->prepare("INSERT INTO alerts (user_id,message) VALUES (?,?)")->execute([$uid,"Wallet topped up by KES ".number_format($amount,2)]);
      $pdo->commit();
      $success='Top-up successful! KES '.number_format($amount,2).' added to your wallet.';
    } catch(Exception $e){ $pdo->rollBack(); $error='Error: '.$e->getMessage(); }
  }
}
$bal=$pdo->prepare("SELECT wallet_balance FROM users WHERE id=?"); $bal->execute([$uid]); $balance=$bal->fetchColumn();
$pageTitle='Top-Up Wallet'; $pageSubtitle='Add funds to your account';
include '../components/header.php';
?>
<div class="main-content">
  <div class="page-header">
    <div class="page-title">Top-Up Wallet</div>
    <div class="page-subtitle">Add funds to your water distribution account</div>
  </div>
  <div class="grid grid-2" style="max-width:900px">
    <div class="card">
      <?php if($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if($error): ?><div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div class="card-header"><div class="card-title">Payment Details</div></div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Amount (KES)</label>
          <input type="number" name="amount" class="form-control" placeholder="Enter amount" min="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select name="method" class="form-control" required>
            <option value="MPesa">📱 M-Pesa</option>
            <option value="Cash">💵 Cash</option>
            <option value="Bank">🏦 Bank Transfer</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">M-Pesa Code (Optional)</label>
          <input type="text" name="mpesa_code" class="form-control" placeholder="e.g. QFT34H87Y">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Submit Top-Up</button>
      </form>
    </div>
    <div>
      <div class="stat-card green" style="margin-bottom:20px">
        <div class="stat-icon green">💰</div>
        <div class="stat-value">KES <?= number_format($balance,2) ?></div>
        <div class="stat-label">Current Wallet Balance</div>
        <div class="stat-sub"><?= number_format($balance*2,2) ?> water units available</div>
      </div>
      <div class="card">
        <div class="card-title" style="margin-bottom:16px">💡 How It Works</div>
        <p style="font-size:0.88em;color:var(--text-secondary);line-height:1.8">
          • 1 KES = 2 water units<br>
          • Units are deducted as you use water<br>
          • Top up anytime to continue service<br>
          • System auto-closes valve when balance runs out
        </p>
      </div>
    </div>
  </div>
</div>
<?php include '../components/footer.php'; ?>
