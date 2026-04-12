<?php
    
	use anorrl\utilities\UserUtils;

	$user = SESSION->user;

    if($user == null && isset($_GET['suggest'])) {
        $key = base64_decode($_GET['suggest']);

        UserUtils::SetCookies($key);
    }
?>