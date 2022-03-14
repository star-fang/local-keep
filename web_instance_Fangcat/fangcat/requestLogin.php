<?php
//header("Access-Control-Allow-Origin: *");

$method = $_SERVER['REQUEST_METHOD'];
if( $method !== 'POST' ) {
   $returnJson->status= "fail";
   $returnJson->message= "fail to open login section";
   die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
}

const IV_KEY = 'iv';

$json = json_decode(file_get_contents("php://input"),true);
$encodedIv = isset($json[IV_KEY])? $json[IV_KEY] : null;
if( $encodedIv == null || $encodedIv == '' ) {
    $encodedIv = isset($_POST[IV_KEY])? $_POST[IV_KEY] : null;
    if( $encodedIv == null || $encodedIv == '' ) {
      $returnJson->status= "fail";
      $returnJson->message= "please send initial factor";
      die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
    }
 }
 

$iv = base64_decode($encodedIv);
//= $_GET[IV_KEY];
//

require_once './encryption.php';

$cipher = new AES_Cipher();

if( !$cipher->checkIv($iv) ) {
   $returnJson->status= "fail";
   $returnJson->message= "invalid initial factor";
   die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
}


require_once './dbConfig.php';
require_once './readKey.php';
$keyStore = new KeyStore();
$db = new DB_Connection('fangcat_users');

$newIv = $cipher->genSecureIv();
$lastInsertId = $db->insertBlob($newIv,'login_transaction','iv');

//$checkIv = $db->selectBlob($lastInsertId, 'login_transaction','iv');
//echo "<br>insertedIv: ".base64_encode($checkIv)."<br>";

$returnJson->status= "succ";
$returnJson->message= "begin login transaction";
$returnJson->ot_id_enc= base64_encode($cipher->encrypt($keyStore->getKey(), $iv, $lastInsertId));
$returnJson->ot_iv= base64_encode($newIv);
echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);

?>