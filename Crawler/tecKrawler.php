<?php
ini_set('max_execution_time', '-1');
include_once 'Snoopy.class.php';

$contents = '<h3>Tier 1</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Military Discipline}}
}}
<h3>Tier 2</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Iron Working}}
    {{BlockGalleryItemTech|Improved Fletching}}
    {{BlockGalleryItemTech|Horsemanship}}
    {{BlockGalleryItemTech|Flaming Projectile}}
}}
<h3>Tier 3</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Swordsman}}
    {{BlockGalleryItemTech|Bowman}}
    {{BlockGalleryItemTech|Light Cavalry}}
    {{BlockGalleryItemTech|Arcuballista}}
}}
<h3>Tier 4</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Tracking}}
    {{BlockGalleryItemTech|Pathfinding}}
}}
<h3>Tier 5</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Buckler}}
    {{BlockGalleryItemTech|Leather Armor}}
    {{BlockGalleryItemTech|Scale Armor}}
    {{BlockGalleryItemTech|Enhanced Axle}}
}}
<h3>Tier 6</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Spearman}}
    {{BlockGalleryItemTech|Composite Bowman}}
    {{BlockGalleryItemTech|Heavy Cavalry}}
    {{BlockGalleryItemTech|Mangonel}}
}}
<h3>Tier 7</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Camouflage}}
}}
<h3>Tier 8</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Combat Tactics}}
    {{BlockGalleryItemTech|Defensive Formation}}
    {{BlockGalleryItemTech|Herbal Medicine}}
}}
<h3>Tier 9</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Cartography}}
}}
<h3>Tier 10</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Long Swordsman}}
    {{BlockGalleryItemTech|Crossbowman}}
    {{BlockGalleryItemTech|Knight}}
    {{BlockGalleryItemTech|Ballista}}
    {{BlockGalleryItemTech|Legionary|category=Long Swordsman|civ=Rome}}
    {{BlockGalleryItemTech|Teutonic Knight|category=Knight|civ=Germany}}
    {{BlockGalleryItemTech|Longbowman|category=Crossbowman|civ=Britain}}
    {{BlockGalleryItemTech|Throwing Axeman|category=Long Swordsman|civ=France}}
    {{BlockGalleryItemTech|Conquistador|category=Knight|civ=Spain}}
    {{BlockGalleryItemTech|Chu-Ko-Nu|category=Crossbowman|civ=China}}
    {{BlockGalleryItemTech|Samurai|category=Long Swordsman|civ=Japan}}
    {{BlockGalleryItemTech|Hwarang|category=Crossbowman|civ=Korea}}
}}
<h3>Tier 10</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Wootz Steel}}
    {{BlockGalleryItemTech|Bodkin Arrows}}
    {{BlockGalleryItemTech|Stirrups}}
    {{BlockGalleryItemTech|Ballistics}}
}}
<h3>Tier 11</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Scutum}}
    {{BlockGalleryItemTech|Pavise}}
    {{BlockGalleryItemTech|Plate Armor}}
    {{BlockGalleryItemTech|Heavy Frame}}
}}
<h3>Tier 12</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Medical Corps}}
}}
<h3>Tier 13</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Combined Arms}}
    {{BlockGalleryItemTech|Encampment}}
}}
<h3>Tier 14</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Royal Guard}}
    {{BlockGalleryItemTech|Royal Crossbowman}}
    {{BlockGalleryItemTech|Royal Knight}}
    {{BlockGalleryItemTech|Trebuchet}}
    {{BlockGalleryItemTech|Elite Legionary|category=Royal Guard|civ=Rome}}
    {{BlockGalleryItemTech|Elite Teutonic Knight|category=Royal Knight|civ=Germany}}
    {{BlockGalleryItemTech|Elite Longbowman|category=Royal Crossbowman|civ=Britain}}
    {{BlockGalleryItemTech|Elite Throwing Axeman|category=Royal Guard|civ=France}}
    {{BlockGalleryItemTech|Elite Conquistador|category=Royal Knight|civ=Spain}}
    {{BlockGalleryItemTech|Elite Chu-Ko-Nu|category=Royal Crossbowman|civ=China}}
    {{BlockGalleryItemTech|Elite Samurai|category=Royal Guard|civ=Japan}}
    {{BlockGalleryItemTech|Elite Hwarang|category=Royal Crossbowman|civ=Korea}}
}}|
';

$contents_eco = '<h3>Tier 1</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Quarrying}}
}}
<h3>Tier 2</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Irrigation}}
    {{BlockGalleryItemTech|Handsaw}}
}}
<h3>Tier 3</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Sickle}}
    {{BlockGalleryItemTech|Masonry}}
    {{BlockGalleryItemTech|Handaxe}}
}}
<h3>Tier 4</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Metallurgy}}
}}
<h3>Tier 5</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Chisel}}
    {{BlockGalleryItemTech|Writing}}
    {{BlockGalleryItemTech|Metalworking}}
}}
<h3>Tier 6</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Handcart}}
    {{BlockGalleryItemTech|Multilayer Structure}}
    {{BlockGalleryItemTech|Placer Mining}}
}}
<h3>Tier 7</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Wheel}}
    {{BlockGalleryItemTech|Jewelry}}
}}
<h3>Tier 8</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Plow}}
    {{BlockGalleryItemTech|Sawmill}}
}}
<h3>Tier 9</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Scythe}}
    {{BlockGalleryItemTech|Engineering}}
    {{BlockGalleryItemTech|Whipsaw}}
}}
<h3>Tier 10</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Mathematics}}
}}
<h3>Tier 11</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Open-pit Quarry}}
    {{BlockGalleryItemTech|Coinage}}
}}
<h3>Tier 12</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Stone Saw}}
    {{BlockGalleryItemTech|Machinery}}
    {{BlockGalleryItemTech|Shaft Mining}}
}}
<h3>Tier 13</h3>
{{BlockGallery|
    {{BlockGalleryItemTech|Carriage}}
    {{BlockGalleryItemTech|Cutting & Polishing}}
}}|';
$contentAry = array();
$tableAry = array();
$eachId = 300000;

