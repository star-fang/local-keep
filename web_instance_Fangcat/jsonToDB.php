<?php
require_once 'DBConfig.php';
ini_set('max_execution_time', '-1');

$jsonDir = $_GET['json'];
$tableName = $_GET['table'];
$json = file_get_contents($jsonDir);
$json = trim($json);
//if($json[0] == '[') {
  $json = mb_substr($json,1,mb_strlen($json,'utf-8')-2,'utf-8');
  $json = trim($json).',';
  //die($json);
//}

$arr = explode('},',$json);
$global_arr = array(); // Contains each decoded json (TABLE ROW)
$global_keys = array(); // Contains columns for SQL
$global_ids = array();
$id_length = 5; // minimum length of id (digits)

if(!function_exists('json_decode')) die('Your host does not support json');

echo((count($arr) - 1)."json record(s) found.. converting to mysql table</br></br>");
// except last empty element

for($i=0; $i<(count($arr) - 1); $i++) {
  echo($arr[$i].'}</br></br>');
  $decoded = json_decode($arr[$i].'}',true);
  if(!$decoded) {
    $firstColonPos = mb_strpos($arr[$i], ':', 0 ,'utf-8');
    $refinedElmnt = mb_substr($arr[$i], $firstColonPos+1, NULL, 'utf-8').'}';
    $decoded = json_decode($refinedElmnt,true);
    $id_integer = preg_replace('/[^0-9]/', '', mb_substr($arr[$i], 0, $firstColonPos, 'utf-8'));
    $id_length = max($id_length,strlen((string)$id_integer));
    $global_ids[$i] = $id_integer;
  } 
  
  foreach($decoded as $key=> $value) {
      $cur_type = getType($value);
      $value_length = $global_keys[$key]["length"]?:0;
      
        switch($cur_type) {
          case 'integer':
            $value_type = $global_keys[$key]["type"]?:'int';
            $value_length = max($value_length,mb_strlen((string)$value),'utf-8');
          break;
          case 'array':
            $value_type = 'json';
            $value = $conn->quote(json_encode($value));
          break;
          default:
          $value_type = 'varchar';
          $value = $conn->quote($value);
          $value_length = max($value_length,mb_strlen($value),'utf-8');
        }
      
        //echo($decoded['name'].' : '.$key.' : '.$value_type.'</br>');
      
        $global_keys[$key]["type"] = $value_type;
        $global_keys[$key]["length"] = $value_length;
        $global_arr[$i][$key] = $value;
    }


   // echo($refinedElmnt.'</br>');
}



die();


// CREATE SQL TABLE
//int(11) unsigned NOT NULL auto_increment
//die( count($global_ids) );
$idOption = empty($global_ids)? '' : "`id` int($id_length),";
$query = "CREATE TABLE IF NOT EXISTS `$tableName` (
  $idOption";
  foreach($global_keys as $key => $val)
  {
    $colOption = $val["type"] == 'json'? 'json' : $val["type"]."(".$val["length"].")";
	$query .= "`$key` $colOption,";
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


// iterate $global_arr
for($i=0; $i<count($global_arr); $i++) // this is faster than foreach
{
  $clauseArr = array();
  if(!empty($global_ids[$i])) {
    array_push($clauseArr, "`id` = $global_ids[$i]");
  }
  
    foreach($global_arr[$i] as $key => $value){
      array_push($clauseArr, "`$key` = $value");
    }
    $sqlclause = implode(',',$clauseArr);
    $insertSql = "INSERT INTO `$tableName` SET $sqlclause";
    
    //echo($insertSql.'</br>');

    $stmt = $conn->prepare($insertSql);

   // echo( $insertSql.'</br></br>' );

    
    if( $stmt->execute() ) {
      echo( "number $i record successfully inserted </br>" );
    } else if( $stmt->errorInfo()[0] == 42000) {
      echo( "$insertSql</br>insert failure :".$stmt->errorInfo()[2].'</br>');
    } else if( $stmt->errorInfo()[0] != 23000 ){
      echo(  "$insertSql</br>insert failure :".$stmt->errorInfo()[0].'</br>' );
    }
    
} // for i
?> 