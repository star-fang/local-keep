<?php
class CheckTableList{

    
    const COLUMN_LAST_MODIFIED = 'lastModified';
    private $clientTableList;
    private $dbName;

    public function __construct( $tableListJson,  $dbName ) {
        if( $tableListJson != null ) {
            $this->clientTableList = json_decode($tableListJson);
        } else {
            $this->clientTableList = null;
        }

        $this->dbName = $dbName;

        //echo '<br>CheckTableList constructed<br>';
    }

    public function execute( $db ) {
        $rows = $db->searchRowsByQuery('SHOW TABLES');
        $count = count($rows);
        $tableList = array();
        for($i = 0; $i < $count; $i++) {
            $tableName = $rows[$i]["Tables_in_".$this->dbName];

            $lastModifedMillis = 0;
            if( $this->clientTableList != null ) {
            foreach( $this->clientTableList as $clientTable) {
                if( $clientTable->table == $tableName) {
                   $lastModifedMillis = $clientTable->lastModified;
                    break 1;
                }
            } // foreach clientTableList
        }

            $tableInfo = $this->getTableInfo($this->dbName, $tableName, $db, $lastModifedMillis);
            if( $tableInfo->count > 0)
            array_push( $tableList, $tableInfo );
        }

        
        $returnJson->message = "check table list successful";
        $returnJson->tables = $tableList;

        return $returnJson;
    }

    private function getTableInfo($db_name, $table_name, $db, $lastModifedMillis) {
        $lastModifed = $lastModifedMillis / 1000;
        $tableInfo->table = $table_name;
        $lm = self::COLUMN_LAST_MODIFIED;
        $sql = "SELECT COUNT(*) AS c FROM `$table_name` WHERE UNIX_TIMESTAMP(`$lm`) > $lastModifed";
        $rows = $db->searchRowsByQuery($sql);
        $count = $rows[0]['c'];
        $tableInfo->count = $count;
        if($count > 0 ) {
            $rows = $db->searchRowsByQuery("SELECT `AVG_ROW_LENGTH` AS b FROM information_schema.tables WHERE `TABLE_SCHEMA` = '$db_name' AND `TABLE_NAME` = '$table_name' ");
            $tableInfo->size = $count * $rows[0]['b'];
        } else {
            $tableInfo->size = 0;
        }
        //var_dump($tableInfo);
        //die();
        

        return $tableInfo;
    }


}





    
    
    
    

    




?>