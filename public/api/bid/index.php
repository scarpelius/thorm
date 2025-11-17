<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$amount = (float)($_POST['amount'] ?? 0);

$currentPrice = $_SESSION['current_price'] ?? 10;

$response['current_price'] = $currentPrice;
//$response['session'] = $_SESSION;

http_response_code(200); // or 200
$response['amount']     = $amount;

if ($amount <= $currentPrice) {
    $response['ok'] = false;
    $response['error'] = ['code'=>'BID_TOO_LOW','min'=>10,'message'=>'Bid too low, current bid is ' . $currentPrice];
    $response['message' ]   = 'Bid rejected';
// Invalid logic here, this should be in a push service
//} else if ($amount <= $currentPrice) {
//    $response['ok'] = false;
//    $response['error'] = ['code'=>'OUTBID','current'=>$currentPrice,'message'=>'You were outbid'];
//    $response['message' ]   = 'Outbid';
} else {
    $id = ($_SESSION['bidId'] ?? 0) + 1;
    $_SESSION['bidId'] = $id;
    $response['ok']         = true;
    $response['bidId']      = $id;
    $response['message' ]   = 'Bid accepted';
}

if(!isset($_SESSION['current_price']) || $_SESSION['current_price'] < $amount) {
    $_SESSION['current_price'] = $amount;
}

echo json_encode($response);
