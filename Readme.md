#SFTO User Syncing (Drupal as UMS)

##Authentication

All of the examples requests below, and indeed all requests made to this API require two authentication parameters. All requests should be POST requests.

1. `t` is the current UNIX timestamp
2. `s` is the request signature, generated from the `t` parameter, the action of the request, the fields included, and a secret

The following is an example of how to generate these authentication parameters.

```php
<?php
	function generate_hash($time, $action, $fields){
		return sha1(sha1($time.$action.$fields).'sdf#$Ih2MKLS!'); //'sdf#$Ih2MKLS!' is secret
	}
	$time = time();
	$action = 'check';
	$fields = 'username,password';
	echo "&t=$time&s=".generate_hash($t, $action, $fields);
?>
```

##Cases

There are two main cases when using this system, which will be covered below. I will then explore all the possibilities with both traditional and Facebook logins, and map them to one of these two cases.

Each case will be documented with step-by-step instructions on how to proceed. Example code is with command-line cURL for the sake of portability, but you probably want to implement it in whatevr language the backend is in (eg. PHP's [curl_exec](http://www.php.net/manual/en/curl.examples-basic.php)).

###Case 1: New User

In this case, a user has logged into the site, and does **not** exist in the Drupal database. This is determined by making a request to `check`, and receiving the `invalid user` response.

```
$ curl -d "a=check&f=username,password&0=Test123&1=123456" "http://stopfortheone.org/private/auth/api.php"
{"status":"error","message":"invalid user"}
$ 
```