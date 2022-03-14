<?php
 
require_once 'DBConfig.php';  

$json = $_POST['json'];
$obj = json_decode($json);

$magicSFX = array();

$str = "(1=1) and ";

for( $i = 0; $i < 4; $i++) {
  
    $keyname = "key".$i;
    $magicSFX[$i] = $obj->$keyname;
    
    
    if( $magicSFX[$i] ) {
    $str.= "(CONCAT(`magicCombSFXs:0`,`magicCombSFXs:1`,`magicCombSFXs:2`,`magicCombSFXs:3`) LIKE '%$magicSFX[$i]%') and ";
    }
}

$spec = array();

$involveSpec = false;
for( $i = 0; $i < 6; $i++) {
    $keyname = "key".($i+4);
    $sepc[$i] = $obj->$keyname;
    
    if( $sepc[$i] ) {
        $involveSpec = true;
    $str.= "(REPLACE(`magicCombination`,' ','') LIKE '%$sepc[$i]%') or ";
    }   
}

$str.= $involveSpec ? "(1=0)" : "(1=1)";

$query="SELECT `magicCombination`, RIGHT(`magicCombSFXs:0`,1), CONCAT(LEFT(`magicCombSFXs:0`,1),LEFT(`magicCombSFXs:1`,1),LEFT(`magicCombSFXs:2`,1),LEFT(`magicCombSFXs:3`,1)) FROM `$magicCombT` WHERE $str"; 

$stmt = $conn->prepare($query);
$stmt->execute();

$dataset = array();

 while($row = $stmt->fetch())
   {
      $dataset[] = $row;
   }

if(!empty($dataset))
   {
echo "[";
foreach ($dataset as $data)

{
 echo "{\"A\":\"$data[0]\",\"B\":\"$data[1]\",\"C\":\"$data[2]\"},";
   }

    echo "]";
} else {
echo 'fail';
}

 


?>