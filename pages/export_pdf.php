<?php
session_start(); require '../db.php'; require '../vendor/autoload.php';
use Dompdf\Dompdf; use Dompdf\Options;
if(empty($_SESSION['user_id'])) die('Unauthorized');
$uid=$_SESSION['user_id']; $from=$_GET['from']??null; $to=$_GET['to']??null;
$q="SELECT units_used,litres,created_at FROM water_usage WHERE user_id=:uid";
$params=['uid'=>$uid];
if($from&&$to){ $q.=" AND DATE(created_at) BETWEEN :from AND :to"; $params['from']=$from; $params['to']=$to; }
$q.=" ORDER BY created_at ASC";
$stmt=$pdo->prepare($q); $stmt->execute($params); $records=$stmt->fetchAll();
$totalUnits=array_sum(array_column($records,'units_used'));
$totalLitres=array_sum(array_column($records,'litres'));
$totalCost=$totalUnits*0.5;
$uname=$pdo->prepare("SELECT username FROM users WHERE id=?"); $uname->execute([$uid]); $uname=$uname->fetchColumn();
$rows='';
foreach($records as $i=>$r){
  $rows.='<tr><td>'.($i+1).'</td><td>'.number_format($r['units_used'],3).'</td><td>'.number_format($r['litres'],2).'</td><td>'.date('d M Y H:i',strtotime($r['created_at'])).'</td></tr>';
}
$html='<style>body{font-family:Arial;font-size:12px;color:#1a2236}h1{color:#2563eb;font-size:18px}h2{font-size:14px;color:#374151;border-bottom:1px solid #e5e7eb;padding-bottom:6px}table{width:100%;border-collapse:collapse;margin-top:10px}th{background:#1e3a8a;color:white;padding:8px;text-align:left}td{padding:8px;border-bottom:1px solid #e5e7eb}.stats{display:flex;gap:20px;margin:16px 0}.stat{background:#f0f9ff;border:1px solid #bae6fd;padding:10px 16px;border-radius:6px;flex:1}</style>';
$html.='<h1>💧 Meru Smart Water — Usage Report</h1><p>Account: <strong>'.htmlspecialchars($uname).'</strong> | Generated: '.date('d M Y H:i').'</p>';
if($from&&$to) $html.='<p>Period: '.date('d M Y',strtotime($from)).' to '.date('d M Y',strtotime($to)).'</p>';
$html.='<h2>Summary</h2><table><tr><th>Total Units</th><th>Total Litres</th><th>Estimated Cost</th></tr><tr><td>'.number_format($totalUnits,3).'</td><td>'.number_format($totalLitres,2).' L</td><td>KES '.number_format($totalCost,2).'</td></tr></table>';
$html.='<h2 style="margin-top:20px">Detailed Records</h2><table><tr><th>#</th><th>Units Used</th><th>Litres</th><th>Date</th></tr>'.$rows.'</table>';
$options=new Options(); $options->set('defaultFont','Arial');
$pdf=new Dompdf($options); $pdf->loadHtml($html); $pdf->setPaper('A4','portrait'); $pdf->render();
$pdf->stream("meru_usage_report.pdf",["Attachment"=>true]); exit;
