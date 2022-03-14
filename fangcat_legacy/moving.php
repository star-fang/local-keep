<?php

require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);

$s1 = $obj->{'key0'};
$s2 = $obj->{'key1'};


$stmt = $conn->prepare("DESCRIBE `병종 이동력 소모`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);



if ($s1 == null) {
    die("fail");
} else if ($s2 == null) {

    $query = "SELECT * FROM `병종 이동력 소모` WHERE (`병종` LIKE '%$s1%')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dataset = array();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }
    if (!empty($dataset)) {

        echo "{\"" . $dataset[0]["병종"] . "\":{";
        foreach ($finfo as $fval) {
            if ($fval != "병종") {

                $len = mb_strlen($fval, "utf-8");
                $padd = ($len < 3 ) ? "　" . $fval : $fval;
                $padd = ($len < 2 ) ? "　" . $padd : $padd;


                echo "\"" . $padd . "\":\"" . $dataset[0][$fval] . "\",";
            }
            $i++;
        }
        echo "}}";
    } else {

        foreach ($finfo as $fval) {
            if (strpos(str_replace(" ", "", $fval), $s1) !== false) {
                $s1 = $fval;
                break;
            }
        }


        $query = "SELECT `병종`, `$s1` FROM `병종 이동력 소모`";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $dataset = array();
        while ($row = $stmt->fetch()) {
            $dataset[] = $row;
        }
        if (empty($dataset)) {
            die("fail");
        }

        echo "{\"" . $s1 . "\":{";

        foreach ($dataset as $data) {
            echo "\"" . mb_substr($data[0], 0, 2, "utf-8") . "\":\"" . $data[$s1] . "\",";
        }
        echo "}}";
    }
} else {
    foreach ($finfo as $fval) {
        if (strpos(str_replace(" ", "", $fval), $s2) !== false) {
            $s2 = $fval;
            break;
        }
    }
    $query = "SELECT `병종`, `$s2` FROM `병종 이동력 소모` WHERE (`병종` LIKE '%$s1%')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $existing = $stmt->fetch();

    echo "{\"" . $existing["병종"] . " " . $s2 . "\":{\"값\":" . $existing[$s2] . "}}";
}
?> 
