<?php

namespace Microsite;

include 'microsite.phar';

session_start();

$app = new App();

// Assign a directory in which templates can be found for rendering
$app->template_dirs = [
	__DIR__ . '/views',
];

$authdata = function(Response $response) {
	$response['currentuser'] = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
	$response['loggedin'] = isset($_SESSION['user_email']);
};

$app->route('home', '/', $authdata, function (Response $response) {
	$response['title'] = 'Cub Scout Pack 32 - Pickering Valley - Events, News, Calendar, &amp; Communication Center';
	$response['menu'] = [
		[
			'href' => '/',
			'title' => 'Home',
		],
	];
	if($response['loggedin']) {
		$response['menu'][] = [
			'href' => 'javascript:navigator.id.logout()',
			'title' => 'Log Out',
			'class' => 'login',
		];
	}
	else {
		$response['menu'][] = [
			'href' => 'javascript:navigator.id.request()',
			'title' => 'Log In',
			'class' => 'login',
		];
	}
	return $response->render('home.php');
});


$app->route('test', '/den/:den', function (Request $request) {
	echo "Den: {$request['den']}";
})->validate_fields([':den' => '[0-9]+']);

$app->route('login', '/auth/login', function (Request $request) {
	$assertion = $_POST['assertion'];
	$audience = $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];

	$url = 'https://verifier.login.persona.org/verify';

	$http = new HTTP();
	$result = $http->post($url, compact('assertion', 'audience'));
	$result = json_decode($result);

	if($result->status = 'okay') {
		$_SESSION['user_email'] = $result->email;
	}
	else {
		unset($_SESSION['user_email']);
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

$app();


?>