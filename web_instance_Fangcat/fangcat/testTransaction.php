<?php
require_once './dyingMessage.php';
/*
0: checkTable
1: downloadJson
2: convertTable
3: changeData
*/
$transactionCode = 1;
require_once './dbConfig.php';
require_once './readKey.php';
require_once './encryption.php';
   
$keyStore = new KeyStore();
$cipher = new AES_Cipher();
$db = new DB_Connection('fangcat_users');

const PRIVILEGE_LIST = 'privilege_list';

$privilege = $db->selectRow('0', PRIVILEGE_LIST, "*");

const DB_FANGCAT = "fangcat";
const LIST_TABLE_KEY = 'table_list';
const NAME_TABLE_KEY = 'table';
const LAST_MODIFIED_KEY = 'last_modified';
switch($transactionCode) {
    case 0:
        $authorized = $privilege['checkTable'] != 0;
        if( !$authorized ) break;
        require_once './transaction/checkTableList.php';
        
        $tableListJson = isset($json[LIST_TABLE_KEY])? $json[LIST_TABLE_KEY] : null;
        $transaction = new CheckTableList( $tableListJson, DB_FANGCAT );
    break;
    case 1:
        $authorized = $privilege['downloadJson'] != 0;
        if( !$authorized ) break;
        require_once './transaction/downloadJson.php';
        $tableName = 'units';
        $lastModified = 1;
        if( $tableName == null || $tableName == '' || $lastModified == null) {
            dyingMessage("fail", "please send download json factor");
        }
        $transaction = new DownloadJson( $tableName, $lastModified );
        //header("Content-disposition: attachment; filename=$tableName.json");
        //header('Content-type: application/json');
    break;
    case 2:
        $authorized = $privilege['convertTable'] != 0;
        if( !$authorized ) break;
    break;
    case3:
        $authorized = $privilege['changeData'] != 0;
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
  $returnJson->newIv = $newIv;
  //$db->updateBlob($id, $newIv, TABLE_USER_LIST, 'iv');

  //header('Content-Type: application/json');
  //ob_start();
  echo json_encode($returnJson);
  //, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
  //$output = ob_get_clean();
  //ob_end_clean();


?>