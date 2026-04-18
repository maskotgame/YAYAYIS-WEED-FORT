<?php
	use anorrl\User;
	use anorrl\enums\AssetType;

	header("Content-Type: application/json");

	if(SESSION) {
		$userid = SESSION->user->id;
	} else {
		$userid = 1;
	}

	$user = User::FromID($userid);

	die(json_encode([
		"emotes" => $user->getOwnedAssets(AssetType::ANIMATION)
	]));

?>