<?php
//header("Access-Control-Allow-Origin: *");

/*
로그인 요청 
클라 iv1 생성 저장 전송
서버 tid iv2 생성 저장 전송
클라 tid 복호화 > iv2 저장 tid, uid암호화 전송
서버 tid 복호화 > uid복호화 > iv3생성 저장
*/

$method = $_SERVER['REQUEST_METHOD'];
if( $method !== 'POST' ) {
   dyingMessage("fail","please requst using POST(curr: $method) method");
}

const OT_ID_KEY = 'ot_id';
const EE_UID_KEY = 'ee_uid';


$json = json_decode(file_get_contents("php://input"),true);

$otId = isset($json[OT_ID_KEY])? $json[OT_ID_KEY] : null; // one-time-id
$eeUid = isset($json[EE_UID_KEY])? $json[EE_UID_KEY] : null; // encoded encrtypted uid

if( $otId == null
   || $eeUid == null || $eeUid == '' ) {
      dyingMessage("fail","please send certificate factor");
}

const TABLE_LOGIN_TRANSACTION = 'login_transaction';
const TABLE_USER_LIST = 'user_list';

require_once './dbConfig.php';
$db = new DB_Connection('fangcat_users');

$row = $db->selectRow($otId, TABLE_LOGIN_TRANSACTION, "`iv`, `status`, `start`");
if( $row == null || $row['status'] != 'WAIT' )
   dyingMessage("fail","this login session is quit");

if( time() - strtotime($row['start']) > 40 ) {
   $db->updateRow($otId, TABLE_LOGIN_TRANSACTION, "`status` = 'TOUT'");
   dyingMessage("fail","time out");
}

require_once './readKey.php';
require_once './encryption.php';

$keyStore = new KeyStore();
$cipher = new AES_Cipher();
$iv = $row['iv'];
$uid = $cipher->decrypt($keyStore->getKey(),$iv, base64_decode($eeUid));


/*
require_once './dbConfig.php';
$db = new DB_Connection('fangcat_users');
$otId = 12;
$uid = 'fafaefaafcx';
require_once './readKey.php';
require_once './encryption.php';
$keyStore = new KeyStore();
$cipher = new AES_Cipher();
*/

if( $uid == null) {
   $db->updateRow($otId, TABLE_LOGIN_TRANSACTION, "`status` = 'FAIL'");
   dyingMessage("fail","invalid key");
}

$db->updateRow($otId, TABLE_LOGIN_TRANSACTION, "`status` = 'SUCC'");

$user = $db->searchRows('uid', $uid, TABLE_USER_LIST, '`id`, `privilege_id`');

//var_dump($user);

$count = count($user);




if( $count > 1) {
   dyingMessage("fail", "duplicate user");
}






$newIv = $cipher->genSecureIv();

if( $count == 0 ) {
   $id = $db->insertBlobAndRow($newIv, $uid,  TABLE_USER_LIST, 'iv', 'uid');
   $privilege_id = 0;
   $returnJson->message= "Welcome. First login successful";
} else {
   $id = $user[0]['id'];
   $privilege_id = $user[0]['privilege_id'];
   $db->updateBlob($id, $newIv, TABLE_USER_LIST, 'iv');
   $returnJson->message= "Good to see you again.";
}

$returnJson->status= "succ";
$returnJson->ee_id= base64_encode($cipher->encrypt($keyStore->getKey(), $iv, $id));
$returnJson->ee_priv_id= base64_encode($cipher->encrypt($keyStore->getKey(), $iv, $privilege_id));
$returnJson->new_iv= base64_encode($newIv);

echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);

function dyingMessage($status, $message) {
   $dyingJson->status= $status;
   $dyingJson->message= $message;
   die(json_encode($dyingJson,JSON_UNESCAPED_UNICODE));
}

?>