<?php
$db = 'fangcat';

require './DBConfig.php';

$stmt = $conn->prepare("SHOW TABLES");
if( $stmt->execute() ) {
    $tableList = array();
    while($row = $stmt->fetch()) {
        $tableInfo = array();
        $tableInfo['table'] = $row[0];
        $tableInfo['lastModified'] = getLastModified($row[0], $conn);
        array_push( $tableList, $tableInfo);
    }
    //var_dump($tableList);
    //echo json_encode($tableList,JSON_UNESCAPED_UNICODE);
} else {
    $jsonResult->status="fail";
    $jsonResult->message="fail to get table list";
    die(json_encode($jsonResult,JSON_UNESCAPED_UNICODE));
}

function getLastModified($table_name, $conn) {
    $stmt = $conn->prepare("SELECT MAX(`lastModified`) FROM `$table_name`");
    if( $stmt->execute()) {
        $row = $stmt->fetch();
        //return date("Y-m-d h:i:s",strtotime($row[0]));
        return strtotime($row[0]);
    } else {
        return 0;
        //$jsonResult->status="fail";
        //$jsonResult->message="fail to read update time";
        //die(json_encode($jsonResult,JSON_UNESCAPED_UNICODE));
    }
}

  
 
?> 
