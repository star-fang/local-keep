<?php

require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);


$cmd = $obj->key0;
$name = $obj->key1;
$mine = $obj->key2;
$ally = $obj->key3;
$des = $obj->key4;
$date = $obj->key5;
$yymm = substr($date, 0, 4);
$dd = "a" . substr($date, -2);


if ($cmd == "수급") {
    $query = "SELECT `$dd`, `A` FROM `$yymm` WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    //echo $query;

    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (empty($dataset)) {

        $query = "INSERT INTO `$yymm` (`A`,`B`) VALUES ('$name', '$ally')";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        echo ("newone");
    }

    $mineInfo;
    if ($dataset[0][$dd] == "") {
        $mineInfo = "0:0:0:0:0:";
    } else {
        $mineInfo = $dataset[0][$dd];
    }
    $splitInfo = explode(":", $mineInfo);
    $mineArray = array(
        "C" => $splitInfo[0],
        "D" => $splitInfo[1],
        "E" => $splitInfo[2],
        "F" => $splitInfo[3],
        "G" => $splitInfo[4],
        "H" => $splitInfo[5],
    );

    if ($mineArray[$mine] > 0) {
        die("already");
    }

    $mineArray[$mine] = 1;
    if ($des != "") {
        $mineArray["H"] = $des;
    }

    $mineStr = "";
    foreach ($mineArray as $mines) {
        $mineStr .= $mines;
        $mineStr .= ":";
    }
    $mineStr = substr($mineStr, 0, -1);
    $query = "UPDATE `$yymm` SET `$dd` = '$mineStr' WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    echo "succ";
} else if ($cmd == "추가수급") {
    $query = "SELECT `$dd`, `A` FROM `$yymm` WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    //echo $query;

    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (empty($dataset)) {

        $query = "INSERT INTO `$yymm` (`A`,`B`) VALUES ('$name', '$ally')";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        echo ("newone");
    }

    $splitInfo = explode(":", $dataset[0][$dd]);
    $mineArray = array(
        "C" => $splitInfo[0],
        "D" => $splitInfo[1],
        "E" => $splitInfo[2],
        "F" => $splitInfo[3],
        "G" => $splitInfo[4],
        "H" => $splitInfo[5],
    );

    if ($mineArray[$mine] > 1) {
        die("already");
    }

    $mineArray[$mine] = 2;
    if ($des != "") {
        $mineArray["H"] = $des;
    }

    $mineStr = "";
    foreach ($mineArray as $mines) {
        $mineStr .= $mines;
        $mineStr .= ":";
    }
    $mineStr = substr($mineStr, 0, -1);
    $query = "UPDATE `$yymm` SET `$dd` = '$mineStr' WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    echo "succ";
} else if ($cmd == "신규") {
    $query = "SELECT `A` FROM `$yymm` WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (!empty($dataset)) {
        die("beforeone");
    } else {
        $query = "INSERT INTO `$yymm` (`A`,`B`) VALUES ('$name', '$ally')";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo "newone";
    }
} else if ($cmd == "변경") {
    $query = "SELECT `$dd` FROM `$yymm` WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    //echo $query;

    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (empty($dataset)) {


        die("noone");
    } else {


        $splitInfo = explode(":", $dataset[0][$dd]);
        $mineArray = array(
            "C" => $splitInfo[0],
            "D" => $splitInfo[1],
            "E" => $splitInfo[2],
            "F" => $splitInfo[3],
            "G" => $splitInfo[4],
            "H" => $splitInfo[5],
        );


        if ($des != "") {
            $mineArray["H"] = $des;
        }

        $mineStr = "";
        foreach ($mineArray as $mines) {
            $mineStr .= $mines;
            $mineStr .= ":";
        }
        $mineStr = substr($mineStr, 0, -1);











        $allydesStr = "";
        if ($ally != "" && $des != "") {
            $allydesStr = "`B` = '$ally', `$dd` = '$mineStr'";
        } else if ($des != "") {
            $allydesStr = "`$dd` = '$mineStr'";
        } else if ($ally != "") {
            $allydesStr = "`B` = '$ally'";
        }


        $query = "UPDATE `$yymm` SET $allydesStr WHERE `A` = '$name'";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        echo "alter";
    }
} else if ($cmd == "조회") {

    if ($name != null) {
        $nameInfo = "(`A` = '$name')";
        echo "[";
        for ($i = 18; $i < 20; $i++) {
            $year = $i;
            $year_now = (int) date("Y");

            $month_now = (int) date("m");
            $date_now = (int) date("d");
            //echo "냥".$year_now . " ". $month_now."냐앙";
            $month_due = ($i + 2000 === $year_now) ? $month_now : 12;


            for ($j = 1; $j <= $month_due; $j++) {

                $month = ($j < 10) ? "0" . $j : "" . $j;
                $ddbb = $year . $month;
                
                $date_due = (($i + 2000 === $year_now)&&($j ==  $month_now))? $date_now : 31;

                for ($k = 1; $k <= $date_due; $k ++) {
                    $date = ($k < 10) ? "a0" . $k : "a" . $k;
                    $query = "SELECT `$date` FROM `$ddbb` WHERE $nameInfo AND ((`$date` LIKE '%1%') OR (`$date` LIKE '%2%'))";
                    // echo($query);
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                        $dataset[] = $row;
                    }

                    if (!empty($dataset)) {

                        foreach ($dataset as $data) {
                            $ii = $data[$date];
                            if ($ii) {
                                echo "{\"A\":\"$k"."일\",\"B\":\"$year"."년 "."$month"."월\",\"C\":\"$data[$date]\"},";
                            }
                        }
                    }
                }
            }
        }
        die("]");
    }



    $allyInfo = "";
    if ($ally != null) {
        $allyInfo = "(`B` = '$ally') and ";
    }



    //$replaceStr = "REPLACE(`$dd`,NULL,'') != ':::::'";
    $orderStr = "ORDER BY ( case  when `B` = '명송' then 2 when `B` =  '휴게송' then 2 when `B` = '곧' then 3 when `B` = '도나스' then 1 else 5 end )";

    $query = "SELECT `A`, `B`, `$dd` FROM `$yymm` WHERE $allyInfo (SUBSTR(REPLACE(`$dd`,'NULL','0'),1,10) <> '0:0:0:0:0:') and (`$dd` <> '') $orderStr";

    //die($query);
    // $query = "SELECT * FROM `$date` WHERE $allyInfo ((`C`>0 ) or (`D`>0 ) or (`E`>0 ) or (`F`>0 ) or (`G`>0 )) $orderStr";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (empty($dataset)) {
        die("noone");
    } else {
        echo "[";
        foreach ($dataset as $data) {
            echo "{\"A\":\"$data[A]\",\"B\":\"$data[B]\",\"C\":\"$data[$dd]\"},";
        }
        echo "]";
    }
} else if ($cmd == "초기화") {
    $query = "SELECT * FROM `$yymm` WHERE `A` = '$name'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (empty($dataset)) {
        die("noone");
    } else {

        $query = "UPDATE `$yymm` SET `$dd` = '0:0:0:0:0:' WHERE `A` = '$name'";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        echo clear;
    }
}



//echo "{\"A\":\"$data[0]\",\"B\":\"$data[1]\",\"C\":\"$data[2]\"},"; }
?>