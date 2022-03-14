<?php

require_once 'DBConfig.php';


$json = $_POST['json'];
$obj = json_decode($json);

$line = $obj->{'key0'};        // 계열 병종
$lineage = $obj->{'key1'};     // 계보

$cost_pivot = $obj->{'key2'};  // 코스트
$cost_more = $obj->{'key3'};   // 이상
$cost_below = $obj->{'key4'};  // 이하
$cost_ex = $obj->{'key5'};     // 초과
$cost_less = $obj->{'key6'};   // 미만

$spec = array();
$spec[0] = $obj->{'key7'};       // 효과
$spec[1] = $obj->{'key8'};
$spec[2] = $obj->{'key9'};
$spec[3] = $obj->{'key10'};
$spec[4] = $obj->{'key11'};
$spec[5] = $obj->{'key12'};


$sort_pivot = $obj->{'key13'};   // 정렬기준
$sort_asc = $obj->{'key14'};     // 오름 내림

//B이름 C계열 D계보 E코스트 F~J스탯 K,M,O,Q30~90효과 L,N,P,R수치 S태수 T군주





//$spec[0] = '화계책략';
//$spec[1] = '특화';

$oneSpec = "";
foreach($spec as $specEach) {
    //echo $specEach.",<br />\n";
    $oneSpec .= $specEach;
}


$query = "SELECT * FROM `$specT` WHERE REPLACE(`specName`,' ','') LIKE '%$oneSpec%'";
//die($query);

$stmt = $conn->prepare($query);
$stmt->execute();
$dataset = array();
while ($row = $stmt->fetch()) {
     $dataset[] = $row;
}

if(!empty($dataset)) {
    $spec[0] = $oneSpec;
    for($i = 1; $i < 6; $i++) {
        $spec[$i] = NULL;
    }
}else {
    foreach($spec as $specEach) {
        if( mb_strlen($specEach,'utf-8') == 1 ) {
            die("fail : please enter at least 2 chars");
        }
    }
}



$wstr = " WHERE (aT.`heroNo` > 0)";
$wstr .= $line ? " and (aT.`heroBranch` LIKE '%$line%')" : "";
$wstr .= $lineage ? " and (aT.`heroLineage` LIKE '%$lineage%')" : "";
$wstr .= $cost_pivot ? " and (aT.`heroCost` = $cost_pivot)" : "";
$wstr .= $cost_more ? " and (aT.`heroCost` >= $cost_more)" : "";
$wstr .= $cost_below ? " and (aT.`heroCost` <= $cost_below)" : "";
$wstr .= $cost_ex ? " and (aT.`heroCost` > $cost_ex)" : "";
$wstr .= $cost_less ? " and (aT.`heroCost` < $cost_less)" : "";
//$wstr .= $sort_pivot ? " ORDER BY $sort_pivot $sort_asc" : "";'



/*
  SELECT * FROM `장수 정보` AS aT
  INNER JOIN `장수 정보` AS bT ON aT.`A` = bT.`A`
  INNER JOIN `장수 정보` AS cT ON aT.`A` = cT.`A`
  WHERE
  (1=1)
  and (REPLACE(CONCAT_WS(aT.`K`,aT.`M`, aT.`O`, aT.`Q`),' ','') LIKE '%전화위복%')
  and (REPLACE(CONCAT_WS(bT.`K`,bT.`M`, bT.`O`, bT.`Q`),' ','') LIKE '%역전%')
  and (REPLACE(CONCAT_WS(cT.`K`,cT.`M`, cT.`O`, cT.`Q`),' ','') LIKE '%주동%')
 */

$wSpecStr = array();
$wSpec = array();

