<?php

require_once 'DBConfig.php';  


$json = $_POST['json'];
$obj = json_decode($json);




$what = $obj->key0;        // 섬멸 or 경쟁
$date = $obj->key1;
$date2 = $obj->key2;
$map = $obj->key3;

if( !$what ) {
    die( "fail" );
}
$table = $what."일정";
$str = (!$map)? "`시작` BETWEEN $date and $date2" : "`맵` = '$map'";
$query="SELECT * FROM `$table` WHERE $str";
//die( $query );
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
 echo "{\"시작\":\"$data[시작]\",\"맵\":\"$data[맵]\"},";
   }

    echo "]";
} else {
echo 'fail';
}

?> 