<?php
header("Access-Control-Allow-Origin: *");
$method = $_SERVER['REQUEST_METHOD'];
if( $method !== 'POST' ) {
   $returnJson->status= "fail";
   $returnJson->message= "please requst using POST(curr: $method) method ";
    die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
}

//$json = json_decode(file_get_contents("php://input"),true);
//$uid_enc = isset($json['uid_enc'])? $json['uid_enc'] : null;
$uid_enc = isset($_POST['uid_enc'])? $_POST['uid_enc'] : null;

if(  $uid_enc == null || $uid_enc == '' ) {
   $returnJson->status= "fail";
   $returnJson->message= "please send uid";
    die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
}


require_once './readKey.php';
include './encryption.php';

//$key = base64_decode($encodedKey);

/*
   $key_bytes = array();
   foreach(str_split($key) as $byte)
      $key_bytes[] = sprintf("%08b", ord($byte));
   print_r($key_bytes);

*/

//echo "sgin_enc:".$sign_enc."<br>";
//ad7TTo+apJ9s5EAeqscMtQ==:ZmVkY2JhOTg3NjU0MzIxMA==
//$enc_of_enc= PHP_AES_Cipher::encrypt($key, $iv, $sign_enc, false);
//echo "enc_of_enc:".$enc_of_enc."<br>";
//$sign = PHP_AES_Cipher::decrypt($key, $sign_enc, false);
//echo "sign:".$sign."<br>";



$uid = PHP_AES_Cipher::decrypt($key, $uid_enc, false);
if( $uid == null) {
   $returnJson->status= "fail";
   $returnJson->message= "invalid key";
   die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
}

$db = 'fangcat_users';
require_once './DBConfig.php';
$stmt = $conn->prepare("SELECT `id`, `privilege_id` FROM user_list WHERE `uid` = '$uid'");
try {
   $conn->beginTransaction();
   if( $stmt->execute() ) {
      
      $rowCount = $stmt->rowCount();
      //echo "rowCount:$rowCount<br>";
      if( $rowCount == 0) {
         $stmt = $conn->prepare("INSERT INTO `fangcat_users`.`user_list` (`uid`) VALUES ('$uid')");
         if( $stmt->execute() ) {
            $lastInsertId = $conn->lastInsertId();
            $stmt = $conn->prepare("SELECT `privilege_id` FROM user_list WHERE `id` = '$lastInsertId'");
            if( $stmt->execute() ) {
               $row = $stmt->fetch();
               $returnJson->status= "succ";
               $returnJson->message= "Welcome. First login successful";
               $returnJson->id_enc= PHP_AES_Cipher::encrypt($key, null, $lastInsertId, false);
               $returnJson->privilege_enc= PHP_AES_Cipher::encrypt($key, null, $row[0], false);
            } else {
               $returnJson->status= "fail";
               $returnJson->message= $stmt->errorInfo()[2];
            }
         } else {
            $returnJson->status= "fail";
            $returnJson->message= $stmt->errorInfo()[2];
         }
      } else if( $rowCount == 1) {
         $row = $stmt->fetch();
         $returnJson->status= "succ";
         $returnJson->message= "Good to see you again.";
         $returnJson->id_enc= PHP_AES_Cipher::encrypt($key, null, $row[0], false);
         $returnJson->privilege_enc= PHP_AES_Cipher::encrypt($key, null, $row[1], false);
      } else {
          $returnJson->status= "fail";
          $returnJson->message= "duplicate uid";
      }
   
   } else {
      $returnJson->status= "fail";
      $returnJson->message= $stmt->errorInfo()[2];
   }

   $conn->commit();

} catch( PDOExecption $e ) {
   $conn->rollback();
   $returnJson->status= "fail";
   $returnJson->message= $e->getMessage();
}


echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);

?>