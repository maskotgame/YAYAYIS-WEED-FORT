<?php
	use anorrl\utilities\Thumbnail;
	
	header("Content-Type: image/png");

	if(!isset($hash) || !isset($image))
		die(http_response_code(500));


	$data = Thumbnail::Get3DTex($hash, $image, false);

	if(!$data)
		die(http_response_code(500));

	exit($data);
?>