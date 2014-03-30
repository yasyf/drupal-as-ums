#SFTO User Syncing (Drupal as UMS)

All requests should be POST requests. Each request can have the following parameters, plus the authentication parameters described below.

1. `a` is the action, one of `check`, `fetch`, `set`, or `create`
2. `f` are the comma-separated fields of the remaining numbered parameters
3. `0`, `1`, etc. are the zero-indexed list of values associated with the fields listed in `f`

Each call is demonstrated below for further clarification.

##Authentication

All of the examples requests below, and indeed all requests made to this API, require two authentication parameters. These authentication parameters are not included in the sample code, but should be in any real requests.

1. `t` is the current UNIX timestamp
2. `s` is the request signature, generated from the `t` parameter, the action of the request, the fields included, and a secret

The following is an example of how to generate these authentication parameters.

```php
<?php
	function generate_hash($time, $action, $fields){
		return sha1(sha1($time.$action.$fields).'sdf#$Ih2MKLS!'); //'sdf#$Ih2MKLS!' is the secret
	}
	$time = time();
	$action = 'check';
	$fields = 'username,password';
	echo '&t='.$time.'&s='.generate_hash($t, $action, $fields);
?>
```

##Cases

There are three main cases when using this system, which will be covered below. I will then explore all the possibilities with both traditional and Facebook logins, and map them to one of the two login cases.

Each case will be documented with step-by-step instructions on how to proceed. Example code is with command-line `cURL` for the sake of portability, but you probably want to implement it in whatever language the backend is in (eg. PHP's [`curl_exec`](http://www.php.net/manual/en/curl.examples-basic.php)).

A single example API call in PHP is below. The rest of the examples will be command-line `cURL`.

```php
<?php
//Sample API call to check if user exists
$endpoint = 'http://stopfortheone.org/private/auth/api.php';
$time = time();
$fields = array(
			'a' => 'check',
			'f' => 'user,password',
			'0' => urlencode('YasyfM'),
			'1' => urlencode('my_password')
		);
$fields['s'] = generate_hash($time, $fields['a'], $fields['f']);
$fields['t'] = $time;

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

if($result['status'] == 'error') {
	echo "This user does not exist."
} else {
	echo "This user exists, and has a uid of " . $result['uid'];
}
?>
```

###Case 1: New User Login

In this case, a user has logged into the site, and does **not** exist in the Drupal database. This is determined by making a request to `check`, and receiving the `invalid user` response.

```bash
$ curl -X POST -d "a=check&f=username,password&0=Test123&1=my_password" "http://stopfortheone.org/private/auth/api.php"
{"status":"error","message":"invalid user"}
```

Following this, you will have already created the new user on your end, so all that is left to do before continuing on as normal is making a request to `create` so as to let Drupal know about the new account. This API call requires the `username` and `password` fields, with `email` and `photo_url` being optional.

```bash
$ curl -X POST -d "a=create&f=fields,values&0=username,password&1=YasyfM,my_password" "http://stopfortheone.org/private/auth/api.php"
{"uid":"211"}
```

###Case 2: Existing User Login

In this case, a user has logged into the site, and **does** exist in the Drupal database. This is determined by making a request to `check`, and receiving the `uid` response.

```bash
$ curl -X POST -d "a=check&f=username,password&0=YasyfM&1=my_password" "http://stopfortheone.org/private/auth/api.php"
{"uid":"211"}
```

Following this, you need to create the user on your end if it doesn't already exist, and update the data on your end if it does already exist. Either way, you will need to make a call to `fetch` to get the lastest information from Drupal. This API call requires that you pass the `username` and `uid` parameters, as well as a list of fields to retrieve, as the `fields` parameter.

```bash
$ curl -X POST -d "a=fetch&f=username,uid,fields&0=YasyfM&1=211&2=uid,signature" "http://stopfortheone.org/private/auth/api.php"
{"uid":"211", "signature": "test"}
```

###Case 3: Field Updated

In this case, the user has already been logged in, and they have changed one of the settings that should be synced back to the Drupal database. All this requires is a call to `set`, with the `username` and `uid` parameters, as well as a list of fields and their respective values to set, as the `fields` and `values` parameters.

```bash
$ curl -X POST -d "a=set&f=username,uid,fields,values&0=YasyfM&1=211&2=signature&3=test2" "http://stopfortheone.org/private/auth/api.php"
{"signature": "test2"}
```

##Login Handling

###Traditional

The site should present a username and password field. When a user enters credentials into this box and submits, the site should, at that time, follow the protocol for `Case 1` or `Case 2` depending on the result of a call to `check`, as described above. Please see the respective cases for examples. If the user exists, but has provided an invalid password, `check` will return the `invalid credentials` response, as demonstrated below.

```bash
$ curl -X POST -d "a=check&f=username,password&0=YasyfM&1=bad_pass" "http://stopfortheone.org/private/auth/api.php"
{"status":"error","message":"invalid credentials"}
```

###Facebook

When a user logs in through Facebook, protocol for `Case 1` or `Case 2` should still be followed, as above, once again depending on the result of a call to `check`, as described above. There are a few differences here to be aware of, primarily that `check` will be used slightly differently. However, the actions associated with the correctly-interpreted results will still be the same as for traditional logins.

1. For new users, calls to `create` should omit the `password` parameter, as one will be randomly generated
2. When making API calls, the user's email address should be used as the `username` parameter
3. Calls to `check` should use anything for the `password` parameter (see **4**)
4. Response of `check` is `invalid credentials` if the the user exists, and `invalid user` if it does not
