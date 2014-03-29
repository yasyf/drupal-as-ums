<?php
require_once 'functions.php';
verifySignature();

define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
$base_url = 'http://'.$_SERVER['HTTP_HOST'];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$action = $_POST['a'];

if($action == 'email_to_username'){
	// curl -d "a=email_to_username&f=email&0=yasyfm@gmail.com" "http://stopfortheone.org/private/auth/api.php"
	$user = user_load_by_mail(field('email'));
	if(!$user){
		jerror('invalid user');
	}
	else{
		jprint($user->name);
	}
}
elseif($action == 'check'){
	// curl -d "a=check&f=username,password&0=YasyfM&1=123456" "http://stopfortheone.org/private/auth/api.php"
	if(!user_load_by_name(field('username'))){
		jerror('invalid user');
	}
	else{
		$uid = user_authenticate(field('username'), field('password'));
		if($uid != false){
			jprint(array('uid' => $uid));
		}else{
			jerror('invalid credentials');
		}
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
elseif ($action == 'create') {
	// curl -d "a=create&f=fields,values&0=username,password&1=YasyfM,123456 "http://stopfortheone.org/private/auth/api.php"

	$username = field('username');
	$user = user_load_by_name($username);
	if($user){
		jerror('invalid username');
	}
	else{	
		$fields = explode(",",field('fields'));
		$values = explode(",",field('values'));
		$insert = array_combine($fields, $values);
		if(!$insert['password']){
			$insert['password'] = user_password(8);
		}
		$user_fields =  array(
							'name' => $insert['username'],
							'mail' => $insert['email'],
							'pass' => $insert['password'],
							'status' => 1,
							'roles' => array(DRUPAL_AUTHENTICATED_RID  => 'authenticated user')
						);
		if(user_save('',$user_fields)){
			$user = user_load_by_name($username);
			jprint(array('uid' => $user->uid));
		}else{
			jerror('error creating user');
		}
	}
}
else{
	jerror('invalid action');
}
?>