$jstr = "";
$tmpTName = array("aT", "bT", "cT", "dT", "eT", "fT");
for ($i = 0; $i < 6; $i++) {

    if ($spec[$i]) {

        if( $spec[$i] == "연속책략" || $spec[$i] == "회심공격" || $spec[$i] == "강공" ) {
            $wherestr = "REPLACE(`specName`,' ','') = '$spec[$i]'";
        } else {
              $wherestr = "REPLACE(`specName`,' ','') LIKE '%$spec[$i]%'";
        }
        $querySpec = "SELECT REPLACE(`specName`,' ','') FROM `$specT` WHERE $wherestr";

        //$querySpec = "SELECT REPLACE(`A`,' ','') FROM `$specT` WHERE (REPLACE(`A`,' ','') LIKE '%$spec[$i]%')";
        //echo $query;
        
        $stmt = $conn->prepare($querySpec);
        $stmt->execute();
        $dataset = array();
        while ($row = $stmt->fetch()) {
            $dataset[] = $row;
        }

        if (!empty($dataset)) {
            //INNER JOIN `장수 정보` AS bT ON aT.`A` = bT.`A`
            $jstr .= ($i > 0) ? " INNER JOIN `$heroesT` AS " . $tmpTName[$i] . " ON aT.`heroNo` = " . $tmpTName[$i] . ".`heroNo`" : "";


            // and (REPLACE(CONCAT_WS(aT.`K`,aT.`M`, aT.`O`, aT.`Q`),' ','') LIKE '%전화위복%')
            //$wstr .= " and (";
            $j = 0;
            $tmpWSpecStr = array();
            $tmpWspec = array();
            //echo "// " . $i . ": ";
            foreach ($dataset as $data) {
                $spSpec = $data[0];
                //echo $spSpec . " ";
                $tmpWSpecStr[] = "((REPLACE(" . $tmpTName[$i] . ".`heroSpecs:30`, ' ', '') = '$spSpec') "
                        . "OR (REPLACE(" . $tmpTName[$i] . ".`heroSpecs:50`, ' ', '') = '$spSpec') "
                        . "OR (REPLACE(" . $tmpTName[$i] . ".`heroSpecs:70`, ' ', '') = '$spSpec') " 
                        . "OR (REPLACE(" . $tmpTName[$i] . ".`heroSpecs:90`, ' ', '') = '$spSpec') " 
                        . "OR (REPLACE(" . $tmpTName[$i] . ".`heroSpecs:태수`, ' ', '') = '$spSpec') "
                        . "OR (REPLACE(" . $tmpTName[$i] . ".`heroSpecs:군주`, ' ', '') = '$spSpec') )";
                      //  . "AND (".$tmpTName[$i]."`순번` > 0)";
                //"(REPLACE(CONCAT_WS(" . $tmpTName[$i] . ".`K`," . $tmpTName[$i] . ".`M`," . $tmpTName[$i] . ".`O`," . $tmpTName[$i] . ".`Q`),' ','') LIKE '%$spSpec%')";
                $tmpWspec[] = $spSpec;

                $j++;
            }
            $wSpecStr[] = $tmpWSpecStr;
            $wSpec[] = $tmpWspec;
            // $wstr .= " )";
        } else {
            die("fail");
        }
    }
}




$rawSize = 1;
$colSize = 0;
$sizeArr = array();

//echo "<br />";
for ($i = 0; $i < 6; $i++) {
    $size = count($wSpecStr[$i]);
    //echo "size" . $i . ": " . $size . " / ";
    $sizeArr[] = $size;
    $rawSize *= ($wSpecStr[$i]) ? $size : 1;
    $colSize += ($wSpecStr[$i]) ? 1 : 0;
}


