<?php
	session_start();
	//validate the form
	//change values in db
	include('storeData.php');
	updateStatusValue($_SESSION['cart']['itemID']);

	//send data to blockchain
	$itemID =  $_SESSION['cart']['itemID'];
	if(!isset($_POST['signature'])){
		echo('invalid form');
		exit;
	}
	$amount = $_POST['amount'];
	$recipient = $_POST['recipient'];
	//$publickey = $_POST['publickey'];

	$publickey  = '98a4cd7d92583558ce2f2c67a3f8252dcd9ad6057ed44cfbd9b838aec0c0f958c91d0a3855aa48081fa27f3bff83dc1edae13967d5d0cd7410f351d0ace1d56f';


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

      var_dump($message);
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

	var_dump(json_encode($postdata));

	$opts=array('http' =>
		 array(
	        'method'  => 'POST',
	        'header'  => 'Content-type: application/json',
	        'content' => json_encode($postdata)
   		 )
	);
	$context  = stream_context_create($opts);
	$server = '10.132.39.159';
	$port = '5000';
	$url = 'http://'.$server.':'.$port.'/transactions/new';

	// $result = file_get_contents($url, false, $context);
	// var_dump($result);
?>
