<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('class/Paypal.php');
require('config/secret.php');

$paypal = new Paypal($CLIENT_ID, $CLIENT_SECRET);
$authentification = $paypal->generateTokenAuth();

var_dump($authentification);

$order = $paypal->createOrder(50);

var_dump($order);

$orderDetail = $paypal->showOrderDetail($order->id);