echo "{";
for ($i = 0; $i < $rawSize; $i++) {
    $queryHero = "SELECT * FROM `$heroesT` AS aT" . $jstr . $wstr;

   // die($queryHero);

    echo "\"";
    $key_first = "";
    for ($j = 0; $j < $colSize; $j++) {
        $base = 1;
        $underBase = 1;
        for ($k = $j; $k < 6; $k++) {
            $base *= ($sizeArr[$k]) ? $sizeArr[$k] : 1;
            $underBase *= ($sizeArr[$k + 1]) ? $sizeArr[$k + 1] : 1;
        }

        $specNum = floor(($i % $base) / $underBase);
        //echo "[" . $j . floor(($i % $base)/$underBase) . "]:";
        //echo $wSpecStr[$j][($i%$sizeArr[$j])]." //";


        $key_first .= "*".$wSpec[$j][$specNum]."crlf";



        $queryHero .= " and " . $wSpecStr[$j][$specNum];
    }

    $key_first .= $line ? "*" . $line . "계crlf" : "";
    $key_first .= $lineage ? "*" . $lineage . " 계보crlf" : "";
    $key_first .= $cost_pivot ? "*코스트 " . (10 + $cost_pivot) ."crlf": "";
    $key_first .= $cost_more ? "*코스트 " . (10 + $cost_more) . "이상crlf" : "";
    $key_first .= $cost_below ? "*코스트 " . (10 + $cost_below) . "이하crlf" : "";
    $key_first .= $cost_ex ? "*코스트 " . (10 + $cost_ex) . "초과crlf" : "";
    $key_first .= $cost_less ? "*코스트 " . (10 + $cost_less) . "미만crlf" : "";
    //$key_first .= $vice ? "crlf" . $vice . "태수" : "";
    //$key_first .= $lord ? "crlf" . $lord . "군주" : "";
    echo $key_first;


    echo "\":";


   // $queryHero .= " ORDER BY `C`";
    $stmt = $conn->prepare($queryHero);
    $stmt->execute();
    $dataset = array();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    echo "[";
    if (!empty($dataset)) {

        //$dataSize = count($dataset);
        $n = 0;
        foreach ($dataset as $data) {

            $padd_line = $data[heroBranch];
            $n_padd_line = 4-mb_strlen( $padd_line, "utf-8");
           if ( $n_padd_line > 0 ) {
                for($np = 0; $np < $n_padd_line; $np++) {
                    $padd_line.="　";
               }
          }
            
            $padd_hero = $data[heroName];
            $n_padd_hero = 4-mb_strlen( $padd_hero, "utf-8");
            if ( $n_padd_hero > 0 ) {
                for($np = 0; $np < $n_padd_hero; $np++) {
                    $padd_hero.="　";
               }
          }



            echo (($n > 0) ? "," : "") . "{\"계열\":\"$padd_line\",\"COST\":\"" . (intval($data[heroCost]) + 10 ) . "\",\"이름\":\"$padd_hero\"}";
            $n++;
        }
    }
    echo "]";
    echo (($i < $rawSize - 1) ? "," : "");
}

echo "}";


//die("<br />raw:" . $rawSize . " col:" . $colSize);
//echo "\"$spSpec\":";
//0 연책00 연책강01 연책면02
//1 화책특10 산책특11 풍책특12
//
//  3진
//0 00 연책 화책특 00 10   3*0 + 1*0
//1 01 연책 산책특 00 11   3*0 + 1*1
//2 02 연책 풍책특 00 12   3*0 + 1*2 
//3 10 연책강 화책특 01 10  3*1 + 1*0
//4 11 연책강 산책특 01 11  3*1 + 1*1
//5 12 연책강 풍책특 01 12  3*1 + 1*2
//6 20 연책면 화책특 02 10  3*2 + 1*0
//7 21 연책면 산책특 02 11  3*2 + 1*1
//8 22 연책면 풍책특 02 12  3*2 + 1*2
//sizeArr : 3 3
//0 연책00 연책강01 연책면02
//1 화책전10 풍책전11
//
//      3진 2진
//0 00   00 00 연책 화책전 00 10    
//1 01   01 01 연책 풍책전 00 11     
//2 10   02 10 연책강 화책전 01 10 
//3 11    10 11 연책강 풍책전 01 11
//4 20   11 20 연책면 화책전 02 10
//5 21    12 21 연책면 풍책전 02 11
//sizeArr : 3 2
// 3 2 3
// 18
//0 000  0/6
//1 001  1/6
//2 002  2/6
//3 010  3/6         
//4 011  4/6       
//5 012  5/6      
//6 100   6/6
//7 101   7/6
//8 102   8/6
//9 110   9/6
//10 111  10%(3*2*3)/(2*3*1)  10%(3*2)/(3*1)
//11 112  11%(3*2*3)/(2*3*1)  11%(3*2)/3
//12 200  12%(3*2*3)/(2*3*1)  12%(3*2)/3       12%3/1
//13 201  13%(3*2*3)/(2*3*1)  13%6/3            13%3/1
//14 202  14%(3*2*3)/(2*3*1)  14%6/3         14%3/1
//15 210  15%(3*2*3)/(2*3*1)
//16 211  16%(3*2*3)/(2*3*1)
//17 212  17%(3*2*3)/(2*3*1)
?> 


