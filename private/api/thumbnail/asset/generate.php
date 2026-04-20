<?php
	use anorrl\Asset;
	use anorrl\utilities\Thumbnail;
	
	header("Content-Type: application/json");

	if(!isset($_GET['for']))
		die(http_response_code(500));

	$asset = Asset::FromID(intval($_GET['for']));
	
	if(!$asset)
		die(http_response_code(500));

	$generated_shit = Thumbnail::Generate3D($asset);

	if(!$generated_shit)
		die(http_response_code(500));

	exit(json_encode($generated_shit));
?>