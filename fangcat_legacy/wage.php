<?php

require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);

$cmd = $obj->{'key0'};  // 신규, 변경, 탈퇴, 삭제, 조회, 입력, 계산
$cmd2 = $obj->{'key1'};  // 기여도, 분노, 연의임무, 퇴치임무, 제작임무, 경고, 스티커, 주급, 교환사용, 감면사용
$cmd3 = $obj->{'key6'};  // 주급 물품
$wnum =  $obj->{'key2'};  // 주차
$year =  $obj->{'key3'};  // 연도
$date = $obj->{'key4'}; // 날짜
$alliance =  $obj->{'key5'};  // 연합

$what1 = $obj->{'key7'};  // 변경내용1
$what2 = $obj->{'key8'};  // 변경내용2
$what3 = $obj->{'key9'};  // 변경내용3

$wageitems = array("보존권","은전","식량","제련도구","공예도구","은사","금사","경험열매");


if( empty($cmd)) {
    die("");
}

$table_alliance = $alliance." 연합원 ".$year;
$table_task = $alliance." 기여도 분노 ".$year;
$table_warning = $alliance." 경고 ".$year;
$table_prize = $alliance." 포상 ".$year;
$table_wp_rule = $alliance." 경고 포상 기준 ".$year;
$table_wage = $alliance." 주급 ".$year;
$table_wage_base = $alliance." 주급 기본 ".$year;
$table_wage_rule = $alliance." 기여도별수여량 ".$year;
$table_mission = $alliance." 임무 ".$year;
$table_check = $alliance." 출첵 ".$year;

$stmt = $conn->prepare("DESCRIBE `$table_alliance`");
$stmt->execute();
$finfo = $stmt->fetchAll(PDO::FETCH_COLUMN);
// 0 국명
// 1 순번
// 2 직위
// 3 연합
// 4 가입일자
// 5 탈퇴일자
// 6 경고현황
// 7 경고누적
// 8 스티커현황
// 9 스티커누적

$query = "SELECT `$finfo[0]`,`$finfo[2]`, @rownum := @rownum+1 AS RNUM FROM `지낭 연합원 2019` T1, (SELECT @rownum :=0) AS R WHERE `$finfo[3]` = '$alliance' ORDER BY `$finfo[0]` DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$memberset = array();
while ($row = $stmt->fetch()) {
    $memberset[] = $row;
}


