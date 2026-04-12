<?php
	use anorrl\enums\AssetType;

	$user = SESSION->user;

	$result = [];

	if($user != null) {
		$models = $user->getOwnedAssets(AssetType::MODEL, $_GET['query'] ?? "", true);
		foreach($models as $model) {
			$result[] = [
				"AssetSetID" => 1,
				"AssetTypeID" => $model->type->ordinal(),
				"AssetVersionID" => $model->getVersionID(),
				"ID" => $model->id,
				"Name" => $model->name,
				"SortOrder" => 2147483647,
				"NewerVersionAvailable" => "False",
				"AssetID" => $model->id,
				"IsEndorsed" => false,
				"TotalNumAssetsInSet" => 0
			];
		}

	}
	die(json_encode($result));

?>