<?php
    function dyingMessage($status, $message) {
       $dyingJson->status= $status;
       $dyingJson->message= $message;
       die(json_encode($dyingJson,JSON_UNESCAPED_UNICODE));
    }
?>