if($cmd == "조회") {
    if( $lord == null ) {
       

        if (empty($memberset)) {
            die("NoOneHere");
        }

       // echo "[";
        foreach( $memberset as $data ) {

            echo str_pad($data[2],2,"0",STR_PAD_LEFT)." ".$data[1]." ".$data[0]."//";
           // echo "{\"$finfo[0]\":\"$data[0]\"  \"$finfo[2]\":\"$data[1]\"  \"$finfo[1]\":\"$data[2]\"}  ";

        }
       // echo "]";
    

        // 국명 연합 직위 순번



    } else {

        // 국명 연합 직위 주차주급 주차기여도 주차분노 가입일자 탈퇴일자 경고현황 스티커현황



    }
} else if( $cmd == "탈퇴" ) {
    $query = "UPDATE `$table_alliance` SET `연합` = '$what2', `탈퇴일자` = '$date', `직위` = ''  WHERE `국명` = '$what1'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    echo $what1."탈퇴";

} else if( $cmd == "신규" ) {

    $query = "SELECT * FROM `$table_alliance` WHERE `$finfo[0]` = '$what1'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dataset = array();
    while ($row = $stmt->fetch()) {
        $dataset[] = $row;
    }

    if (!empty($dataset)) {
        die("already");
    }



    $query = "INSERT INTO `$table_alliance` (`$finfo[0]`, `$finfo[2]`, `$finfo[3]`, `$finfo[4]`) VALUES ('$what1','연합원','$alliance','$date') ";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if(!$stmt) {
        die("fail_insert");
    }

    echo("succ_add ".$what1. " ".$date);
} else if( $cmd == "입력" ) {
    

    if( $cmd2 == "경고" || $cmd2 == "스티커" ) {
        // what1 : 국명, what2 : 비고, what3 : 수량
        $query = ($cmd2 == "경고")? "INSERT INTO `지낭 경고 2019` (`국명`,`주차`, `구분`, `수량`, `품목`) VALUES ('$what1','$wnum','$what2','$what3','경고')" : 
        "INSERT INTO `지낭 포상 2019` (`국명`,`주차`, `구분`, `수량`, `품목`) VALUES ('$what1','$wnum','$what2','$what3','스티커')";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        die("insert_succ". $what1);




    } else {



    $splitInfo = explode(":", $what1);
    $i = 0;
    foreach( $splitInfo as $info ) {

    if( $cmd2 == "기여도" || $cmd2 == "분노" ) {
        
       $query = "SELECT * FROM `$table_task` WHERE (`국명` = '".$memberset[$i][0]."') AND (`주차` = '$wnum')";
       $stmt = $conn->prepare($query);
       $stmt->execute();
       $dataset = array();
       while ($row = $stmt->fetch()) {
           $dataset[] = $row;
       }

    if (!empty($dataset)) {
        $query = "UPDATE `$table_task` SET `$cmd2` = '$info' WHERE (`국명` = '".$memberset[$i][0]."') AND (`주차` = '$wnum')";
    } else {
        $query = "INSERT INTO `$table_task` (`국명`, `주차`, `$cmd2` ) VALUES ('".$memberset[$i][0]."', '$wnum', '$info')";
    }

    //echo $query;
    $stmt = $conn->prepare($query);
    $stmt->execute();

    echo "insert_task".$memberset[$i][0];

 
    } else if( $cmd2 == "연의임무" || $cmd2 == "퇴치임무" || $cmd2 == "제작임무" ) {

        $query = "SELECT * FROM `$table_mission` WHERE (`국명` = '".$memberset[$i][0]."') AND (`주차` = '$wnum') AND (`구분` = '$cmd2')";
            
        $stmt = $conn->prepare($query);
$stmt->execute();
$dataset = array();
while ($row = $stmt->fetch()) {
    $dataset[] = $row;
}

if (!empty($dataset)) {
    $query = "UPDATE `$table_mission` SET `퍼센티지` = '$info' WHERE (`국명` = '".$memberset[$i][0]."') AND (`주차` = '$wnum') AND (`구분` = '$cmd2')";
} else {
    $query = "INSERT INTO `$table_mission` ( `국명`, `구분`, `주차`, `퍼센티지` ) VALUES ('".$memberset[$i][0]."','$cmd2' ,'$wnum', '$info')";
}
$stmt = $conn->prepare($query);
$stmt->execute();

echo "insert_mission".$memberset[$i][0];

    } else if( $cmd2 == "출첵" ) {
        $query = "SELECT * FROM `$table_check` WHERE (`국명` = '".$memberset[$i][0]."') AND (`주차` = '$wnum')";
            
        $stmt = $conn->prepare($query);
$stmt->execute();
$dataset = array();
while ($row = $stmt->fetch()) {
    $dataset[] = $row;
}

if (!empty($dataset)) {
    $query = "UPDATE `$table_check` SET `출첵` = '$info' WHERE (`국명` = '".$memberset[$i][0]."') AND (`주차` = '$wnum')";
} else {
    $query = "INSERT INTO `$table_check` ( `국명`, `주차`, `출첵` ) VALUES ('".$memberset[$i][0]."', '$wnum', '$info')";
}
$stmt = $conn->prepare($query);
$stmt->execute();

echo "insert_check".$memberset[$i][0];

    } else if( $cmd2 == "교환사용" || $cmd2 == "감면사용" ) {

        $query = "SELECT * FROM `지낭 포상 2019` WHERE (`국명`='".$memberset[$i][0]."') AND (`주차`='$wnum') AND (`구분`='$cmd2')";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $dataset = array();
        while ($row = $stmt->fetch()) {
            $dataset[] = $row;
        } 

        $query = "SELECT * FROM `지낭 스티커 교환비 2019` WHERE (`주차`='$wnum')";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $dataset = array();
        $row = $stmt->fetch();

        $change_ratio = $row[1];
        $reduce_ratio = $row[2];

        $change_value = (-1)* $info;
        $change_by_ratio_value = ($cmd2 == "교환사용")? $info * $change_ratio : (int)((double)$change_value * $reduce_ratio);




        if( $info > 0) {

        if (!empty($dataset)) {
            $query = "UPDATE `지낭 포상 2019` SET `수량` = '$change_value' WHERE (`국명`='".$memberset[$i][0]."') AND (`주차`='$wnum') AND (`구분`='$cmd2') AND  (`품목` = '스티커')";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            $query = ($cmd2 == "교환사용")? "UPDATE `지낭 포상 2019` SET `수량` = '$change_by_ratio_value' WHERE (`국명`='".$memberset[$i][0]."') AND (`주차`='$wnum') AND (`구분`='$cmd2') AND  (`품목` = '보존권')" :
            "UPDATE `지낭 경고 2019` SET `수량` = '$change_by_ratio_value' WHERE (`국명`='".$memberset[$i][0]."') AND (`주차`='$wnum') AND (`구분`='$cmd2') AND  (`품목` = '경고')";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            
        
        } else {
            $query = "INSERT INTO `지낭 포상 2019` ( `국명`, `주차`,`구분`, `수량`,`품목` ) VALUES ('".$memberset[$i][0]."', '$wnum','$cmd2', '$change_value', '스티커')";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $query = ($cmd2 == "교환사용")? "INSERT INTO `지낭 포상 2019` ( `국명`, `주차`,`구분`, `수량`,`품목` ) VALUES ('".$memberset[$i][0]."', '$wnum','$cmd2', '$change_by_ratio_value', '보존권')" :
            "INSERT INTO `지낭 경고 2019` ( `국명`, `주차`,`구분`, `수량`,`품목` ) VALUES ('".$memberset[$i][0]."', '$wnum','$cmd2', '$change_by_ratio_value', '경고')";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            
        }

        echo "change".$memberset[$i][0];

    }





    }
    $i++;
}

}

} else if( $cmd == "계산" ) {

  


    foreach( $memberset as $data ) {



                $query = "SELECT * FROM `$table_task` WHERE (`국명` = '".$data[0]."') AND (`주차` = $wnum)";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $taskset = array();
                while ($row = $stmt->fetch()) {
                  $taskset[] = $row;
                }
                // 2 기여도
                // 3 분노

                if(empty($taskset)) {
                    die("Unprepared_task");
                }


    
                

                


        if( $cmd2 == "경고" || $cmd2 == "포상" ) {

            $cummulated_value = ($cmd2 == "포상")? "스티커" : $cmd2; 
            $cummulated_name = ($cmd2 == "포상")? "스티커누적" : "경고누적"; 
            $table_tmp = $alliance. " " .$cmd2. " ". $year;
            $query = "SELECT * FROM `$table_wp_rule` WHERE `구분1` = '$cmd2'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $ruleset = array();
            while ($row = $stmt->fetch()) {
               $ruleset[] = $row;
            }
            // 0 구분1
            // 1 구분2
            // 2 기준이상
            // 3 기준이하
            // 4 등수이내
            // 5 품목
            // 6 수량

            // rule0 = 경고 or 포상 = cmd2 = 구분1
            // rule1 = 분노 or 기여도 or 연의임무 or 퇴치임무 or 제작임무 or 

            foreach( $ruleset as $rule ) {

                
                

                    if( $rule[1] == "분노" || $rule[1] == "기여도" || $rule[1] == "연의임무" || $rule[1] == "퇴치임무" || $rule[1] == "제작임무" ) {

                        $taskvalue;
                        $taskname;
                        $table_tmp_task;

                        if( $rule[1] == "분노" || $rule[1] == "기여도" ) {

                            $taskvalue = $taskset[0][$rule[1]];
                            $taskname = $rule[1];
                            $table_tmp_task = $table_task;

                        } else {

                            $query = "SELECT * FROM `$table_mission` WHERE (`국명` = '".$data[0]."') AND (`주차` = $wnum) AND (`구분` = '".$rule[1]."')";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $missionset = array();
                            while ($row = $stmt->fetch()) {
                              $missionset[] = $row;
                            }
                            // 2 기여도
                            // 3 분노
            
                            //if(empty($missionset)) {
                           //     die("Unprepared_mission");
                           // }








                            $taskvalue = $missionset[0][3];
                            $taskname = "퍼센티지";
                            $table_tmp_task = $table_mission;
                        }

                    
                        $warning_already = false;

                        $query = "SELECT * FROM `$table_tmp` WHERE (`국명` = '".$data[0]."') AND (`주차` = '$wnum') AND (`구분` = '".$rule[1]."') AND (`품목` = '".$rule[5]."')";

                       // die($query);
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        $warnset = array();
                        while ($row = $stmt->fetch()) {
                          $warnset[] = $row;
                        }
                        // 2 구분
                        // 3 수량
                        // 4 품목
        
                        if(!empty($warnset)) {
                            $warning_already = true;
                        }        
        

                        if( $rule[4] == 0 ) {


                        if( $taskvalue  >= $rule[2] && $taskvalue  <= $rule[3] ) {

                            $query = "SELECT * FROM `$table_check` WHERE (`국명` = '".$data[0]."') AND (`주차` = '$wnum')";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $row = $stmt->fetch();


                            if( empty($row) ) {
                                die( "Unprepared_check" );
                            }

                            if( !empty($row[2]) ) {
                                echo(  $data[0]." / ". $rule[1]. ":" .$taskvalue . " / ". $rule[5]. ":" . $rule[6]. "//");

                                $query = $warning_already? "UPDATE `$table_tmp` SET `수량` = '".$rule[6]."' WHERE (`국명` = '".$data[0]."') AND (`주차` = '$wnum') AND (`구분` = '".$rule[1]."') AND (`품목` = '".$rule[5]."')" :  
                                "INSERT INTO `$table_tmp` (`국명`, `주차`, `구분`, `수량`, `품목`) VALUES ('".$data[0]."','$wnum','".$rule[1]."','".$rule[6]."', '".$rule[5]."')";
    
                                //die( $query );
                                $stmt = $conn->prepare($query);
                                $stmt->execute();
                            }


                           
                            
                        }
                    } else if ($rule[4] >0 ) {

                        $query = "SELECT t.`국명`, (SELECT COUNT(*) FROM `$table_tmp_task` WHERE (`$taskname` >= t.`$taskname`) AND (`구분` = '".$rule[1]."')) AS ranking, t.`$taskname` FROM `$table_tmp_task` t WHERE (t.`국명` = '".$data[0]."') AND (`구분` = '".$rule[1]."')";
                        //die($query);
                        
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        $row = $stmt->fetch();

                        if( $row[1] <= $rule[4] ) {
                            echo(  "!!".$data[0]." / ". $row[1]. "<=" .$rule[4] . " / ". $rule[5]. ":" . $rule[6]. "//");


                            $query = $warning_already? "UPDATE `$table_tmp` SET `수량` = '".$rule[6]."' WHERE (`국명` = '".$data[0]."') AND (`주차` = '$wnum') AND (`구분` = '".$rule[1]."') AND (`품목` = '".$rule[5]."')" :  
                            "INSERT INTO `$table_tmp` (`국명`, `주차`, `구분`, `수량`, `품목`) VALUES ('".$data[0]."','$wnum','".$rule[1]."','".$rule[6]."', '".$rule[5]."')";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();



                        }
                     
                        

                    }

                    }


                }

                $query = "SELECT SUM(`수량`) FROM `$table_tmp` WHERE (`국명` = '".$data[0]."') AND (`품목` = '".$cummulated_value."')";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch();



                $query = "UPDATE `$table_alliance` SET `$cummulated_name`='" . $row[0] . "' WHERE `국명` = '" . $data[0] . "'";
               // die($query);
                $stmt = $conn->prepare($query);
                $stmt->execute();




            } else if ( $cmd2 == "주급" ) {


                $wnum2 = ($wnum+1);


                foreach( $wageitems as $item) {

                $query = "SELECT * FROM `$table_check` WHERE (`국명` = '".$data[0]."') AND (`주차` = '$wnum')";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $row = $stmt->fetch();


                            if( empty($row[2])) {
                                echo "--- ";
                            } else {


                

                $str = "";

                

                $query = "SELECT `$item` FROM `지낭 주급 기본 2019` WHERE (`주차` = '$wnum2')";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $wageset = array();
                $row = $stmt->fetch();
                $sum0 = $row[0];

                //$str.= "기본(".$row[0].") + ";


                $query = "SELECT `구분`, `수량`, SUM(`수량`) FROM `지낭 포상 2019` WHERE (`국명` = '" . $data[0] . "') AND (`품목` = '$item') AND (`주차` = '$wnum')";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $wageset = array();
                while ($row = $stmt->fetch()) {
                  $wageset[] = $row;
                }

                

              

                //foreach($wageset as $wage) {
                 //   $str.= ($wage[1]!= 0)?$wage[0]."(".$wage[1].") +": "";
                //}
            
                

                $sum1 = $wageset[0][2];

                
                $query = "SELECT `구분`, `수량`, SUM(`수량`) FROM `지낭 주급 2019` WHERE (`국명` = '" . $data[0] . "') AND (`품목` = '$item') AND (`주차` = '$wnum2')";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $wageset = array();
                while ($row = $stmt->fetch()) {
                  $wageset[] = $row;
                }


               // foreach($wageset as $wage) {
                //    $str.= ($wage[1]!= 0)?$wage[0]."(".$wage[1].") +": "";
               // }

                $sum2 = $wageset[0][2];


                $query = "SELECT SUM(`기여도`) FROM `지낭 기여도 분노 2019` WHERE (`국명` = '" . $data[0] . "') AND (`주차` <= '$wnum2')";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch();
                $task_next_week = $row[0] + ($wnum2+1) * 7000;
                $sum_without_adj = ($sum0 + $sum1 + $sum2);




                $query = "SELECT `수량` FROM `지낭 기여도별수여량 2019` WHERE ('$task_next_week' >= `기여도이상`) AND (`품목` = '$item') ORDER BY ABS( '$task_next_week' - `기여도이상` )";
                //die($query);
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch();
                $val1 = $row[0];


                $query = "SELECT `수량` FROM `지낭 기여도별수여량 2019` WHERE ('$task_next_week' >= `기여도이상`) AND ('$sum_without_adj' <= `수량`) AND (`품목` = '$item') ORDER BY ABS( '$sum_without_adj' - `수량` )";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch();
                $val2 = $row[0];





                $adj1 = $val1-$sum_without_adj;
                $adj2 = $val2-$sum_without_adj;


                $adj = (abs($adj1)<abs($adj2))? $adj1 : $adj2;
               



                $wnum3 = ($wnum2 + 1);


                if( $adj != 0 ) {
                    $minusadj = (-1)*$adj;
                $query = "SELECT * FROM `지낭 주급 2019`WHERE (`국명` = '" . $data[0] . "') AND (`품목` = '$item') AND (`주차` = '$wnum3') AND (`구분` = '이월')";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $wageset = array();
                while ($row = $stmt->fetch()) {
                  $wageset[] = $row;
                }

                $query = (!empty($wageset))? "UPDATE `지낭 주급 2019` SET `수량` = '$minusadj' WHERE (`국명` = '" . $data[0] . "') AND (`품목` = '$item') AND (`주차` = '$wnum3') AND (`구분` = '이월')":
                "INSERT INTO `지낭 주급 2019` (`국명`, `주차`, `품목`, `구분`, `수량`) VALUES ('" . $data[0] . "', '$wnum3', '$item', '이월', '$minusadj' )";
                $stmt = $conn->prepare($query);
                $stmt->execute();

                }
                //$str = substr($str,0,-1);
                //$str .= " + ". $adj;        
                //$str .= " = ";
                $str .= str_pad(($sum_without_adj + $adj),3,"0",STR_PAD_LEFT);
                //$str .= " [".$task_next_week."] ";
                
                $str .= " ";









                echo $str;

            }

            




            }

            echo ": ".$data[0]."//";











        }

        //echo "suckckc".$data[0];
    }
}



?> 
