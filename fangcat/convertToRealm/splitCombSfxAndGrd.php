<?php

$magicCombCol = "magicCombination";
$magicCombGrdCol = "magicCombGRD";
$magicCombSfxCols = "magicCombSFXes";

$selectStr = "`$magicCombCol`, RIGHT(`".$magicCombSfxCols.":0`,1) AS `$magicCombGrdCol`";
for( $i = 0; $i < 4; $i++)
$selectStr.= ", LEFT(`".$magicCombSfxCols.":$i`,1) AS `".$magicCombSfxCols.":$i`";
$query = "SELECT $selectStr FROM `$magicCombT` WHERE 1";
$stmt = $conn->prepare($query);

if($stmt->execute() ) {
$dataset = array();
while($row = $stmt->fetch()) $dataset[] = $row;

$jsonAry = array();
foreach( $dataset as $data) {
  
  $eachAry = array();
  $eachAry[$magicCombCol] = $data[$magicCombCol];
  $eachAry[$magicCombGrdCol] = $data[$magicCombGrdCol];
  $eachAry[$magicCombSfxCols] = array();
  for( $i = 0; $i < 4; $i++)
  $eachAry[$magicCombSfxCols][] = $data["$magicCombSfxCols:$i"];
  //array_push($eachAry[$magicCombSfxCols], $data["$magicCombSfxCols:$i"]);
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
