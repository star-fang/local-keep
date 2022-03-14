<?php
 
require_once 'DBConfig.php'; 

$json = $_POST['json'];
$obj = json_decode($json);
$specs = array();
$num = $obj->{'key0'};
for( $i = 0; $i < $num; $i++) {
    $keyname = "key".($i+1);
    $specs[$i] = $obj->$keyname;
}

$oneSpec = "";
foreach($specs as $specEach) {
    $oneSpec .= $specEach;
}

$query = "SELECT * FROM `$specT` WHERE REPLACE(`specName`,' ','') LIKE '%$oneSpec%'";
$stmt = $conn->prepare($query);
$stmt->execute();
$dataset = array();
while ($row = $stmt->fetch()) {
     $dataset[] = $row;
}

if(!empty($dataset)) {
    //die($oneSpec);
    $specs[0] = $oneSpec;
    $num = 1;
}else {
    foreach($specs as $specEach) {
        if( mb_strlen($specEach,'utf-8') == 1 ) {
            die("fail : please enter at least 2 chars");
        }
    }
}


 
echo "[";
    
    
      for($j=0; $j<$num; $j++) {
          
          
          if( $specs[$j] == "연속책략" || $specs[$j] == "회심공격" || $specs[$j] == "강공" ) {
              $str = "REPLACE(`specName`,' ','') = '$specs[$j]' ORDER BY `specName` ASC";
          } else {
                $str = "REPLACE(`specName`,' ','') LIKE '%$specs[$j]%' ORDER BY `specName` ASC";
          }
          
          
    
    $query="SELECT * FROM `$specT` WHERE $str";

$stmt = $conn->prepare($query);
$stmt->execute();


$dataset1 = array();

 while($row = $stmt->fetch())
   {
      $dataset1[] = $row;
   }


    
    
    
    
    if( empty($dataset1) ) {


        $stmt = $conn->prepare("DESCRIBE `$branchT`");
        $stmt->execute();
        $fInfo = $stmt->fetchAll(PDO::FETCH_COLUMN);

         $query="SELECT * FROM `$branchT` WHERE `branchName` LIKE '%$specs[$j]%'";
         $stmt = $conn->prepare($query);
         $stmt->execute();
         $dataset2 = array();
         while($row = $stmt->fetch()) $dataset2[] = $row;
   
         if(empty($dataset2)) die("fail");
           
           $jsonAry = array();
         foreach($dataset2 as $data_row) {

                echo "{";
                foreach($fInfo as $info) {
                    echo "\"$info\":\"$data_row[$info]\",";
                }
                echo "},";
          
           }
          
          
    } else {
       
    
           foreach($dataset1 as $data_row) {
   
            $ef = $data_row[specName];
            echo "{   \"A\":\"$ef\",
                      \"B\":\"$data_row[specDescription]\",},";
           }
      }
      }
      
      echo "]";
      ?>                