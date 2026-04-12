<?php
    $user = SESSION->user;
	$domain = CONFIG->domain;
	
    if($user != null) {
        echo "http://$domain/Login/Negotiate.ashx?suggest=".base64_encode($user->security_key);
    } else {
        die(http_response_code(401));
    }
?>