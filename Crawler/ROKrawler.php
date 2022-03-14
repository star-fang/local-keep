<?php

$number = $_GET['number'];


$returnAry = array();

if( !isset($number) ) {
    $returnAry['status'] = 'failure';
    $returnAry['message'] = '숫자를 입력하시오';
    die();
}

include_once 'Snoopy.class.php';


$snoopy = new snoopy;

$snoopy->fetch("https://rok.wiki/bbs/board.php?bo_table=hero&wr_id=".$number);
$txt=$snoopy->results;
$commander_start_pos = strpos($txt,'<div> <!-- 사령관 카드 !-->');
$commander_end_pos = strpos($txt,'<!--중단 에드센스 !-->');
$commander_info = substr($txt,$commander_start_pos, $commander_end_pos-$commander_start_pos);

//print_r($commander_info);

if( empty($commander_info) ) {
    $returnAry['status'] = 'failure';
    $returnAry['message'] = '사령관 정보 없음';
    die();
}

$returnAry['status'] = 'success';

$name_start_pos = strpos($commander_info,'<h2 class="commander_name_');
$name_end_pos = strpos($commander_info,'<figure class="cammander_s_container">');
$name_info = substr($commander_info,$name_start_pos, $name_end_pos-$name_start_pos);


$grade_info = "";
preg_match('/"(.*?)"/', $name_info, $grade_info);
$grade_str_eng = str_replace("commander_name_","",preg_replace('/[\"+]/',"",$grade_info[0]) );
//print_r($grade_str_eng);
$returnAry['grade_eng'] = $grade_str_eng;


$name_str=preg_replace("/\r|\n|\t/", "",preg_replace("(\<(/?[^\>]+)\>)", "", $name_info));

//print_r($name_str);
$returnAry['name'] = $name_str;

$nickname_start_pos = strpos($commander_info, '<div class="commander_nickname_');
$nickname_end_pos = strpos($commander_info, '<!-- 사령관 전문성 !-->');
$nickname_part = substr($commander_info,$nickname_start_pos, $nickname_end_pos-$nickname_start_pos);

$nickname_info = "";
preg_match_all('/\<p(.*?)\<\/p\>/',$nickname_part,$nickname_info);


$nickname_str = preg_replace("(\<(/?[^\>]+)\>)", "", $nickname_info[0][1]);
$gain_str = preg_replace("(\<(/?[^\>]+)\>)", "", $nickname_info[0][3]);
$civil_str = preg_replace("(\<(/?[^\>]+)\>)", "", $nickname_info[0][5]);

//echo '<br>별명: '.$nickname_str.'<br>';
//echo '획득: '.$gain_str.'<br>';
//echo '문명: '.$civil_str.'<br>';

$returnAry['nickname'] = $nickname_str;
$returnAry['gain'] = $gain_str;
$returnAry['civil'] = $civil_str;
/*
for( $i = 0; $i < count($nickname_info[0]); $i++ ) {
    if( $i % 2 == 0 ) {
        echo "<br>";
    } else {
        echo ": ";
    }
    echo preg_replace("(\<(/?[^\>]+)\>)", "", $nickname_info[0][$i]);
}
*/


$spec_start_pos = strpos($commander_info,'<div class="commander_specialties_subject">');
$spec_end_pos = strpos($commander_info,'<!--1번째 스킬-->');
$spec_part = substr($commander_info,$spec_start_pos, $spec_end_pos-$spec_start_pos);
//print_r($spec_part);

$spec_info = "";
preg_match_all('/\<p.*?\<\/p\>/is',$spec_part,$spec_info);

//print_r( $spec_info[0] );

$spec_strs = array();
for( $i = 0; $i < count($spec_info[0]); $i++ ) {
    if( $i % 2 != 0 ) {
    //echo "<br>";
    $index = floor($i / 2);
    //echo "특성".($index + 1).": ";
    $spec_strs[$index] = preg_replace("(\<(/?[^\>]+)\>)", "", $spec_info[0][$i]);
    $returnAry['spec'.$index] = preg_replace("/\r|\n|\t/", "",$spec_strs[$index]);
    //echo $spec_strs[$index];
    }
}

//echo "<br>";

$skill_start_pos = strpos($commander_info,'<div class="skill">');
$skill_part = substr($commander_info,$skill_start_pos);
$skill_part = str_replace('<b>업그레이드 미리보기:</b>','',$skill_part);
$skill_part = str_replace('<b>필요한 분노 포인트 :','필요한 분노 포인트 :',$skill_part);
//print_r($skill_part);
$skill_names = "";
preg_match_all('/\<p\>\<b\>(.*?)\<\/b\>\<\/p\>/',$skill_part,$skill_names);
//print_r( $skill_names[0] );

$skill_conditions = "";
preg_match_all('/\<p\>\<i\>(.*?)\<\/i\>\<\/p\>/',$skill_part,$skill_conditions);
//print_r( $skill_conditions[0] );

$skill_subjects = "";
preg_match_all('/\<div class\=\"skill_subject.*?\<\/div\>/is',$skill_part,$skill_subjects);
//print_r( $skill_subjects[0] );


for( $i = 0; $i < count($skill_names[0]); $i++ ) {
    //echo "<br>스킬".($i + 1).": ";
    //echo preg_replace("(\<(/?[^\>]+)\>)", "",$skill_names[0][$i])." (";
    //echo preg_replace("(\<(/?[^\>]+)\>)", "",$skill_conditions[0][$i]).")<br>";
    //echo $skill_subjects[0][$i]."<br>";
    $returnAry['skill_name'.$i] = preg_replace("(\<(/?[^\>]+)\>)", "",$skill_names[0][$i]);
    $returnAry['skill_condition'.$i] = preg_replace("(\<(/?[^\>]+)\>)", "",$skill_conditions[0][$i]);
    $returnAry['skill_subject'.$i] = str_replace("\r","<br>",preg_replace("/\n|\t/", "", preg_replace("(\<(/?[^\>]+)\>)", "",$skill_subjects[0][$i])));
}

echo json_encode($returnAry,JSON_UNESCAPED_UNICODE);


//$split=array_filter(explode(",,,",preg_replace("(\<(/?[^\>]+)\>)", ",,,", $commander_info)));

//print_r($split);
?>