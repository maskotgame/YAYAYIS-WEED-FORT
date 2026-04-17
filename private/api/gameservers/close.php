<?php
	use anorrl\GameServer;

	$access = CONFIG->asset->key;

	if(isset($_GET['access']) && isset($_GET['jobID'])) {
		if($_GET['access'] == $access) {
			$gameserver = GameServer::GetFromJobID($_GET['jobID']);

			if($gameserver) {
				$gameserver->destroy();
				die();
			}
		}
	}

	http_response_code(503);
?>