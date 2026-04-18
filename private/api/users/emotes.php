<?php
	use anorrl\User;
	use anorrl\enums\AssetType;

	header("Content-Type: application/json");

	$userid = 1;

	if(isset($_GET['userId'])) {
		$userid = intval($_GET['userId']);
	} else {
		if(SESSION)
			$userid = SESSION->user->id;
	}

	$user = User::FromID($userid);

	if(!$user && SESSION) {
		$user = SESSION->user;
	}

	if(!$user)
		die(json_encode([
			"success" => false,
			"reason" => "User not found!"
		]));

	$emotes = [];

	foreach($user->getOwnedAssets(AssetType::EMOTE) as $emote) {
		$emotes[] = [
			"id" => $emote->id,
			"name" => $emote->name,
			"version" => $emote->current_version,
			"versionid" => $emote->getLatestVersionDetails()->id,
		];
	}

	die(json_encode([
		"success" => true,
		"emotes" => $emotes
	]));

?>