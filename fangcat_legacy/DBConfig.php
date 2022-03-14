<?php

$dsn = 'mysql:dbname=clavis;host=localhost';
$user = 'root';
$password = '1111';

$suggestT = "건의사항";
$competAgendaT = "경쟁일정";
$relationT = "병종 상성";
$movingT = "병종 이동력 소모";
$branchT = "병종 정보";
$terrainT = "병종 지형상성";
$itemT = "보물";
$itemCateT = "보물 분류";
$magicReinfCostT = "보패 강화비용";
$magicPrfxSpecValueT = "보패 접두사";
$magicSfxStatT = "보패 접미사";
$magicCombT = "보패 조합";
$specT = "설명";
$annihAgendaT = "섬멸일정";
$destinyT = "인연";
$heroesT = "장수 정보";

try {
    $conn = new PDO($dsn, $user, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
} catch (PDOException $e) {
    $returnJson->status="fail";
    $returnJson->message= "Connection failed: " . $e->getMessage();
    die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
}

  
 
?> 
