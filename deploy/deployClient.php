<?php
require_once('backend/rabbitMQLib.inc');

$act = $argv[1] ?? '';
$tgt = $argv [2] ?? '';
$f = $argv[3] ?? '';
$v = $argv[4] ?? '';

if (!$act || !$tgt) die("usage: php deployClient.php <deploy|rollback|list> <qa|prod> [file] [ver]\n");

$c = new rabbitMQClient("backend/deploy.ini", "deployServer");
$req = ['type' => $act, 'target' => $tgt];
if ($act === 'deploy') {
	$req['bundle_name'] = "adem";
	$req['version'] = $v;
	$req['filename'] = $f;
}
if $act === 'markbad'){
	$req['id'] = (int)$f;
}

print_r($c->send_request($req));
?>
	
