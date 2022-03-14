<?php
class DownloadJson {

    private $tableName;
    private $lastModifiedTime;
    public function __construct($tableName, $lastModifiedTime) {
        $this->tableName = $tableName;
        $this->lastModifiedTime = $lastModifiedTime;
    }

    public function execute( $db ) {
        $table = $this->tableName;
        $lastModified = ($this->lastModifiedTime)/1000;

        $fInfoRows = $db->searchRowsByQuery("DESCRIBE `".$table."`");

        $fInfo = array();
        foreach( $fInfoRows as $fInfoRow ) {
            $field = $fInfoRow['Field'];
            $fInfo[] = $field;
        }
        
        $sql = "SELECT * FROM `$table` WHERE UNIX_TIMESTAMP(`lastModified`) > '$lastModified'";
        $rows = $db->searchRowsByQuery($sql);
        
        $jsonAry = array();
        $maxLm = 0;
        for( $i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $eachAry = array();
            foreach($fInfo as $info) {
              $data = $row[$info];
              if($info == 'lastModified') {
                if( $maxLm < $data ) {
                  $maxLm = strtotime($data);
                }
              } else {
                if( $data == null ) {
                  $eachAry[$info] = null;
                } else {
                  $data = trim($data);
                  if( $data[0] == '{' ) {
                    $decodedArray = json_decode($data, true);
                    if( $decodedArray ) {
                      //{"2000001": 11,  "2000064": 61, ....}
                      //[{20000:11}, {22414:42}, ....]
                      $convertedArr = array();
                      foreach($decodedArray as $key => $val) {
                        $convertedArr[] = array($key=>$val);
                      }
                      $eachAry[$info] = $convertedArr;
                    } else {
                      if( $data == '{}') {
                        $eachAry[$info] = null;
                      } else {
                        $eachAry[$info] = $data;
                      }
                    } // if..else decode success
                } else if( $data[0] == '[' ) {
                  $decoded = json_decode($data, true);
                  if( $decoded) {
                    $eachAry[$info] = $decoded;
                  } else {
                    if( $data == '[]' ) {
                      $eachAry[$info] = null;
                    } else {
                      $eachAry[$info] = $data;
                    }
                  }
                } else {
                  $eachAry[$info] = $data;
                } // if..else data type is jsonObject or jsonArray
               } // if..else data != null
              } // if...else column != lastModified
            }
            //var_dump($eachAry);
          array_push($jsonAry, $eachAry);
        }
        
        $returnJson->lastModified = $maxLm;
        $returnJson->message = "download json now!";
        $returnJson->tuples = $jsonAry;
        return $returnJson;
    }


}

 
?> 
