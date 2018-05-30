<?php 



if(isset($_POST['loadItems'])){
    fetchItems(); 
}

if(isset($_GET['updateStatusValue'])){
    updateStatusValue($_GET['requestedItemID']); 
}

if(isset($_POST['getAllUserPurchases'])){
    getAllUserPurchases($_POST['userID']); 
}
function getItem($itemID){
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

        $html .= ' <div class ="row">';
        foreach($itemsList as $item){
            
            $html .= '<div class="col-sm-6 col-md-4 col-lg-3 p-b-50">';
            $html .= '<form action="payment.php" method="get">'; 
            $html .= '<div class="block" id="item1">'; 
            $html .= '<div class="block-img wrap-pic-w of-hidden pos-relative block-labelnew">';
            $html .= '<img src="' . $item['img'] . '" alt="IMG-PRODUCT"class="img-fluid">'; 
            $html .= ' </div>'; 
            $html .= '<p>' . $item['itemName'] . '</p>';    
            $html .= '<p>' . $item['amt'] . '</p'; 
            $html .= '<input type="hidden" id="item' . $item['itemID'] .'_price" value ="'. $item['amt'].'">'; 
            $html .= '<input type="hidden" id="item' . $item['itemID'] .'_name" value ="'. $item['name'].'">'; 
            $html .= '<button  class="bg-success" type="submit" id="buyButton" onclick="add(' . $item['itemID'] .')">Buy</button>'; 
            $html .= '</div>'; 
            $html .= '</form>'; 
            $html .= '</div>'; 
            
        }
        $html .= '</div>';     
        echo($html);                 
}    
function getAllUserPurchases($userID){
    $file_db = new PDO('sqlite:db_test.db');
    $file_db->setAttribute(PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION); 
    $itemsList = array(); 
    $result = $file_db->query("
        SELECT items.*
        FROM items
        INNER JOIN solditems ON items.itemID == soldItems.itemID
        WHERE soldItems.buyerID=$userID
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

    


    $html .= ' <div class ="row">';
    foreach($itemsList as $item){  
            $html .= '<div class="col-sm-6 col-md-4 col-lg-3 p-b-50">';
            $html .= '<form action="payment.php" method="get">'; 
            $html .= '<div class="block" id="item1">'; 
            $html .= '<div class="block-img wrap-pic-w of-hidden pos-relative block-labelnew">';
            $html .= '<img src="' . $item['img'] . '" alt="IMG-PRODUCT"class="img-fluid">'; 
            $html .= ' </div>'; 
            $html .= '<div class="row">';
            $html .= '<div class="col-sm">';
            $html .= '<p>' . $item['itemName'] . '</p>';    
            $html .= '<p>' . $item['amt'] . '</p>'; 
            $html .= '</div>';
            $html .= '<input type="hidden" name="requestedItemID" value="' . $item['itemID'] . '">'; 
            $html .= '<input type="hidden" id="item' . $item['itemID'] . '_price" value ="' . $item['amt'] . '">'; 
            $html .= '<input type="hidden" id="item' . $item['itemID'] .'_name" value ="'. $item['itemName'].'">'; 
            $html .= '<div class="col-sm">';
            if($item['status']=='pending'){
                $html .= '<button type="button" class="float-right btn btn-lg btn-warning" disabled>Pending</button>';
            }elseif($item['status']=='purchased'){
                $html .= '<button type="button" class="float-right btn btn-lg btn-danger" disabled>Pending</button>';
            }
            $html .= '</div>'; 
            $html .= '</div>'; 
            $html .= '</form>'; 
            $html .= '</div>'; 
    }
    $html .= '</div>';     
    echo($html);    
}

function updateStatusValue($requestedItemID){
    $file_db = new PDO('sqlite:db_test.db');
    $file_db->setAttribute(PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION); 

    $file_db->exec(
        "UPDATE items 
        SET status='pending'
        WHERE itemID == $requestedItemID 
    ");
    $file_db->exec(
        "UPDATE soldItems 
        SET status='pending' 
        WHERE itemID == $requestedItemID 
    ");
}

function fetchItems(){
    $file_db = new PDO('sqlite:db_test.db');
    $file_db->setAttribute(PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION); 
        $result = $file_db->query("SELECT * FROM items
                    WHERE status=='available'
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
        printItems($itemsList); 
} 

function printItems($itemsList){
    $html .= ' <div class ="row">';
    foreach($itemsList as $item){  
            $html .= '<div class="col-sm-6 col-md-4 col-lg-3 p-b-50">';
            $html .= '<form action="payment.php" method="get">'; 
            $html .= '<div class="block" id="item1">'; 
            $html .= '<div class="block-img wrap-pic-w of-hidden pos-relative block-labelnew">';
            $html .= '<img src="' . $item['img'] . '" alt="IMG-PRODUCT"class="img-fluid">'; 
            $html .= ' </div>'; 
            $html .= '<p>' . $item['itemName'] . '</p>';    
            $html .= '<p>' . $item['amt'] . '</p>'; 
            $html .= '<input type="hidden" name="requestedItemID" value="' . $item['itemID'] . '">'; 
            $html .= '<input type="hidden" id="item' . $item['itemID'] . '_price" value ="' . $item['amt'] . '">'; 
            $html .= '<input type="hidden" id="item' . $item['itemID'] .'_name" value ="'. $item['itemName'].'">'; 
            $html .= '<button  class="bg-success" type="submit" name="itemName" id="buyButton" onclick="add(' . $item['itemID'] .')">Buy</button>'; 
            $html .= '</div>'; 
            $html .= '</form>'; 
            $html .= '</div>'; 
    }
    $html .= '</div>';     
    echo($html);   
}



?>