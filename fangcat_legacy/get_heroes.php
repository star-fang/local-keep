<?php
 
require_once 'DBConfig.php'; 

$json = $_POST['json'];
$obj = json_decode($json);

$heros = array();
$num = $obj->{'key0'};

for( $i = 0; $i < $num*2; $i++) {
    $keyname = "key".($i+1);
    $heros[$i] = $obj->$keyname;
}

 echo "[";
for( $i = 0; $i < $num; $i++) {
    $hero = $heros[2*$i];
    if( $hero != null ) {
  
        
    
    $line = $heros[2*$i+1];
    $str = $line? "and `heroBranch` = '$line'": "";
    
    
      $query="SELECT * FROM `$heroesT` WHERE `heroName` = '$hero' $str";
      
$stmt = $conn->prepare($query);
$stmt->execute();


$dataset = array();

 while($row = $stmt->fetch())
   {
      $dataset[] = $row;
   }

    
    
    
   // if( !empty($dataset) ) {



        foreach($dataset as $data_row) {
            $strength = $data_row['heroStats:무력'];
            $intelligence = $data_row['heroStats:지력'];
            $leadership = $data_row['heroStats:통솔'];
            $agility = $data_row['heroStats:민첩'];
            $luck = $data_row['heroStats:행운'];
            $specAry = array($data_row['heroSpecs:30']
                            ,$data_row['heroSpecs:50']
                            ,$data_row['heroSpecs:70']
                            ,$data_row['heroSpecs:90']
                            ,$data_row['heroSpecs:태수']
                            ,$data_row['heroSpecs:군주']);
            $specValueAry = array($data_row['heroSpecValues:30']
                            ,$data_row['heroSpecValues:50']
                            ,$data_row['heroSpecValues:70']
                            ,$data_row['heroSpecValues:90'] );

            echo "{
                      \"이름\":\"$data_row[heroName]\",
                      \"계열\":\"$data_row[heroBranch]\",
                      \"계보\":\"$data_row[heroLineage]\",
                      \"COST\":\"$data_row[heroCost]\",
                      \"스탯\":\"무$strength 지$intelligence 통$leadership 민$agility 행$luck\",
                      \"Lv30\":\"$specAry[0] $specValueAry[0]\",
                      \"Lv50\":\"$specAry[1] $specValueAry[1]\",
                      \"Lv70\":\"$specAry[2] $specValueAry[2]\",
                      \"Lv90\":\"$specAry[3] $specValueAry[3]\",
                      \"태수\":\"$specAry[4]\",
                      \"군주\":\"$specAry[5]\",
                      \"인연\":\"$data_row[heroDestiny]\"},";
		
	} // end for
    }


	
   // }
}
	echo "]";



  
 
?> 
