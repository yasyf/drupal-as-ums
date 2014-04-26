<?php

function jprint($object)
{
	ob_end_clean();
	print json_encode($object);
	ob_start();
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

	if(abs(time() - intval($time)) > 60){
		jerror('expired signature:');
	}

	$check = generate_hash($time, $action, $fields);
	if($signature != $check){
		jerror('invalid signature');
	}
}

function fetch_fields($user){
	$fields = explode(",",field('fields'));
	return array_intersect_key((array)$user, array_flip($fields));
}

function generate_hash($time, $action, $fields, $secret='SECRET'){
	return sha1(sha1($time.$action.$fields).$secret);
}

function make_api_call($action, $field_list, $values) {
	$endpoint = 'http://stopfortheone.org/private/auth/api.php';
	$time = time();
	$fields = array(
				'a' => $action,
				'f' => $field_list
			);
	$fields['s'] = generate_hash($time, $fields['a'], $fields['f']);
	$fields['t'] = $time;

	foreach ($values as $i => $value) {
		$fields[(string)$i] = urlencode($value);
	}

	$fields_string = '';
	foreach($fields as $key => $value) {
		$fields_string .= $key.'=' . $value . '&';
	}
	rtrim($fields_string, '&');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

	$result = json_decode(curl_exec($ch));

	curl_close($ch);

	return $result;
}

?>