for( $idValue = 0, $tier = 1; $tier <= 14; $tier++ ) {
    $part = "";
    $h3_head_start = "<h3>Tier ".$tier."</h3>";
    $start_pos = strpos($contents, $h3_head_start);
    $h3_head_end = "<h3>Tier ".($tier+1)."</h3>";
    $end_pos = strpos($contents, $h3_head_end);
    $end_pos = $end_pos > 0? $end_pos : strlen($contents);
    //echo $end_pos."<br>";
    
    $content_detail =  substr($contents, $start_pos + strlen($h3_head_start), $end_pos - $start_pos - strlen($h3_head_end) )."<br>";

    //echo $content_detail."<br>";
    $tech_list = "";
    preg_match_all('/\{BlockGalleryItemTech\|.*?\}/', $content_detail , $tech_list);

    //print_r($tech_list[0]."<br><br><br>");

    //echo "tier".$tier.": ";

   //count($tech_list[0])


   

    for($techIndex = 0; $techIndex < count($tech_list[0]); $techIndex++ ) {
        $name = preg_replace('/\s+/',"_", trim(preg_replace('/\{BlockGalleryItemTech\||\}/',"", $tech_list[0][$techIndex])));
        $name = explode('|',$name)[0];
        $contentRow = array();
        $contentId = ($idValue++) + 900000;
        $contentRow['id'] = $contentId;
        $contentRow['category'] = "military";
        $contentRow['nameEng'] = $name;
        $contentRow['name'] = "";
        $contentRow['tier'] = $tier;

        array_push($contentAry, $contentRow);

        crawlingTech( $tableAry, $name, $contentId, $eachId );

        sleep(1);
        //echo $name."   ";
    }

    //echo "<br>";
}


echo json_encode($tableAry,JSON_UNESCAPED_UNICODE);
echo "<br><br>-------------------------------<br>".json_encode($contentAry,JSON_UNESCAPED_UNICODE);



//die();

function crawlingTech( &$tableArr, $name, $contentId, &$eachId) {


    $tableArrPiece = array();

    $snoopy = new snoopy;

    $snoopy->fetch("https://riseofkingdoms.fandom.com/wiki/Technology/".$name);
    $txt=$snoopy->results;
    
    
    $tables = "";
    preg_match_all('/\<table.*?\<\/table\>/is',$txt,$tables);

    for( $i = 0; $i < count($tables[0]); $i++ ) {
        $className = "";
        preg_match_all('/class=".*?"/is',$tables[0][$i],$className);
        $th_row;
        switch( str_replace('class="', '',$className[0][0]) ) {
            case 'article-table"':
            case 'article-table tech-table"':
                $th_row = 0;
            break;
            case 'article-table mw-collapsible"':
                $th_row = 1;
            break;
            default:
            $th_row = -1;
    
        }
    
        
        if( $th_row > -1 ) {
    
            $tr_list = "";
            preg_match_all('/\<tr.*?\<\/tr\>/is',$tables[0][$i],$tr_list);
            //print_r($tr_list[0]);
    
            $th_list = "";
            preg_match_all('/\<th.*?\<\/th\>/is',$tr_list[0][$th_row],$th_list);
    
            
            for( $j = ($th_row+1); $j < count($tr_list[0]); $j++ ) {
    
                $td_list = "";
                preg_match_all('/\<td.*?\<\/td\>/is',$tr_list[0][$j],$td_list);
    
                for( $k = 0; $k < count($th_list[0]); $k++ ) {
    
                    $th_str = trim(preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$th_list[0][$k]));
    
                    //echo  $td_list[0][$k]."<br>";

                    $td_info  = $td_list[0][$k + $th_row];
                    $td_split = preg_split('/<br[^>]*>/i', $td_info);
                    //$td_str = "";
                    if( count($td_split) > 1 ) {
    
                        $td_ary = array();
                        $rss = array();
                        for( $l = 0; $l < count($td_split); $l++ ) {
    
                            if( $th_str == "Costs") {
                                preg_match('/Resource icon.*?.png/is', $td_split[$l], $rss);
                                $rss[0] = trim(str_replace('.png','',str_replace('Resource icon','',$rss[0])));
                                //echo $rss[0],$rss[1],$rss[2],$rss[3]."<br>";
                            }
    
    
                            $td_str = trim(preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$td_split[$l]));
                            if( $td_str != "")
                            $td_ary[$l] = $rss[0].preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$td_str);
                        } // for l
                        $td_str = json_encode($td_ary,JSON_UNESCAPED_UNICODE);
                        //$td_str = "[".implode(",", $td_ary)."]";
                       
    
                    } else {
                        $td_str = preg_replace("/\<(\/?[^\>]+)\>|\n/", "",$td_info);
                    }
                    //echo $td_str."<br>";
                    
    
                    $tableArrPiece[($j-1-$th_row)][$th_str] = $td_str;
                    
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