<?php
$pref_table = $_GET['pref_t'];
$update_time = isset($_GET['lut'])? $_GET['lut'] : null;
$returnJson;

require_once '../DBConfig.php'; 

if($pref_table == "지형 정보") {
  $returnJson = checkUpdate(array($terrainT,$movingT),$update_time, $conn);
  require 'mergeTerrainInfo.php';
} else if($pref_table == "병종 상성") {
  $returnJson = checkUpdate(array($relationT),$update_time, $conn);
  require 'transformRelationInfo.php';
} else if($pref_table == "보패 조합") {
  $returnJson = checkUpdate(array($magicCombT),$update_time, $conn);
  require 'splitCombSfxAndGrd.php';
} else if($pref_table == "보패 접미사") {
  $returnJson = checkUpdate(array($magicSfxStatT),$update_time, $conn);
  require 'splitSfxAndGrd.php';
} else if($pref_table == "일정") {
  $returnJson = checkUpdate(array($competAgendaT,$annihAgendaT),$update_time, $conn);
  require 'mergeAgenda.php';
} else {

  $returnJson = checkUpdate(array($pref_table),$update_time, $conn);


  $stmt = $conn->prepare("DESCRIBE `$pref_table`");
$stmt->execute();
$fInfo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT * FROM `$pref_table`";
$stmt = $conn->prepare($query);
if($stmt->execute() ) {
$dataset = array();
while($row = $stmt->fetch()) $dataset[] = $row;

$jsonAry = array();
foreach( $dataset as $data) {
  
  $eachAry = array();
  foreach($fInfo as $info) {

    if(strpos($info,":")!==false) {
      $realInfo = explode(":",$info)[0];
      $eachAry[$realInfo] = isset($eachAry[$realInfo]) ? $eachAry[$realInfo] : array();
      array_push($eachAry[$realInfo],$data[$info]);
    } else {
      $eachAry[$info] = $data[$info];
    }
    
  }
  array_push($jsonAry, $eachAry);
}


if($update_time==null) {
  echo json_encode($jsonAry,JSON_UNESCAPED_UNICODE);
} else {
  $returnJson->data=$jsonAry;
  echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);

}





} else {
  $returnJson->status="fail";
  $returnJson->message=$query;
  echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);
}





}



 


function checkUpdate($tableNameArr,$time, $conn) {

  $jsonResult;
  if($time==null) {
    
    return;
  }

  $server_time = 0;

    foreach($tableNameArr as $table_name) {


      $checkUpateSql = "SELECT UPDATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE `TABLE_NAME` = '$table_name'";
      $stmt = $conn->prepare($checkUpateSql);
      if($stmt->execute()) {
        $row = $stmt->fetch();
        $server_time_this = strtotime($row['UPDATE_TIME']);
        $server_time = max($server_time_this, $server_time);
      } else {
        $jsonResult->status="fail";
        $jsonResult->message="fail to read update time";
        die(json_encode($jsonResult,JSON_UNESCAPED_UNICODE));
      }
    }
  
    $local_time = strtotime($time);
        

        $date_format = "Y-m-d H:i:s";
        $local_time_fetch = date($date_format,$local_time);
        $server_time_fetch = date($date_format,$server_time);

        $result = "로컬시간:".$local_time_fetch."<br>서버시간:".$server_time_fetch;

        if($local_time<$server_time) {
          $jsonResult->status = "update";
          $jsonResult->message = "업데이트";
          $jsonResult->time = $server_time_fetch;
          

        } else {
          $jsonResult->status = "latest";
          $jsonResult->message = "DB가 최신 버전 입니다.";
          $jsonResult->time = $server_time_fetch;
          die(json_encode($jsonResult,JSON_UNESCAPED_UNICODE));
        }
  
        return $jsonResult;
}



  
 
?> 
