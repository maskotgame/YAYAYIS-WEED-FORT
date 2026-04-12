<?php
	namespace anorrl;

	use anorrl\Database;
	use anorrl\enums\AssetType;
	use anorrl\Asset;

	class AssetVersion {

		public int $id;
		public Asset $asset;
		public int $sub_id;
		public string $md5sig;
		public string $md5thumb;
		public \DateTime $publish_date;

		public static function GetVersionFromID(int $versionid) {
			include $_SERVER["DOCUMENT_ROOT"]."/private/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `asset_versions` WHERE `id` = ?");
			$stmt_getuser->bind_param('i', $versionid);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();

			if($result->num_rows == 1) {
				return new self($result->fetch_assoc());
			} else {
				return null;
			}
		}

		public static function GetLatestVersionOf(Asset|int $asset): AssetVersion|null {
			if($asset instanceof Asset) {
				return self::GetVersionOf($asset, $asset->current_version);
			} else {
				$asset = Asset::FromID($asset);
				return self::GetVersionOf($asset, $asset->current_version);
			}
		}

		public static function GetVersionOf(Asset|int $asset, int $version): AssetVersion|null {
			$id = $asset;
			if($asset instanceof Asset) {
				$id = $asset->id;
			}
			include $_SERVER["DOCUMENT_ROOT"]."/private/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `asset_versions` WHERE `assetid` = ? AND `subid` = ?");
			$stmt_getuser->bind_param('ii', $id, $version);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();

			if($result->num_rows == 1) {
				return new self($result->fetch_assoc());
			} else {
				return null;
			}
		}


		function __construct($rowdata) {
			$this->id = intval($rowdata['id']);
			$this->asset = Asset::FromID(intval($rowdata['assetid']));
			$this->sub_id = intval($rowdata['subid']);
			$this->md5sig = strval($rowdata['md5sig']);
			$this->md5thumb = strval($rowdata['md5thumb']);

			$this->publish_date = \DateTime::createFromFormat("Y-m-d H:i:s", $rowdata['publishdate']);	
		}

		function ResetThumbnail() {
			
			if($this->asset->type != AssetType::AUDIO && $this->asset->type != AssetType::PLACE) {
				return;
			}

			$md5hash = $this->md5sig;

			if($this->asset->type == AssetType::AUDIO) {
				$md5hash = "sound";
			}

			Database::singleton()->run(
				"UPDATE `asset_versions` SET `md5thumb` = :md5 WHERE `id` = :id",
				[
					":md5" => $md5hash,
					":id" => $this->id
				]
			);

			if($this->asset->type == AssetType::PLACE) {
				// remove place thumbnail
				unlink($_SERVER['DOCUMENT_ROOT']."/../assets/thumbs/".$this->asset->id);
			}
		}

		function setThumbnail(Asset $asset) {

			if($asset->type == AssetType::DECAL) {
				$asset = $asset->getRelatedAssets()[0];
			}

			$version = AssetVersion::GetLatestVersionOf($asset);

			if($version == null)
				return;

			Database::singleton()->run(
				"UPDATE `asset_versions` SET `md5thumb` = :md5 WHERE `id` = :id",
				[
					":md5" => ($asset->id == $this->asset->id ? $this : $version)->md5sig,
					":id" => $this->id
				]
			);
		}

	}
?>