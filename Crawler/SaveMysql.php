<?php
if(!function_exists('json_decode')) die('Your host does not support json');

$json = json_decode(file_get_contents('php://input'), true);

ini_set('max_execution_time', '-1');

$pass = $json['pass'];
unset($json['pass']);

require_once 'dbConfig.php';
$db = new DB_Connection('rok_info', $pass);

$specIds = "[";
for( $i = 0; $i < 3; $i++ ) {
  //	id	name	position	description
  $spec = $json['spec'.($i+1)];
  if( $i == 0 ) {
    if( mb_substr($spec,0, 2,'UTF-8') == '궁병') {
      $spec = '궁병 유닛';
    } else if( mb_substr($spec,0, 2,'UTF-8') == '보병') {
      $spec = '보병 유닛';
    } else if( mb_substr($spec,0, 3,'UTF-8') == '기마병') {
      $spec = '기마병 유닛';
    }
  }
  $insertionResult = $db->insertRow('specifications', "'$spec','$i'", '`name`, `position`');
  if( $insertionResult['status'] == 'succ' ) {
    $specIds .= $insertionResult['id'].', ';
  } else {
    $rows = $db->searchRows('name', $spec, 'specifications', 'id');
    $specIds .= $rows[0]['id'].', ';
  }
  unset($json['spec'.($i+1)]);
}
$specIds .= ']';
$specIds = str_replace(', ]', ']', $specIds);

$skillIds = "[";
for( $i = 0; $i < 5; $i++ ) {
  $skillName = $json['skill'.($i+1).'_name'];
  $skillCondition = $json['skill'.($i+1).'_condition'];
  $skillSubject = str_replace("'","\'", $json['skill'.($i+1).'_subject']);

  if( $skillName != "" &&  $skillCondition != "" ) {
  
    $insertionResult = $db->insertRow('skills', "'$skillName','$skillCondition', '$skillSubject'", '`name`, `property`, `description`');
    if( $insertionResult['status'] == 'succ' ) {
      $skillIds .= $insertionResult['id'].', ';
    } else {
      $rows = $db->searchRows('name', $skillName, 'skills', 'id');
      $skillIds .= $rows[0]['id'].', ';
    }
  }

  unset($json['skill'.($i+1).'_name']);
  unset($json['skill'.($i+1).'_condition']);
  unset($json['skill'.($i+1).'_subject']);
}

$skillIds .= ']';
$skillIds = str_replace(', ]', ']', $skillIds);

$civilName = $json['civil'];
unset($json['civil']);
$insertionResult = $db->insertRow('civilizations', "'$civilName'", '`name`');
$civilId;
if( $insertionResult['status'] == 'succ' ) {
  $civilId = $insertionResult['id'];
} else {
  $rows = $db->searchRows('name', $civilName, 'civilizations', 'id');
  $civilId = $rows[0]['id'];
}



$keys = array_keys($json);

$rowClause = '';
for($i = 0; $i < count($keys); $i++) {
  $rowClause .= '\''.$json[$keys[$i]].'\''.', ';
}

$rowClause .= "'$civilId', '$specIds', '$skillIds'";

$columnClause = implode(',',$keys);
$columnClause .= ', civilization_id, spec_ids, skill_ids';

$insertionResult = $db->insertRow('commanders', $rowClause, $columnClause);

$status = $insertionResult['status'];
if( $status != 'succ') {
  $status .= ': '.$insertionResult['message'];
}

echo $status;

?>