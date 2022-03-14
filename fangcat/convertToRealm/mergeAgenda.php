<?php

$agendaStartKor = "시작";
$agendaMapKor = "맵";
$competKor = "경쟁";
$ahhihKor = "섬멸";
// $competAgendaT = "경쟁일정";
// $annihAgendaT = "섬멸일정";
$agendaStart = "agendaStart";
$agendaDivision = "agendaDivision";
$agendaMap = "agendaMap";

$jsonAry = array();


$query = "SELECT * FROM `$competAgendaT`";
$stmt = $conn->prepare($query);
if($stmt->execute() ) {
$dataset = array();
while($row = $stmt->fetch()) $dataset[] = $row;
foreach( $dataset as $data) {
  $eachAry = array();
  $eachAry[$agendaStart] = $data[$agendaStartKor];
  $eachAry[$agendaMap] = $data[$agendaMapKor];
  $eachAry[$agendaDivision] = $competKor;
  array_push($jsonAry, $eachAry);
}

} else {
    die("fail: ".$query);
}


$query = "SELECT * FROM `$annihAgendaT`";
$stmt = $conn->prepare($query);
if($stmt->execute() ) {
$dataset = array();
while($row = $stmt->fetch()) $dataset[] = $row;
foreach( $dataset as $data) {

  $eachAry = array();
  $eachAry[$agendaStart] = $data[$agendaStartKor];
  $eachAry[$agendaMap] = $data[$agendaMapKor];
  $eachAry[$agendaDivision] = $ahhihKor;
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
