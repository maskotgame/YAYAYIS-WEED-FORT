<?php
	use anorrl\GameServer;

	if(isset($_GET['access']) && isset($_GET['jobID']) && isset($_GET['userID'])) {
		if($_GET['access'] == CONFIG->asset->key) {
			$gameserver = GameServer::GetFromJobID($_GET['jobID']);

			if($gameserver) {
				$gameserver->removePlayer(intval($_GET['userID']));
				die();
			}
		}
	}
	http_response_code(503);
?>