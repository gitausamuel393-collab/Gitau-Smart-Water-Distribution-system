<?php
require '../db.php';
$data=json_decode(file_get_contents('php://input'),true);
file_put_contents(__DIR__.'/../mpesa_log.txt',date('Y-m-d H:i:s').' '.json_encode($data).PHP_EOL,FILE_APPEND);
$cb=$data['Body']['stkCallback']??null;
if(!$cb){ http_response_code(200); exit; }
$resultCode=$cb['ResultCode']; $checkoutID=$cb['CheckoutRequestID'];
if($resultCode==0){
  $items=$cb['CallbackMetadata']['Item'];
  $amount=0; $receipt='';
  foreach($items as $item){
    if($item['Name']==='Amount') $amount=$item['Value'];
    if($item['Name']==='MpesaReceiptNumber') $receipt=$item['Value'];
  }
  $stmt=$pdo->prepare("SELECT * FROM payments WHERE payment_reference=? LIMIT 1");
  $stmt->execute([$checkoutID]); $payment=$stmt->fetch();
  if($payment){
    $pdo->prepare("UPDATE payments SET status='SUCCESS',payment_reference=? WHERE id=?")->execute([$receipt,$payment['id']]);
    $pdo->prepare("UPDATE users SET wallet_balance=wallet_balance+? WHERE id=?")->execute([$amount,$payment['user_id']]);
    $pdo->prepare("INSERT INTO alerts (user_id,message) VALUES (?,?)")->execute([$payment['user_id'],"M-Pesa payment of KES $amount confirmed. Receipt: $receipt"]);
  }
}
http_response_code(200);
