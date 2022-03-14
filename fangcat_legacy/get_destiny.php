<?php


require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);

$s1 = $obj->{'key0'};
$s2 = $obj->{'key1'};


$stmt = $conn->prepare("DESCRIBE `$destinyT`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);


if ($s1 == null) {

    $query = "SELECT `desName` FROM `$destinyT` WHERE 1";
    //echo $query;
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dataset = array();
    echo "[";
    while ($row = $stmt->fetch()) {
      
        echo "{\"key\":\"".$row[0]."\"},";
        
    }
    echo "]";




    
} else {

    $s1 = str_replace(' ','',$s1);
    $query = "SELECT * FROM `$destinyT` WHERE (REPLACE(`desName`, ' ', '') LIKE '%$s1%')";
    //echo $query;
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dataset = array();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }


    if (!empty($dataset)) {

        $condition1 = $dataset[0]["desJoinEffect:1"];
        list($num, $sp, $spval) = explode(":", $condition1);
        if($num <= $s2) {
     

            echo "{\"" . $dataset[0]["desName"] . "\":{";
            foreach ($finfo as $fval) {
               if ($fval != "desName" && $dataset[0][$fval]) {
                   if( (strpos($fval, 'desJoinEffect') !== false)  ) {
                      list($num2, $sp2, $spval2) = explode(":", $dataset[0][$fval]);
                      if($num2 == "퇴각" || $num2 == "인접") {
                        echo "\"$fval\":\"" . $num2." 시 발동: ". $sp2. " ". $spval2 . "\",";
                      } else {
                      echo "\"$fval\":\"" . $num2."명 이상 출진: ". $sp2. " ". $spval2 . "\",";
                      }

                   } else {
                        echo "\"$fval\":\"" . $dataset[0][$fval] . "\",";
                   }
              }
             }
            echo "}}";

        } else {
            die("no");
        }

        
    } else {



        $query = "SELECT `heroDestiny` FROM `$heroesT` WHERE `heroName` = '$s1' OR `heroName2` = '$s1'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $dataset = array();
        while ($row = $stmt->fetch()) {
        $dataset[] = $row;
        }

        if (!empty($dataset)) {

            echo "[";
            foreach($dataset as $data) {
              
                if($data[0] != "")
                echo "{\"key\":\"".$data[0]."\"},";
                
            }
            echo "]";

        } else {
            die("no");
        }



        

    }
}
?> 
