<?php

$magicSfxPlusGrd = "magicSuffixPlusGrade";
$magicSfxName= "magicSuffixName";
$magicSfxGrd = "magicSuffixGRD";
$magicSfxStats = "magicSuffixStats";

$stmt = $conn->prepare("DESCRIBE `$magicSfxStatT`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT * FROM `$magicSfxStatT` WHERE 1";
$stmt = $conn->prepare($query);

if($stmt->execute() ) {
$dataset = $stmt->fetchAll();

$jsonAry = array();
foreach( $dataset as $data) {
  $eachAry = array();
  $eachAry[$magicSfxName] = mb_substr($data[$magicSfxPlusGrd],0,1, "utf-8");
  $eachAry[$magicSfxGrd] = mb_substr($data[$magicSfxPlusGrd],-1,1, "utf-8");
  $eachAry[$magicSfxStats] = array();
  foreach( $finfo as $info ) {
      if( $info != $magicSfxPlusGrd)
      $eachAry[$magicSfxStats][] = $data[$info];
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

  
 
?> 
