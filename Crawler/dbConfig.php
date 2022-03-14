<?php

class DB_Connection {

    const DB_HOST = 'localhost';
    const DB_USER = 'root';

    /**
     * PDO instance
     * @var PDO 
     */
    private $pdo = null;

    public function getPdo() {
        return  $this->pdo;
    }

    /**
     * Open the database connection
     */
    public function __construct($db, $pass) {
        // open database connection
        //echo "<br>open database connection<br>";
        $conStr = sprintf("mysql:host=%s;dbname=$db;charset=utf8", self::DB_HOST);

        try {
            $this->pdo = new PDO($conStr, self::DB_USER, $pass);
            //for prior PHP 5.3.6
            //$conn->exec("set names utf8");
        } catch (PDOException $e) {
            $returnJson->status= "fail";
            $returnJson->message= $e->getMessage();
            die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * insert blob into the table
     * @param byte[] $blob
     * @param string $table : table name
     * @param string $column : column name
     * @return int $id : last insert id
     */
    public function insertBlob($blob, $table, $column) {
        //$blob = fopen($filePath, 'rb');

        $sql = "INSERT INTO $table($column) VALUES(:data)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':data', $blob, PDO::PARAM_LOB);

        $succ = $stmt->execute();

        if( $succ ) {
            $id = $this->pdo->lastInsertId();
        } else {
            die( "<br>insertBlob fail message: ".$stmt->errorInfo()[2]."<br>");
        }


        return $id;
    }

    public function insertBlobAndRow($blob, $rowStr,  $table, $BlobColumn, $columnStr) {


        
        $sql = "INSERT INTO $table($BlobColumn, $columnStr) VALUES(:data,  '$rowStr')";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':data', $blob, PDO::PARAM_LOB);

        $succ = $stmt->execute();

        if( $succ ) {
            $id = $this->pdo->lastInsertId();
        } else {
            die( "<br>insertBlobAndRow fail message: ".$stmt->errorInfo()[2]."<br>");
        }


        return $id;
    }

    public function insertRow($table, $rowStr, $columnStr) {

        $sql = "INSERT INTO `$table`($columnStr) VALUES($rowStr)";
        //die($sql);
        $stmt = $this->pdo->prepare($sql);

        $succ = $stmt->execute();

        $result = array();
        if( $succ ) {
            $id = $this->pdo->lastInsertId();
            $result['status'] = 'succ';
            $result['id'] = $id;
        } else {
            //die( "<br>insertBlobAndRow fail message: ".$stmt->errorInfo()[2]."<br>");
            $result['status'] = 'fail';
            $result['message'] = $stmt->errorInfo()[2];
        }


        return $result;
    }


    /**
     * update the column with the new blob
     * @param int $id
     * @param byte[] $blob
     * @param string $table
     * @param string $column
     * @return bool
     */
    function updateBlob($id, $blob, $table, $column) {

        $sql = "UPDATE $table SET $column = :data WHERE `id` = '$id';";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':data', $blob, PDO::PARAM_LOB);

        $succ = $stmt->execute();

        if( !$succ) {
            die("<br>updateBlob fail message: ".$stmt->errorInfo()[2]."<br>");
        }


        return $succ;
    }

    function updateRow($id, $table, $setStr) {

        $sql = "UPDATE $table SET $setStr WHERE `id` = '$id';";

        $stmt = $this->pdo->prepare($sql);

        $succ = $stmt->execute();

        if( !$succ) {
            die("<br>updateRow fail message: ".$stmt->errorInfo()[2]."<br>");
        }

        return $succ;
    }

    /**
     * select data from the the files
     * @param int $id
     * @param string $table
     * @param string $column
     * @return byte[]
     */
    public function selectBlob($id, $table, $column) {

        $sql = "SELECT $column FROM $table WHERE `id` = '$id';";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stmt->bindColumn($column, $data, PDO::PARAM_LOB);

        $stmt->fetch(PDO::FETCH_BOUND);

        return $data;
    }

    public function selectRow($id, $table, $columnStr) {

        $sql = "SELECT $columnStr FROM $table WHERE `id` = '$id';";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchRows($searchColumn, $searchValue, $table, $columnStr) {

        $sql = "SELECT $columnStr FROM $table WHERE `$searchColumn` = '$searchValue';";
        //echo $sql."<br>";
        $stmt = $this->pdo->prepare($sql);
        if(!$stmt->execute()) {
            die("<br>searchRows fail message: ".$stmt->errorInfo()[2]."<br>");
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchRowsByQuery($sql) {

        $stmt = $this->pdo->prepare($sql);
        if(!$stmt->execute()) {
            die("<br>searchRowsByQuery fail message: ".$stmt->errorInfo()[2]."<br>");
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * close the database connection
     */
    public function __destruct() {
        // close the database connection
        $this->pdo = null;
    }

}

/*
$dbObj = new DB_Connection('fangcat_users');
require_once './encryption.php';
$newIv = PHP_AES_Cipher::genSecureIv();
echo "iv generated:";
echo base64_encode($newIv)."<br>";
$lastInsertId = $dbObj->insertBlob($newIv,'login_transaction','iv');
echo "<br>id: ".$lastInsertId."<br>";

$checkIv = $dbObj->selectBlob($lastInsertId, 'login_transaction','iv');
echo "<br>insertedIv: ".base64_encode($checkIv)."<br>";
*/
