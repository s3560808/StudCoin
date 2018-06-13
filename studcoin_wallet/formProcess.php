<?php
	session_start();
	//validate the form
	//change values in db
	include('storeData.php');
	include('getUTXO.php');
	include('apicall.php');
	updateStatusValue($_SESSION['cart']['itemID']);

	//send data to blockchain
	$itemID =  $_SESSION['cart']['itemID'];
	if(!isset($_POST['signature'])){
		echo('invalid form');
		exit;
	}
	$amount = $_POST['amount'];
	$recipient = $_POST['recipient'];
	$publickey = $_POST['publickey'];
	$signature = $_POST['signature'];
	$file_db = new PDO('sqlite:db_test.db');
    $file_db->setAttribute(PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION);
    $result = $file_db->query("
        SELECT * FROM items
        WHERE itemID==$itemID
    ");
   	$itemsList = array();
        foreach($result as $row){
            $item = array(
                'itemID' => $row['itemID'],
                'amt' => $row['price'],
                'sellerPublicKey' => $row['seller'],
                'itemName' => $row['itemName'],
                'img' => $row['image'],
                'status' => $row['status']
            );
            array_push($itemsList, $item);
      	}

	//get the item purchased
	$postdata =
			array(
				'nodes' => [],
				'signature' => $signature,
				'message' => array(
									'amount' => (int)$amount,
									'recipient' => $recipient,
									'itemID' => (int)$itemID,
									'publickey' => $publickey
								)
			);

	$opts=array('http' =>
		 array(
	        'method'  => 'POST',
	        'header'  => 'Content-type: application/json',
	        'content' => json_encode($postdata)
   		 )
	);
	$context  = stream_context_create($opts);
	$server = '127.0.0.1';
	$port = '5000';
	$url = 'http://'.$server.':'.$port.'/transactions/new';

	$result = file_get_contents($url, false, $context);
	var_dump($result);
	if($result===FALSE){
		var_dump($http_response_header);
	}
	$_SESSION['utxo'] = getUTXO($_SESSION['publicKey']);
?>
