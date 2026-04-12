<?php
	use anorrl\enums\AssetType;

	$user = SESSION->user;

	$result = [];

	if($user != null) {
		$decals = $user->getOwnedAssets(AssetType::DECAL, $_GET['query'] ?? "", true);
		foreach($decals as $decal) {
			$result[] = [
				"AssetSetID" => 1,
				"AssetTypeID" => $decal->type->ordinal(),
				"AssetVersionID" => $decal->getVersionID(),
				"ID" => $decal->id,
				"Name" => $decal->name,
				"SortOrder" => 2147483647,
				"NewerVersionAvailable" => "False",
				"AssetID" => $decal->id,
				"IsEndorsed" => false
			];
		}

	}
	die(json_encode($result));

?>