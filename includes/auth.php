<?php

namespace Microsite;

$app->middleware('auth', function(Response $response, Pack32 $app) {
	if(isset($_COOKIE['auth_token'])) {
		if($user = $app->db()->row('SELECT * FROM users WHERE auth_token = :auth_token', ['auth_token' => $_COOKIE['auth_token']])) {
			$response['currentuser'] = $user['email'];
			$response['loggedin'] = true;
			$response['user'] = $user;
		}
		else {
			setcookie('auth_Token', md5(time()), 1, '/');
			$response['currentuser'] = false;
			$response['loggedin'] = false;
			$response['user'] = false;
		}
	}
	elseif(isset($_SESSION['user_email'])) {
		$response['currentuser'] = $_SESSION['user_email'];
		$response['loggedin'] = true;
		$response['user'] = $app->db()->row('SELECT * FROM users WHERE email = :email', ['email' => $_SESSION['user_email']]);
	}
	else {
		$response['currentuser'] = false;
		$response['loggedin'] = false;
		$response['user'] = false;
	}
});

$app->route('login', '/auth/login', function (Pack32 $app) {
	$assertion = $_POST['assertion'];
	$audience = $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];

	$url = 'https://verifier.login.persona.org/verify';

	$http = new HTTP();
	$result = $http->post($url, compact('assertion', 'audience'));
	$result = json_decode($result);

	if($result->status == 'okay') {
		$row = $app->db()->row('SELECT * FROM users WHERE email = :email', ['email'=>$result->email]);
		$auth_token = md5(time() . 'This is salt. It is used to improve flavor of cookies.');
		if($row) {
			$app->db()->query('UPDATE users SET last_on = :now, auth_token = :auth_token WHERE email = :email', ['email'=>$result->email, 'now' => time(), 'auth_token' => $auth_token]);
			$_SESSION['user'] = $row;
		}
		else {
			$app->db()->query(
				'INSERT INTO users (username, email, auth_token) values (:username, :email, :auth_token)',
				[
					'username' => preg_replace('#@.*$#', '', $result->email),
					'email' => $result->email,
					'auth_token' => $auth_token,
				]
			);
			$row = $app->db()->row('SELECT * FROM users WHERE email = :email', ['email'=>$result->email]);
			$_SESSION['user'] = $row;
			$app->db()->query('UPDATE users SET account_id = id WHERE id = :id', ['id' => $row['id']]);
		}
		$auth_expiry = new \DateTime('+3 months');
		setcookie('auth_token', $auth_token, $auth_expiry->getTimestamp(), '/');
		$_SESSION['public_notice'] = true;

		$_SESSION['user_email'] = $result->email;
	}
	else {
		unset($_SESSION['user_email']);
		unset($_SESSION['user']);
	}

	var_dump($result);
});

$app->route('logout', '/auth/logout', function () {
	setcookie('auth_token', 'zzzzzz', 1, '/');
	if(isset($_SESSION['user_email'])) {
		unset($_SESSION['user_email']);
		return 'true';
	}
	else {
		return 'false';
	}
});

$app->route('public_notice', '/public_notice', function(){
	unset($_SESSION['public_notice']);
	if($_POST['public'] == 'true') {
		setcookie('auth_token', 'zzzzzz', 1, '/');
	}
});