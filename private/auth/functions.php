<?php

function jprint($object)
{
	print json_encode($object);
	die();
}

function field($name){
	$f = explode(",", $_POST['f']);
	$fields = array_combine($f,range(0,count($f)-1));
	return $_POST[(string)$fields[$name]];
}

function jerror($message)
{
	jprint(array('status' => 'error', 'message' => $message));
}

function jsuccess($message)
{
	jprint(array('status' => 'success', 'message' => $message));
}

function verifySignature()
{
	$time = $_POST['t'];
	$signature = $_POST['s'];
	$fields = $_POST['f'];
	$action = $_POST['a'];

	if(empty($time) || empty($signature)){
		jerror('missing parameter');
	}

	if((time() - int($time)) < 60){
		jerror('expired signature');
	}

	$check = sha1(sha1($time.$action.$fields)."sdf#$Ih2MKLS!");
	if($signature != $check){
		jerror('invalid signature');
	}
}

?>