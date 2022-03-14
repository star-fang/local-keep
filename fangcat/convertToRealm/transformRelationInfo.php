<?php

$branchNameKor = "병종";
$branchAttacker = "branchAttacker";
$branchDefender = "branchDefender";
$relationValue = "relationValue";


$stmt = $conn->prepare("DESCRIBE `$relationT`");
$stmt->execute();
$relationFinfo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT * FROM `$relationT`";
$stmt = $conn->prepare($query);
if($stmt->execute() ) {
    $dataset = array();
    while($row = $stmt->fetch()) $dataset[] = $row;
    $jsonAry = array();
    foreach($dataset as $data) {
        foreach($relationFinfo as $info) {
            if( $info != $branchNameKor )
            $jsonAry[] = array(
                $branchAttacker=>$data[$branchNameKor],
                $branchDefender=>$info,
                $relationValue=>$data[$info]);

        }
    }

    $returnJson->data=$jsonAry;
  echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);


} else {
    $returnJson->status="fail";
  $returnJson->message=$query;
  echo json_encode($returnJson,JSON_UNESCAPED_UNICODE);
}



  
 
?> 
