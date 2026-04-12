<?php 
    $user = SESSION->user;

    if($user != null) {
        echo strval($user->id);
    } else {
        echo "1";
    }
?>