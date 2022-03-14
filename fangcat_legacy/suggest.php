<?php

require_once 'DBConfig.php';

$json = $_POST['json'];
$obj = json_decode($json);


$cmd = $obj->key0;  // 건의, 조회
$name = $obj->key1; // 닉네임
$sugg = $obj->key2; // 건의사항
$date = $obj->key3; // 년월일
$time = $obj->key4; // 시분초


$suggT = "`건의사항`";

if ($cmd == "건의") {
    $query = "INSERT INTO $suggT (`닉네임`,`건의사항`,`날짜`,`시간`) VALUES ('$name','$sugg','$date','$time')";
    //die($query);
    $stmt = $conn->prepare($query);
    
    if($stmt->execute()) {
        echo "$date 건의 등록 성공crlfcrlf";
        echo "$time > $sugg";


    } else {

        
         $query = "SELECT * FROM $suggT WHERE `닉네임` = '$name' AND `날짜` = '$date'";
         $stmt = $conn->prepare($query);
         $stmt->execute();
         $row = $stmt->fetch();
         echo "!주의 : 해당 날짜($date) 이전 건의($row[3])가 삭제됩니다.crlfcrlf";
         echo "$row[3] > $row[1]crlfcrlf";



        $query = "UPDATE $suggT SET `건의사항`='$sugg',`시간`='$time' WHERE `닉네임` = '$name' AND `날짜` = '$date'";
        $stmt = $conn->prepare($query);
        if($stmt->execute() ) {
            echo("$date 건의 갱신 성공crlfcrlf");
            echo "$time > $sugg";


        } else {
            die ("$date 건의 등록/갱신 실패");
        }

    }

   
} else if ($cmd == "조회") {

  if($name == "") {
    echo "$date 일자 건의목록crlfcrlf";

    $query = "SELECT * FROM $suggT WHERE `날짜` = $date";
    $stmt = $conn->prepare($query);

   

    if( $stmt->execute() ) {
        while ($row = $stmt->fetch()) {
           echo "$row[0] > $row[1]crlf";
       }
    } else {
        die("조회 실패");
    }

  } else {
    echo "$name 님 건의목록crlfcrlf";
    $query = "SELECT * FROM $suggT WHERE `닉네임` = '$name'";
    $stmt = $conn->prepare($query);

    if( $stmt->execute() ) {
        while ($row = $stmt->fetch()) {
           echo "$row[2] > $row[1]crlf";
       }
    } else {
        die("조회 실패");
    }
    
      
  }

} else {

}



?>