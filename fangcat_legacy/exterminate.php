<?php
header('Content-Type: text; charset=utf-8');
 
require_once 'DBConfig.php';  


$json = $_POST['json'];
$obj = json_decode($json);




$line = $obj->{'key0'};        // 계열 병종


if( !$line ) {
    die( "fail" );
}

$str = "(A LIKE '%$line%')";



$query="SELECT * FROM `퇴치임무` WHERE $str";

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
 echo "{\"B\":\"$data[B]\",\"C\":\"$data[C]\",\"D\":\"$data[D]\"},";
   }

    echo "]";
} else {
echo 'fail';
}

?> 