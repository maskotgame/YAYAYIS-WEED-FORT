<?php
	use anorrl\utilities\Thumbnail;
	
	header("Content-Type: text/plain");

	if(!isset($hash))
		die(http_response_code(500));

	$data = Thumbnail::Get3DObj($hash, false);

	if(!$data)
		die(http_response_code(500));

	exit($data);
?>