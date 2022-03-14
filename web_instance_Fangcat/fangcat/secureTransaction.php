<?php
require_once './dyingMessage.php';
$method = $_SERVER['REQUEST_METHOD'];
if( $method !== 'POST' ) {
    dyingMessage("fail", "please requst using POST(curr: $method) method");
}

const ID_KEY = 'id';
const EE_UID_KEY = 'ee_uid';
const TRANSACTION_KEY = 'transaction';
/*
0: checkTable
1: downloadJson
2: convertTable
3: changeData
*/

$json = json_decode(file_get_contents("php://input"),true);
$id = isset($json[ID_KEY])? $json[ID_KEY] : null; // id
$eeUid = isset($json[EE_UID_KEY])? $json[EE_UID_KEY] : null; // encoded encrtypted uid
$transactionCode = isset($json[TRANSACTION_KEY])? $json[TRANSACTION_KEY] : null;

if( $id == null
   || $eeUid == null || $eeUid == ''
   || $transactionCode == null ) {
    dyingMessage("fail", " please send transaction factor");
}

require_once './dbConfig.php';
$db = new DB_Connection('fangcat_users');

const TABLE_USER_LIST = 'user_list';

$row = $db->selectRow($id, TABLE_USER_LIST, "`uid`, `privilege_id`, `iv`");

if( $row == null )
   dyingMessage("fail","this transaction session is quit");

require_once './readKey.php';
require_once './encryption.php';
   
$keyStore = new KeyStore();
$cipher = new AES_Cipher();
$iv = $row['iv'];
if( $cipher->checkIv( $iv ) ) {
    $uid = $cipher->decrypt($keyStore->getKey(),$iv, base64_decode($eeUid));
} else {
    $uid = null;
}

if( $uid == null || $uid !== $row['uid'] ) {
    dyingMessage("fail_dec","invalid key or iv");
}

const PRIVILEGE_LIST = 'privilege_list';

$privilege = $db->selectRow($row['privilege_id'], PRIVILEGE_LIST, "*");

//unset($db);

const DB_FANGCAT = "fangcat";
const LIST_TABLE_KEY = 'table_list';
const NAME_TABLE_KEY = 'table';
const LAST_MODIFIED_KEY = 'last_modified';
switch($transactionCode) {
    case 0:
        $authorized = $privilege['checkTable'] == 1;
        if( !$authorized ) break;
        require_once './transaction/checkTableList.php';
        $tableListJson = isset($json[LIST_TABLE_KEY])? $json[LIST_TABLE_KEY] : null;
        $transaction = new CheckTableList( $tableListJson, DB_FANGCAT );
    break;
    case 1:
        $authorized = $privilege['downloadJson'] == 1;
        if( !$authorized ) break;
        require_once './transaction/downloadJson.php';
        $tableName = isset($json[NAME_TABLE_KEY])? $json[NAME_TABLE_KEY] : null; // table name
        $lastModified = isset($json[LAST_MODIFIED_KEY])? $json[LAST_MODIFIED_KEY] : 0;
        if( $tableName == null || $tableName == '') {
            dyingMessage("fail", "please send download json factor");
        }
        $transaction = new DownloadJson( $tableName, $lastModified );
        //header('Transfer-Encoding: chunked');
    break;
    case 2:
        $authorized = $privilege['convertTable'] == 1;
        if( !$authorized ) break;
    break;
    case 3:
        $authorized = $privilege['changeData'] == 1;
        if( !$authorized ) break;
    break;
    default:
    $authorized = false;
}

if( !$authorized || !isset($transaction) || $transaction == null)
  dyingMessage('fail','un-authorized account');
  $newIv = $cipher->genSecureIv();
  $returnJson = $transaction->execute(new DB_Connection(DB_FANGCAT));
  $returnJson->status = "succ";
  $returnJson->newIv = base64_encode($newIv);
  $db->updateBlob($id, $newIv, TABLE_USER_LIST, 'iv');
  
  $jsonOutput = json_encode($returnJson);
  header('Content-Type: application/octet-stream');
  header("Content-Length: ".(strlen($jsonOutput) + 3));
  echo $jsonOutput;


?>