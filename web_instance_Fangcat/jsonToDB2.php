<?php
require_once 'DBConfig.php';
ini_set('max_execution_time', '-1');

if(!function_exists('json_decode')) die('Your host does not support json');

$jsonDir = $_GET['json'];
$tableName = $_GET['table'];
$json = file_get_contents($jsonDir);
$json = trim($json);


$decodedFull = json_decode($json,true);
echo(count($decodedFull)." json record(s) found.. converting to mysql table</br></br>");
$global_table = array(); // Contains each decoded json (TABLE ROW)
$global_columns = array(); // Contains columns for SQL

function refineValue($value, $column, &$global_columns, $conn) {
    $cur_type = getType($value);
    $value_length = $global_columns[$column]["length"]?:0;

  //  echo( $column.' before refine : ' .$value. ' ' .$cur_type. ' ' . $value_length.'</br>');
    
    switch($cur_type) {
        case 'integer':
            $value_type = $global_columns[$column]["type"]?:'int';
            $value_length = max($value_length,mb_strlen((string)$value),'utf-8');
         break;
         case 'array':
            $value_type = 'json';
            $value = $conn->quote(json_encode($value));
        break;
        default:
        $value_type = 'varchar';
        $value = $conn->quote($value);
        $value_length = max($value_length,mb_strlen($value,'utf-8'));
    }

    $global_columns[$column]["type"] = $value_type;
    $global_columns[$column]["length"] = $value_length;

   // echo( $column.' after refine : '.$value. ' ' .$cur_type. ' ' . $value_length.'</br>');
    return $value;
} // function refineValue

//$i = 0;
$decodedKeys = array_keys($decodedFull);
for( $i= 0; $i < count($decodedFull); $i++ ){
    $primaryKey  = $decodedKeys[$i];
    $tuple = $decodedFull[$primaryKey];

    $global_table[$i]["id"] = refineValue($primaryKey, "id", $global_columns, $conn);

    if( gettype($tuple) == 'array' ) {
        $primaryKey = $tuple["id"]?:$primaryKey;
        foreach($tuple as $column => $value) {
             $global_table[$i][$column] = refineValue($value, $column, $global_columns, $conn);
        }
    } else {
        $global_table[$i]["value"] = refineValue($tuple, "value", $global_columns, $conn);
    }
    //$i++;
} // foreach decodedFull

$query = "CREATE TABLE IF NOT EXISTS `$tableName` (";
  foreach($global_columns as $column => $info) {

    $colOption = $info["type"] == 'json'? 'json' : $info["type"]."(".$info["length"].")";
    $query .= "`$column` $colOption,";
}

$query .= " 
PRIMARY KEY  (`id`)
)";

$stmt = $conn->prepare($query);
if(  $stmt->execute() ) {
  echo( "create success or already exist:</br> $query</br></br>");
} else {
  die( "create failure:</br>$query</br></br>".$stmt->errorInfo()[2]);
}


// this is faster than foreach
$count = 0;
for($i=0; $i<count($global_table); $i++) {
  $clauseArr = array();

    foreach($global_table[$i] as $key => $value){
      array_push($clauseArr, "`$key` = $value");
    }

    $sqlclause = implode(',',$clauseArr);
    $insertSql = "INSERT INTO `$tableName` SET $sqlclause";
    
    //echo($insertSql.'</br>');

    $stmt = $conn->prepare($insertSql);

   // echo( $insertSql.'</br></br>' );

    
    if( $stmt->execute() ) {
      echo( '.' );
      $count++;
    } else {
        switch( $stmt->errorInfo()[0] ) {
            case 42000:
                die('</br>'.$global_table[$i]["id"].": ".$stmt->errorInfo()[2]);
            break;
            case 42522: // column not found
                die('</br>'.$global_table[$i]["id"].": column not found. please alter table!");
            break;
            case 23000: // already exist
                echo(',');
            break;
            default:
            die( "</br>insert failure :".$stmt->errorInfo()[0] );
        }
    }

    
} // for i

echo( "</br>$count of " .count($global_table)." record(s) inserted" );
?> 