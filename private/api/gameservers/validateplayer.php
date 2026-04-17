<?php
	use anorrl\GameServer;
	use anorrl\User;
	
	if(isset($_GET['access']) && isset($_GET['jobID']) && isset($_GET['userID'])) {
		if($_GET['access'] == CONFIG->asset->key) {
			$gameserver = GameServer::GetFromJobID($_GET['jobID']);

			$user = User::FromID(intval($_GET['userID']));

			if($gameserver != null && $user && !$user->isBanned()) {
				$gameserver->addPlayer($user);

				die("OK");
			}
		}
	}
	http_response_code(503);
?>
