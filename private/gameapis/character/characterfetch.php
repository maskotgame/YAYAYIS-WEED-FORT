<?php 
	use anorrl\User;

    header("Content-Type: text/plain"); 
    // dont cache this shit!
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

	$domain = CONFIG->domain;

    if(isset($_GET['assetId'])): ?>
http://<?= $domain ?>/Asset/BodyColors.ashx?clothing;http://<?= $domain ?>/asset/?id=<?= $_GET['assetId'] ?>
<?php else: 

$userId = intval($_GET['userId']) ?? 1;

$user = User::FromID($userId);

if($user == null) {
    $user = User::FromID(1);
    $userId = 1;
}

die($user->getCharacterAppearance());
endif ?>