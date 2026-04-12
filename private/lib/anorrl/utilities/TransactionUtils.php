<?php

	namespace anorrl\utilities;

	use anorrl\Asset;
	use anorrl\Database;
	use anorrl\User;
	use anorrl\enums\TransactionType;


	class TransactionUtils {
		private static function getRandomString($length = 15): string {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';
			
			for ($i = 0; $i < $length; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$randomString .= $characters[$index];
			}
	
			return $randomString;
		}

		
		public static function GenerateID() {
			$id = self::getRandomString();

			$instances = Database::singleton()->run(
				"SELECT `id` FROM `transactions` WHERE `id` LIKE :id",
				[ ":id" => $id ]
			)->rowCount();
			
			if($instances != 0) {
				return self::GenerateID();
			} else {
				return $id;
			}
		}


		public static function CommitTransaction(TransactionType $type, User $user, int $cost = 0, Asset|null $asset = null) {
			$ta_id = self::GenerateID();

			if($asset) {
				Database::singleton()->run(
					"INSERT INTO `transactions`(`id`, `userid`, `assetcreator`, `asset`, `method`, `cost`) VALUES (:id, :uid, :auid, :aid, :method, :cost)",
					[
						":id"     => $ta_id,
						":uid"    => $user->id,
						":auid"   => $asset->creator->id,
						":aid"    => $asset->id,
						":method" => $type->ordinal(),
						":cost"   => $cost
					]
				);
			} else {
				Database::singleton()->run(
					"INSERT INTO `transactions`(`id`, `userid`, `method`, `cost`) VALUES (:id, :uid, :method, :cost)",
					[
						":id"     => $ta_id,
						":uid"    => $user->id,
						":method" => $type->ordinal(),
						":cost"   => $cost
					]
				);
			}
		}

		public static function CommitAssetTransaction(TransactionType $type, Asset $asset, User $user) {
			$cost = 0;
			switch($type) {
				case TransactionType::CONES:
					$cost = $asset->cones;
					break;
				case TransactionType::LIGHTS:
					$cost = $asset->lights;
					break;
			}
			self::CommitTransaction($type, $user, $cost, $asset);
		}

		public static function StipendCheckToUser(int $user_id) {
			$user = User::FromID($user_id);
			if($user != null && !$user->isBanned() && $user->pendingStipend()) {

				$db = Database::singleton();

				$rowexists = $db->run(
					"SELECT * FROM `subscriptions` WHERE `userid` = :uid",
					[":uid" => $user->id]
				)->rowCount() == 1;

				$db->run(
					$rowexists ?
						'UPDATE `subscriptions` SET `lastpaytime` = now() WHERE `userid` = :uid'
						: 'INSERT INTO `subscriptions`(`userid`) VALUES (:uid)',
					[":uid" => $user->id]
				);

				self::CommitTransaction(TransactionType::CONES, $user, 100);
				self::CommitTransaction(TransactionType::LIGHTS, $user, 250);
			}
		}
	}
?>