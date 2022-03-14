<?php
ini_set('max_execution_time', '-1');
include_once 'Snoopy.class.php';

$contents = array();
$contents['economic'] = "
        {{BlockGalleryItemBuilding|Farm}}
        {{BlockGalleryItemBuilding|Lumber Mill}}
        {{BlockGalleryItemBuilding|Quarry}}
        {{BlockGalleryItemBuilding|Goldmine}}
        {{BlockGalleryItemBuilding|Academy}}
        {{BlockGalleryItemBuilding|Storehouse}}
        {{BlockGalleryItemBuilding|Alliance Center}}
        {{BlockGalleryItemBuilding|Builder's Hut|civ=6|age=4}}
        {{BlockGalleryItemBuilding|Shop|civ=6|age=4}}
        {{BlockGalleryItemBuilding|Trading Post}}
        {{BlockGalleryItemBuilding|Lyceum of Wisdom}}
        {{BlockGalleryItemBuilding|Courier Station|age=3}}";
$contents['military'] = "
        {{BlockGalleryItemBuilding|Tavern}}
        {{BlockGalleryItemBuilding|Scout Camp|civ=6|age=4}}
        {{BlockGalleryItemBuilding|Barracks}}
        {{BlockGalleryItemBuilding|Archery Range}}
        {{BlockGalleryItemBuilding|Stable}}
        {{BlockGalleryItemBuilding|Siege Workshop}}
        {{BlockGalleryItemBuilding|Hospital}}
        {{BlockGalleryItemBuilding|Monument|civ=6|age=4}}
        {{BlockGalleryItemBuilding|Castle}}
        {{BlockGalleryItemBuilding|Blacksmith}}
        {{BlockGalleryItemBuilding|Bulletin Board|age=4}}";

$contents['other'] = "
        {{BlockGalleryItemBuilding|City Hall}}
        {{BlockGalleryItemBuilding|Wall|civ=6|age=4}}
        {{BlockGalleryItemBuilding|Watchtower|civ=6|age=4}}";
$contentAry = array();
$tableAry = array();
$eachId = 100000;


$columns = array('level','requirements','cost',	'time',	'power', 'reward');

foreach( array_keys($contents) as $key ) {
    //echo $key."---<br>";
    $list = "";
    preg_match_all('/BlockGalleryItemBuilding\|.*?\}/', $contents[$key] , $list);
    //print_r($list[0]);
    for( $i = 0; $i < count($list[0]); $i++ ) {
        if( true ) {
        $name = trim(preg_replace('/\s+/',"_", str_replace( "}", "", explode('|',$list[0][$i])[1] )));
        //echo $name."<br>";

        $contentRow = array();
        $contentId = ($idValue++) + 200000;
        $contentRow['id'] = $contentId;
        $contentRow['nameEng'] = $name;
        $contentRow['name'] = "";
        $contentRow['category'] = $key;
        switch( $key ) {
            case 'economic':
                $contentRow['categoryKor'] = '경제';
            break;
            case 'military':
                $contentRow['categoryKor'] = '군사';
            break;
            default:
            $contentRow['categoryKor'] = '기타';
        }

        $contentRow['facts'] = array();
        
        crawlingBuilding( $tableAry, $name, $contentId, $eachId, $contentRow, $columns );
        array_push($contentAry, $contentRow);
        
        sleep(1);
        }
    } // for i
     
} // foreach
echo '{"type": "table","name": "building","database": "rok_json", "data": ';
echo json_encode($tableAry,JSON_UNESCAPED_UNICODE);
echo '},
{"type": "table","name": "buildContent","database": "rok_json", "data": ';
echo json_encode($contentAry,JSON_UNESCAPED_UNICODE);
echo '},';

function crawlingBuilding( &$tableArr, $name, $contentId, &$eachId, &$contentRow, $columns) {


    $tableArrPiece = array();

    $snoopy = new snoopy;

    $snoopy->fetch("https://riseofkingdoms.fandom.com/wiki/Buildings/".$name);
    $txt=$snoopy->results;
    
    
    $tables = "";
    preg_match_all('/\<table.*?\<\/table\>/is',$txt,$tables);

    for( $i = 0; $i < count($tables[0]); $i++ ) {
        $className = "";
        preg_match_all('/class=".*?"/is',$tables[0][$i],$className);
        $th_row;
        switch( str_replace('class="', '',$className[0][0]) ) {
            //case 'article-table"':
            case 'article-table building-table"':
                $th_row = 0;
            break;
            case 'article-table mw-collapsible"':
            case 'article-table mw-collapsible mw-made-collapsible"':
                $th_row = 1;
            break;
            default:
            $th_row = -1;
    
        }
    
        
        if( $th_row > -1 ) {
    
            $tr_list = "";
            preg_match_all('/\<tr.*?\<\/tr\>/is',$tables[0][$i],$tr_list);
    
            $th_list = "";
            preg_match_all('/\<th.*?\<\/th\>/is',$tr_list[0][$th_row],$th_list);

            foreach( $th_list[0] as $key => $th ) {
                $th = strtolower(trim(preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$th)));
                $th_list[0][$key] = $th;
                if( !in_array($th, $columns)) {
                    array_push($contentRow['facts'],$th);
                }
            }
            
            
            for( $j = ($th_row+1); $j < count($tr_list[0]); $j++ ) {
    
                $td_list = "";
                preg_match_all('/\<td.*?\<\/td\>/is',$tr_list[0][$j],$td_list);

    
                for( $k = 0; $k < count($th_list[0]); $k++ ) {
    
                    $th_str = $th_list[0][$k];
                    //echo "<br>th_str:".$th_str;
                    $td_info  = $td_list[0][$k + $th_row];
                    $td_split = preg_split('/<br[^>]*>/i', $td_info);
                    if( count($td_split) > 1 ) {
    
                        $td_ary = array();
                        $rssHead = "";
                        for( $l = 0; $l < count($td_split); $l++ ) {
                            preg_match('/Resource icon.*?.png/is', $td_split[$l], $rssHead);
                                $rss = trim(str_replace('.png','',str_replace('Resource icon','',$rssHead[0])));
                            $td_str = trim(preg_replace("/\<(\/?[^\>]+)\>|\n|\t/", "",$td_split[$l]));
                            if( $td_str != "")
                            $td_ary[$l] = $rss.preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$td_str);
                        } // for l
                        $td_str = $td_ary;
                    } else {
                        $td_str = preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$td_info);
                    }
                    if( in_array($th_str, $columns) ) {
                   
                    $tableArrPiece[($j-1-$th_row)][$th_str] = $td_str;
                } else {
                    if( $tableArrPiece[($j-1-$th_row)]['figures'] == null ) {
                        $tableArrPiece[($j-1-$th_row)]['figures'] = array();
                    }
                    array_push($tableArrPiece[($j-1-$th_row)]['figures'], $td_str == "" ? null : $td_str );
                }
                } // for k

                if( $th_row == 0 ) {
                $tableArrPiece[($j-1-$th_row)]['contentId'] = $contentId;
                $tableArrPiece[($j-1-$th_row)]['id'] = $eachId++;
                }
            } // for j

          
        } // if valid table

        
        
       
        
    } // for i

    
    $tableArr = array_merge($tableArr, $tableArrPiece);
    //echo json_encode($tableAry,JSON_UNESCAPED_UNICODE);
}




?>