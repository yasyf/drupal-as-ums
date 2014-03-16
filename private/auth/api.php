<?php
require_once 'functions.php';
//verifySignature();
define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
$base_url = 'http://'.$_SERVER['HTTP_HOST'];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$action = $_POST['a'];
if($action == 'check'){
	$uid = user_authenticate(field('username'), field('password'));
	if($uid != false){
		jprint(array('uid' => $uid));
	}else{
		jerror('invalid credentials');
	}
}
elseif ($action == 'fetch') {
	$username = field('username');
	$uid = field('uid');
	$user = user_load_by_name($username);
	if(!$user){
		jerror('invalid username');
	}
	elseif ($user->uid != $uid) {
		jerror('invalid uid');
	}
	else{
		$fields = explode(",",field('fields'));
		jprint(array_intersect_key((array)$user, array_flip($fields)));
	}
}
?>