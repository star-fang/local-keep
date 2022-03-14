<?php

require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);

$s1 = $obj->{'key0'};
$stat = $obj->{'key1'};
$prefixColStrKor = "접두사";
$prefixColStr = "prefixName";
$effectStrKor = "효과";
$effectStr = "prefixSpec";
$statStrKor = "스탯";
$statStr = "prefixStat";

$stmt = $conn->prepare("DESCRIBE `$magicPrfxSpecValueT`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stat = ($stat > 200 || $stat == null) ? 200 : $stat;

if ($s1 == null) {
    die("fail");
} else {

    $query = "SELECT * FROM `$magicPrfxSpecValueT` WHERE (`$prefixColStr` LIKE '%$s1%')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dataset = array();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }
    if (!empty($dataset)) {

        echo "[";
        foreach ($dataset as $data) {
            echo "{";
            foreach ($finfo as $fval) {

                if (strpos($fval, 'Lv') !== false) {
                    echo "\"".explode(':',$fval)[1]."\":\"" . " " . ($data[$fval] * $stat / 200) . "\",";
                } else if ($fval == $statStr) {
                    $whatStat = $data[$fval];
                    echo "\"$statStrKor\":\"" . $whatStat . "  " . $stat . "\",";
                } else if ($fval == $effectStr) {
                    echo "\"$effectStrKor\":\"" . $data[$fval] . "\",";
                } else if ($fval == $prefixColStr) {
                    echo "\"$prefixColStrKor\":\"" . $data[$fval] . "\",";
                }
                
            }
            echo "},";
        }
        echo "]";
    } else {

        $query = "SELECT * FROM `$magicPrfxSpecValueT` WHERE (REPLACE(`$effectStr`,' ','') LIKE '%$s1%')";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $dataset = array();
        while ($row = $stmt->fetch()) {
            $dataset[] = $row;
        }

        if (!empty($dataset)) {
            echo "[";
            foreach ($dataset as $data) {
                echo "{";
                foreach ($finfo as $fval) {

                    if (strpos($fval, 'Lv') !== false) {
                        echo "\"".explode(':',$fval)[1]."\":\"" . " " . ($data[$fval] * $stat / 200) . "\",";
                    } else if ($fval == $statStr) {
                        $whatStat = $data[$fval];
                        echo "\"$statStrKor\":\"" . $whatStat . "  " . $stat . "\",";
                    } else if ($fval == $effectStr) {
                        echo "\"$effectStrKor\":\"" . $data[$fval] . "\",";
                    } else if ($fval == $prefixColStr) {
                        echo "\"$prefixColStrKor\":\"" . $data[$fval] . "\",";
                    }
                }
                echo "},";
            }
            echo "]";
        } else {
            die("fail");
        }
    }
}
?> 
