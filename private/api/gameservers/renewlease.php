<?php
	use anorrl\GameServer;

	if(isset($_GET['access']) && isset($_GET['jobID'])) {
		if($_GET['access'] == CONFIG->asset->key) {
			$gameserver = GameServer::GetFromJobID($_GET['jobID']);

			if($gameserver) {
				$gameserver->renewLease();
				die();
			}
		}
	}
	http_response_code(503);
?>