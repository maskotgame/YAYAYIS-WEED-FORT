<?php
	use anorrl\GameServer;
	use anorrl\User;
	use anorrl\utilities\ClientDetector;

	if(!ClientDetector::HasAccess())
		exit(http_response_code(500));

	if(isset($_GET['jobID']) && isset($_GET['userID'])) {
		$gameserver = GameServer::GetFromJobID($_GET['jobID']);

		$user = User::FromID(intval($_GET['userID']));

		if($gameserver != null && $user && !$user->isBanned()) {
			$gameserver->addPlayer($user);

			die("OK");
		}
	}
	http_response_code(503);
?>
