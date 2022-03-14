<?php

$branchNameKor = "병종";
$branchName = "branchName";
$terrainSyns = "terrainSyns:";
$movingCost = "movingCost:";

$tvTerrainName = "tvTerrainName";
$tvValue = "tvValue";

$stmt = $conn->prepare("DESCRIBE `$terrainT`");
$stmt->execute();
$terrainFinfo = $stmt->fetchAll(PDO::FETCH_COLUMN);
$selectColumn = "a.`$branchNameKor` as `$branchName`";
$columnArrayJSON = array();
$columnArrayJSON[] = $branchName;
foreach( $terrainFinfo as $info ) {
  if( $info != "$branchNameKor") {
  $selectColumn.= ", a.`".$info."` as `$terrainSyns".$info."`";
  $columnArrayJSON[] = "$terrainSyns"."$info";
  }
}

$stmt = $conn->prepare("DESCRIBE `$movingT`");
$stmt->execute();
$movingFinfo = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach( $movingFinfo as $info ) {
  if( $info != "$branchNameKor") {
  $selectColumn.= ", b.`".$info."` as `$movingCost".$info."`";
  $columnArrayJSON[]= "$movingCost"."$info";
  }
}

//SELECT a.`병종` as `branchName`, a.`산지` as `terrainSyn:산지` 
//FROM `병종 지형상성` a INNER JOIN `병종 이동력 소모` b ON a.`병종` = b.`병종`
$query = "SELECT $selectColumn FROM `$terrainT` a INNER JOIN `$movingT` b ON a.`$branchNameKor` = b.`$branchNameKor`";
$stmt = $conn->prepare($query);

if($stmt->execute() ) {
$dataset = array();
while($row = $stmt->fetch()) $dataset[] = $row;

$jsonAry = array();
foreach( $dataset as $data) {
  
  $eachAry = array();
  foreach($columnArrayJSON as $info) {

    if(strpos($info,":")!==false) {
      $realInfo = explode(":",$info)[0];
      $hiddenInfo = explode(":",$info)[1];
      $eachAry[$realInfo] = isset($eachAry[$realInfo])? $eachAry[$realInfo] : array();
      array_push($eachAry[$realInfo],array($tvTerrainName=>$hiddenInfo, $tvValue=>$data[$info] ));
      //echo $info.":".$data[$info]."</br>";
    } else {
      $eachAry[$info] = $data[$info];
    }
    
  }
  array_push($jsonAry, $eachAry);
}

$returnJson->data=$jsonAry;
  echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);


} else {
    $returnJson->status="fail";
  $returnJson->message=$query;
  echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);
}
  
 
?> 
