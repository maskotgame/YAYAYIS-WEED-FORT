<?php
	use anorrl\Asset;

	$user = SESSION ? SESSION->user : null;
	
	if(isset($_POST['type'])) {
		if(isset($_POST['id'])) {
			$asset = Asset::FromID(intval($_POST['id']));

			if($asset != null && ($asset->creator->id == $user->id || $user->isAdmin())) {
				if($_POST['type'] == "delete") {
					$asset->delete();
					$message = "Success!";
				} else if($_POST['type'] == "render") {
					$asset->render();
					$message = "Success!";
				}

			}
		}
	}

	if(!isset($message))
		$message = "You are not authorised to use this.";

	die($message);
?>
