<?php
class KeyStore {
    private $encodedKey = null;

    public function __construct() {
        $result = shell_exec( "java -jar readKey.jar" );
        //echo("cla: ".$encodedKey."<br>");
        if( !isset($result) || $result ==null) {
            $returnJson->status= "fail";
            $returnJson->message= "unprepared secure server";
            die(json_encode($returnJson,JSON_UNESCAPED_UNICODE));
        }
        $this->encodedKey = $result;
    }

    public function getKey() {
        return base64_decode($this->encodedKey);
    }
}
?>