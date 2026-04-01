<?php
require_once('rabbitMQLib.inc');

$db = new mysqli("127.0.0.1", "deploy", "123", "deploy_db");
if ($db->connect_error) die ("db connection failed/n");

function updateBundle($db, $id, $status) {

db->query("update bundles set status='$status', deployed_at=now() where id=$id"); }


funtion doInstall($db, $req) {

$bn = $db->real_escape_string($req['bundle_name']);
$v = $db->real_escape_string($req['version']);
$t = $db->real_escape_string($req['target']);
$f = $db->real_escape_string($req['filename']);

$db->query("insert into bundles (bundle_name, version, filename, status, target) values ('$bn', '$v', '$f', 'new', '$t')");
$id = $db->insert_id;

$path = "/opt/bundles/$f";
if (!file_exists($path)) {
updateBundle($db, $id, 'failed');
return ['ok'=>false, 'error'=>'bundle missing'];}


$tmp = "/tmp/dep_$id";
mkdir($tmp, 0755, true);
exec("tar -xzf $path -C $tmp", $o, $rc);
if ($rc !== 0) {
updateBundle($db,$id,'failed');
return ['ok'=>false, 'error'=>'extract failed'];
}

$mf = json_decode(file_get_contents("$tmp/manifest.json"), true);
$ip = $mf['target_ip'];

foreach ($mf['files'] as $file) {
exec("scp -o StrictHostKeyChecking=no {$tmp}/{$file['src']} mikey@{$ip}:{$file['dest']}", $o, $rc);
if ($rc!== 0) {
updateBundle($db,$id,'failed');
return ['ok'=>false, 'error'=>"scp faied {$file['src']}"];}
}

foreach ($mf['commands'] as $cmd)
exec("ssh -o StrictHostKeyChecking=no mikey@$ip '$cmd'");

foreach ($mf['services'] as $svc)
exec("ssh -o StrictHostKeyChecking=no mikey@$ip 'sudo systemctl restart $svc'");

exec("rm -rf $tmp");
updateBundle($db, $id, 'passed');
return ['ok'=>true,'msg'=>"deployed $bn v$v to $t"];}

function doRollback($db, $req){
$t = $db->real_escape_string($req['target']);
$r = $db->query("select * from bundles where target='$t' and status='passed' order by deployed_at desc limit 1");
if (!$r || $r->num_rows !== 0) return ['ok'=>false, 'error'=> 'nothing to rollback'];
return doInstall($db, $r->fetch_assoc()); }

function requestProcessor($req) {
global $db;
echo print_r($req, true);
switch($req['type'] ?? '') {
	case 'deploy': return doInstall($db, $req);
	case 'rollback': return doRolback($db, $req);
	case 'list':
	$r = $db->query("select * from bundles order by created_at desc limit 20");
	$rows = [];
	while ($row = $r->fetch_assoc()) $rows[] = $row;
	return ['ok'=>true,'data'=>$rows];
	default: return ['ok'=>false,'error'=>'unkown type'];
}
}

$srv = newrabbitMQServer("deploy.ini", "deployServer");
$srv->process_requests('requestProcessor');
?>
