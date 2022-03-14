<?php

require_once 'DBConfig.php';


$json = $_POST['json'];
$obj = json_decode($json);

$NameOrSpec = $obj->{'key0'};        // 아이템이름 or 효과

if( !$NameOrSpec ) {die("fail1");}

$stmt = $conn->prepare("DESCRIBE `$itemT`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);


$sql = "SELECT * FROM `$itemCateT`";
$stmt = $conn->prepare($sql);
$stmt->execute();

$NameOrSpecSplit = explode( ' ', $NameOrSpec );

$cate = null;
$smallCate = false;

while ($row = $stmt->fetch()) {
    foreach($NameOrSpecSplit as $split ) {

        if($split == $row[1] ) {
            $cate = $row[1];
            $NameOrSpec = str_replace($split,'',$NameOrSpec);
            break;
        } else if($split == $row[0] ) {
            $cate = $row[0];
            $NameOrSpec = str_replace($split,'',$NameOrSpec);
            $smallCate = true;
            break;
        } 

    }
}





if($cate) {
    $cateWhereStr = ($smallCate)? "(`itemSubCate` = '$cate')" : "(`itemMainCate` = '$cate')";
} else {
    $cateWhereStr = "(1=1)";
}

//SELECT * FROM `보물` LEFT JOIN `보물 분류` ON `보물`.`종류` = `보물 분류`.`소분류` WHERE `대분류` = '무기'
$cateJoinStr = ($cate && !$smallCate)? "LEFT JOIN `$itemCateT` ON `$itemT`.`itemSubCate` = `$itemCateT`.`itemSubCate`" : "";

$NameOrSpec = str_replace(' ','',$NameOrSpec);
$specStr = ($NameOrSpec != "") ? "(REPLACE(`specName`,' ','') LIKE '%$NameOrSpec%')" : "(1=0)";
$querySpec = "SELECT `specName` FROM `$specT` WHERE $specStr";
//die($querySpec);
$stmt = $conn->prepare($querySpec);
$stmt->execute();
$dataset = array();
while ($row = $stmt->fetch()) {
    $dataset[] = $row;
}

if (!empty($dataset)) {
    echo "{";
    $dataSize = count($dataset);
    $count = 0;
    foreach ($dataset as $data) {
        $plusCateKey = ($cate)? "crlf*".$cate : "";
        echo "\"*" . $data[0]. $plusCateKey . "\":";
        $specNoBlank = str_replace(' ', '' , $data[0] );
        //$specStr2 = ($NameOrSpec == "") ? "(REPLACE(`specName`,' ','') LIKE '%$NameOrSpec%')" : "(1=1)";
        $queryItem = "SELECT * FROM `$itemT` $cateJoinStr WHERE $cateWhereStr AND ((REPLACE(`itemSpecs:1`,' ','') = '$specNoBlank') OR (REPLACE(`itemSpecs:2`,' ','') = '$specNoBlank'))";
        $stmt = $conn->prepare($queryItem);
        $stmt->execute();
        $dataset2 = array();
        while ($row = $stmt->fetch()) {
            $dataset2[] = $row;
        }
        echo "[";
        if (!empty($dataset2)) {

            
            foreach ($dataset2 as $data2) {

                echo "{";

                foreach ($finfo as $fval) {
                    echo "\"$fval\":\"" . $data2[$fval] . "\",";
                }
                echo "},";
            }
        }
        echo "]";

        echo ($count < $dataSize - 1 ) ? "," : "";
        $count++;
    }
    echo "}";
} else {

    $nameStr = ($NameOrSpec != "") ? "(REPLACE(`itemName`,' ','')) LIKE '%$NameOrSpec%'" : "(1=1)";
    $queryItem = "SELECT * FROM `$itemT` $cateJoinStr WHERE $cateWhereStr AND $nameStr";
    $stmt = $conn->prepare($queryItem);
    $stmt->execute();
    $dataset2 = array();
    while ($row = $stmt->fetch()) {
        $dataset2[] = $row;
    }
    if (!empty($dataset2)) {
        echo ($NameOrSpec != "") ? "{\"보물 이름 검색\":" : "{\"$cate 검색\":";
        echo "[";
        foreach ($dataset2 as $data2) {
            echo "{";
            foreach ($finfo as $fval) {
                echo "\"$fval\":\"" . $data2[$fval] . "\",";



                if($fval == "itemSpecs:1" || $fval == "itemSpecs:2") {
                    $specNoBlank = str_replace( ' ','',$data2[$fval] );

                    $querySpec = "SELECT `specDescription` FROM `$specT` WHERE (REPLACE(`specName`,' ','') = '$specNoBlank')";
                  //die( $querySpec );
                    $stmt = $conn->prepare($querySpec);
                    $stmt->execute();
                    $dataset = array();
                    $row = $stmt->fetch();
                    
                    echo "\"".$fval.":desc\":\"" . $row[0] . "\",";
                    
                }
            }
            echo "},";
        }
        echo "]";
        echo "}";
    } else {
        die("fail : ".$queryItem);
    }
}
?> 


