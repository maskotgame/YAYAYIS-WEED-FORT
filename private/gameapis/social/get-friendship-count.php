<?php
	use anorrl\User;
	
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-Type: application/json");
	
	if(isset($_GET['userId'])) {
		$user = User::FromID(intval($_GET['userId']));

		if($user != null) {
			die(json_encode(
				[
					"success" => true,
					"count" => $user->getFriendsCount()
				]
			));
		} else {
			$user = SESSION->user;

			if($user != null) {
				die(json_encode(
					[
						"success" => true,
						"count" => $user->getFriendsCount()
					]
				));
			} 
		}
	} else {
		$user = SESSION->user;

		if($user != null) {
			die(json_encode(
				[
					"success" => true,
					"count" => $user->getFriendsCount()
				]
			));
		} 
	}

	die(json_encode(
		[
			"success" => false
		]
	));
?>