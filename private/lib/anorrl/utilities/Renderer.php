<?php 
	namespace anorrl\utilities;

	use anorrl\User;
	use anorrl\utilities\Arbiter;

	class Renderer {

		public static function RenderPlayer(int $id = 0) {

			if(\CONFIG->arbiter->disabled) {
				return null;
			}
			
			$data = Arbiter::singleton()->request(
				"avatar-render",
				[
					"UserId" => $id,
					"IsHeadshot" => false,
					"IsClothing" => true
				]
			);

			if(!$data) {
				return null;
			}

			return $data;
		}

		public static function RenderUser(int $id = 0, bool $headshot = false) {
			if($id == 0) {
				return null;
			}
			
			$user = User::FromID($id);

			if($user == null) {
				return null;
			}

			if(\CONFIG->arbiter->disabled) {
				return null;
			}

			$data = Arbiter::singleton()->request(
				"avatar-render",
				[
					"UserId" => $id,
					"IsHeadshot" => $headshot,
					"IsClothing" => false
				]
			);

			return $data;
		}

		public static function RenderMesh(int $id = 0) {

			if(\CONFIG->arbiter->disabled) {
				return null;
			}

			$data = Arbiter::singleton()->request("mesh-render", ["MeshId" => $id]);

			if(!$data) {
				return null;
			}

			return $data;
		}

		public static function RenderPlace(int $id = 0) {

			if(\CONFIG->arbiter->disabled) {
				return null;
			}

			$data = Arbiter::singleton()->request("place-render", ["PlaceId" => $id]);

			if(!$data) {
				return null;
			}

			return $data;
		}

		public static function RenderModel(int $id = 0) {

			if(\CONFIG->arbiter->disabled) {
				return null;
			}

			$data = Arbiter::singleton()->request("model-render", ["AssetId" => $id]);

			if(!$data) {
				return null;
			}

			return $data;
		}
	}
?>