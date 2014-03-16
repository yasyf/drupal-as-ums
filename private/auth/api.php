<?php
require_once 'functions.php';
//verifySignature();
define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
$base_url = 'http://'.$_SERVER['HTTP_HOST'];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

if($_POST['a'] == 'check'){
	$uid = user_authenticate(field('username'), field('password'));
	if($uid != false){
		jprint(array('uid' => $uid));
	}else{
		jerror('invalid credentials');
	}
}
?>