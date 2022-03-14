<?php
$db = $_POST['db'];
$apiKey = $_POST['key'];
$returnJson;

require_once './DBConfig.php';

$stmt = $conn->prepare("SHOW TABLES");
if( $stmt->execute() ) {
    $tableList = array();
    while($row = $stmt->fetch()) {
        $tableInfo = array();
        $tableInfo['table'] = $row[0];
        $tableInfo['lastModified'] = getLastModified($row[0], $conn);
        array_push( $tableList, $tableInfo);
    }
    echo json_encode($tableList,JSON_UNESCAPED_UNICODE);
} else {
    $jsonResult->status="fail";
    $jsonResult->message="fail to get table list";
    die(json_encode($jsonResult,JSON_UNESCAPED_UNICODE));
}

function getLastModified($table_name, $conn) {
    $stmt = $conn->prepare("SELECT MAX(lastModified) FROM `$table_name`");
    if( $stmt->execute()) {
        $row = $stmt->fetch();
        return date(strtotime($row[0]));
    } else {
        $stmt = $conn->prepare("SELECT UPDATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE `TABLE_NAME` = '$table_name'");
        if( $stmt->execute()) {
            $row = $stmt->fetch();
            return date(strtotime($row[0]));
        } else {
            return null;
        }
        //$jsonResult->status="fail";
        //$jsonResult->message="fail to read update time";
        //die(json_encode($jsonResult,JSON_UNESCAPED_UNICODE));
    }
}

  
 
?> 
