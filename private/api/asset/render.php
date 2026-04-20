<?php
	use anorrl\Asset;

	$user = SESSION ? SESSION->user : null;

	header("Content-Type: application/json");

	$result = ["error" => true, "reason" => "Request failed."];
	
	if(!isset($_POST['id'])) {
		die(json_encode($result));
	}

	$asset = Asset::FromID(intval($_POST['id']));

	if(!$asset) {
		$result['reason'] = "Failed to retrieve asset.";
		die(json_encode($result));
	}

	if(!($asset->creator->id == $user->id || $user->isAdmin())) {
		$result['reason'] = "You are not authorised to perform this action.";
		die(json_encode($result));
	}

	$asset->render();
	$asset->render(true);

	die(json_encode([
		"error" => false
	]));
?>
