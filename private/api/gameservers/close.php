<?php
	use anorrl\GameServer;

	use anorrl\utilities\ClientDetector;

	if(!ClientDetector::HasAccess())
		exit(http_response_code(500));

	if(isset($_GET['jobID'])) {
		$gameserver = GameServer::GetFromJobID($_GET['jobID']);

		if($gameserver) {
			$gameserver->destroy();
			die();
		}
	}

	http_response_code(503);
?>