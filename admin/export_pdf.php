<?php
session_start(); require '../db.php'; require '../vendor/autoload.php';
use Dompdf\Dompdf; use Dompdf\Options;
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin') die('Unauthorized');
$payments=$pdo->query("SELECT p.*,u.username FROM payments p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC")->fetchAll();
$total=array_sum(array_column($payments,'amount'));
$rows=''; foreach($payments as $i=>$p) $rows.='<tr><td>'.($i+1).'</td><td>'.htmlspecialchars($p['username']).'</td><td>KES '.number_format($p['amount'],2).'</td><td>'.$p['payment_method'].'</td><td>'.$p['status'].'</td><td>'.date('d M Y H:i',strtotime($p['created_at'])).'</td></tr>';
$html='<style>body{font-family:Arial;font-size:11px}h1{color:#1e3a8a}table{width:100%;border-collapse:collapse}th{background:#1e3a8a;color:white;padding:8px;text-align:left}td{padding:7px;border-bottom:1px solid #e5e7eb}</style>';
$html.='<h1>Meru SWD — Payments Report</h1><p>Generated: '.date('d M Y H:i').' | Total Revenue: KES '.number_format($total,2).'</p>';
$html.='<table><tr><th>#</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>'.$rows.'</table>';
$options=new Options(); $options->set('defaultFont','Arial');
$pdf=new Dompdf($options); $pdf->loadHtml($html); $pdf->setPaper('A4','landscape'); $pdf->render();
$pdf->stream("meru_admin_report.pdf",["Attachment"=>true]); exit;
