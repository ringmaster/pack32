<?php

namespace Microsite;

$authdata = function(Response $response, Pack32 $app) {
	if(isset($_SESSION['user_email'])) {
		$response['currentuser'] = $_SESSION['user_email'];
		$response['loggedin'] = true;
		$response['user'] = $app->db()->row('SELECT * FROM users WHERE email = :email', ['email' => $_SESSION['user_email']]);
	}
	else {
		$response['currentuser'] = false;
		$response['loggedin'] = false;
		$response['user'] = false;
	}
};

$app->route('login', '/auth/login', function (App $app) {
	$assertion = $_POST['assertion'];
	$audience = $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];

	$url = 'https://verifier.login.persona.org/verify';

	$http = new HTTP();
	$result = $http->post($url, compact('assertion', 'audience'));
	$result = json_decode($result);

	if($result->status == 'okay') {

		$row = $app->db()->row('SELECT * FROM users WHERE email = :email', ['email'=>$result->email]);
		if($row) {
			$_SESSION['user'] = $row;
		}
		else {
			$app->db()->query(
				'INSERT INTO users (username, email) values (:username, :email)',
				[
					'username' => preg_replace('#@.*$#', '', $result->email),
					'email' => $result->email,
				]
			);
			$row = $app->db()->row('SELECT * FROM users WHERE email = :email', ['email'=>$result->email]);
			$_SESSION['user'] = $row;
		}

		$_SESSION['user_email'] = $result->email;
	}
	else {
		unset($_SESSION['user_email']);
		unset($_SESSION['user']);
	}

	var_dump($result);
});

$app->route('logout', '/auth/logout', function () {
	if(isset($_SESSION['user_email'])) {
		unset($_SESSION['user_email']);
		return 'true';
	}
	else {
		return 'false';
	}
});