<?php

require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);

$s1 = $obj->{'key0'};
$s2 = $obj->{'key1'};

$stmt = $conn->prepare("DESCRIBE `$relationT`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$branchName = "branchName";
$relations = "relations:";

if ($s1 == null) {
    die("fail");
} else if ($s2 == null) {

    foreach ($finfo as $fval) {
        if (strpos(str_replace(" ", "", $fval), $s1) !== false) {
            $s1 = explode(":",$fval)[1];
            break;
        }
    }



    $query = "SELECT * FROM `$relationT` WHERE (`$branchName` = '$s1')";
    //die($query);
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dataset = array();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }



    $query = "SELECT `$branchName`,`$relations"."$s1` FROM `$relationT`";
    //die($query);
    $stmt2 = $conn->prepare($query);
    $stmt2->execute();
    $dataset2 = array();
    while ($row = $stmt2->fetch()) {
        $dataset2[] = $row;
    }

    // die( $dataset2[0][1] );



    if (!empty($dataset)) {

        echo "{\"" . $dataset[0][$branchName] . "\":{";
        $line_num = 0;
        foreach ($finfo as $fval) {
            if ($fval != $branchName) {
                echo "\"".explode(":",$fval)[1]."\":\"" . $dataset[0][$fval] . " / " . $dataset2[$line_num++][1] . "\",";
            }
            $i++;
        }
        echo "}}";
    }
} else {
    foreach ($finfo as $fval) {
        if (strpos(str_replace(" ", "", $fval), $relations.$s1) !== false) {
            $s1 = explode(":",$fval)[1];
            break;
        }
    }
    
    
    foreach ($finfo as $fval) {
        if (strpos(str_replace(" ", "", $fval), $relations.$s2) !== false) {
            $s2 = explode(":",$fval)[1];
            break;
        }
    }
    $query = "SELECT `$branchName`, `$relations"."$s2` FROM `$relationT` WHERE (`$branchName` = '$s1')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $existing = $stmt->fetch();

    $query = "SELECT `$branchName`, `$relations"."$s1` FROM `$relationT` WHERE (`$branchName` = '$s2')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $existing2 = $stmt->fetch();

    echo "{\"" . $existing[$branchName] . ">" . $s2 . "\":{\"값\": \"" . $existing[$relations.$s2] . " / " . $existing2[$relations.$s1] . "\"}}";
}
?> 
