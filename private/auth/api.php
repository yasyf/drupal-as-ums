<?php
require_once 'functions.php';
verifySignature();

define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
$base_url = 'http://'.$_SERVER['HTTP_HOST'];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$action = $_POST['a'];

if($action == 'check'){
	// curl -d "a=check&f=username,password&0=YasyfM&1=123456" "http://stopfortheone.org/private/auth/api.php"

	$uid = user_authenticate(field('username'), field('password'));
	if($uid != false){
		jprint(array('uid' => $uid));
	}else{
		jerror('invalid credentials');
	}
}
elseif ($action == 'fetch') {
	// curl -d "a=fetch&f=username,uid,fields&0=YasyfM&1=211&2=uid" "http://stopfortheone.org/private/auth/api.php"

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
		jprint(fetch_fields($user));
	}
}
elseif ($action == 'set') {
	// curl -d "a=set&f=username,uid,fields,values&0=YasyfM&1=211&2=signature&3=test" "http://stopfortheone.org/private/auth/api.php"

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
		$values = explode(",",field('values'));
		$edit = array_combine($fields, $values);
		if(user_save($user, $edit)){
			jprint(fetch_fields($user));
		}else{
			jerror('invalid fields');
		}
	}
}
else{
	jerror('invalid action');
}
?>