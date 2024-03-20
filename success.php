<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('class/Paypal.php');
require('config/secret.php');

$paypal = new Paypal($CLIENT_ID, $CLIENT_SECRET);

$capturePayment = $paypal->capturePayment($_GET['token']);

echo "L'opération a été un succes.\n";
echo "détails de la commande : \n";

var_dump($capturePayment);

// Save the capture payment in database.

