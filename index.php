<?php

namespace Microsite;

use Microsite\DB\PDO\DB;

include 'microsite.phar';

session_start();

class Pack32 extends App {
	/**
	 * @return \Microsite\DB\PDO\DB
	 */
	public function db() {
		return $this->dispatch_object('db', func_get_args());
	}
}

$app = new Pack32();

// Assign a directory in which templates can be found for rendering
$app->template_dirs = [
	__DIR__ . '/views',
];

include 'includes/auth.php';

$buildmenu = function(Response $response) {
	$response['menu'] = [
		[
			'href' => '/',
			'title' => 'Home',
		],
		[
			'href' => '/calendar',
			'title' => 'Calendar',
		]
	];
	if($response['loggedin']) {
		$response['menu'][] = [
			'href' => 'javascript:navigator.id.logout()',
			'title' => 'Log Out',
			'class' => 'login',
		];
		$response['menu'][] = [
			'href' => 'javascript:add_content()',
			'title' => 'Add Content',
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
};

$app->route('home', '/', $authdata, $buildmenu, function (Response $response, Pack32 $app) {
	$response['title'] = 'Cub Scout Pack 32 - Pickering Valley - Events, News, Calendar, &amp; Communication Center';
	$response['articles'] = $app->db()->results('
		SELECT *
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			groups.is_global = 1
			AND content_type = "article"
		ORDER BY content.posted_on
		DESC LIMIT 5
	');
	$response['upcoming'] = $app->db()->results('
		SELECT *
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			groups.is_global = 1
			AND content_type = "event"
			AND event_on > :now
		ORDER BY content.posted_on ASC
		LIMIT 20;
	', ['now' => time()]);
	$response['app'] = $app;
	return $response->render('home.php');
});

$calendar = function(Request $request, Response $response, Pack32 $app){
	$response['title'] = 'Calendar - Cub Scout Pack 32 - Pickering Valley';

	date_default_timezone_set('UTC');

	if(isset($request['year']) && isset($request['month'])) {
		$sel_date = new \DateTime(intval($request['year']) . '-' . intval($request['month']) . '-01' );
	}
	else {
		$sel_date = new \DateTime(date('Y-m-1'));
	}

	$first_sunday = strtotime('first sunday of this month', $sel_date->getTimestamp());
	$start_date = new \DateTime('@' . $first_sunday);
	if(intval($start_date->format('j')) != 1) {
		$start_date->sub(\DateInterval::createFromDateString('+1 week'));
	}
	$end_date = new \DateTime('@' . strtotime('last saturday of this month', $sel_date->getTimestamp()));
	if(intval($end_date->format('j')) != intval($end_date->format('t'))) {
		$end_date->add(\DateInterval::createFromDateString('+1 week'));
	}

	if(isset($_GET['all'])) {
		$sql = 'SELECT *
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			content_type = "event"
			AND event_on BETWEEN :start_date AND :end_date
		ORDER BY content.posted_on';
	}
	elseif($response['loggedin']) {
		$usergroups = $app->db()->col('SELECT group_id FROM usergroup WHERE user_id = :user_id', ['user_id' => $response['user']['id']]);
		if($usergroups) {
			$usergroups = implode(',', $usergroups);
		}
		else {
			$usergroups = '0';
		}
		$sql = "SELECT *
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			(groups.is_global = 1 OR groups.id IN ({$usergroups}))
			AND content_type = 'event'
			AND event_on BETWEEN :start_date AND :end_date
		ORDER BY content.posted_on";
	}
	else {
		$sql = 'SELECT *
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			groups.is_global = 1
			AND content_type = "event"
			AND event_on BETWEEN :start_date AND :end_date
		ORDER BY content.posted_on';
	}

	$response['events'] = $app->db()->results(
		$sql,
		[
			'start_date' => $start_date->getTimestamp(),
			'end_date' => $end_date->getTimestamp(),
		]
	);
	$response['start_date'] = $start_date;
	$response['end_date'] = $end_date;
	$response['sel_date'] = $sel_date;
	$response['app'] = $app;
	return $response->render('calendar.php');
};

$app->route('calendar', '/calendar', $authdata, $buildmenu, $calendar);
$app->route('calendar_date', '/calendar/:month/:year', $authdata, $buildmenu, $calendar);

$app->route('edit_article', '/admin/article', $authdata, function(){
	$id = $_POST['id'];
	$title = $_POST['title'];
	$content = $_POST['content'];

	var_dump($_POST);

})->post();

$app->share('db', function() {
	$db = new DB('mysql:host=localhost;dbname=pack32', 'root', '');
	return $db;
});

$app->route('test', '/den/:den', function (Request $request) {
	echo "Den: {$request['den']}";
})->validate_fields([':den' => '[0-9]+']);

$app->route('event', '/events/:slug', function(Request $request) {
	echo "Slug: {$request['slug']}";
});

$app->route('add_new', '/admin/new', $authdata, function(Request $request, Response $response, Pack32 $app) {
	if(!$response['loggedin']) {
		header('location: /');
		die();
	}
	$response['app'] = $app;
	$response['groups'] = $app->db()->results('SELECT * FROM groups ORDER BY is_global = 0, name');
	return $response->render('new.php');
})->get();

function add_content(Request $request, Response $response, Pack32 $app) {
	$slug = trim(strtolower(preg_replace('#[^a-z0-9_]+#i', '-', $_POST['title'])), '-');
	$event_on = strtotime($_POST['event_on']);
	$due_on = strtotime($_POST['due_on']);

	// Get a clean slug
	$exists = $app->db()->val('SELECT id FROM content WHERE slug = :slug', ['slug' => $slug]);
	if ($exists) {
		if($event_on != 0) {
			$try = date('Y-m-d', $event_on);
		}
		else {
			$try = 1;
		}
		while($app->db()->val('SELECT id FROM content WHERE slug = :slug', ['slug' => $slug . '-' . $try]) != false) {
			if(is_string($try)) {
				$try = 1;
			}
			else {
				$try++;
			}
		}
		$slug = $slug . '-' . $try;
	}

	$record = [
		'slug' => $slug,
		'title' => $_POST['title'],
		'content' => $_POST['content'],
		'user_id' => $response['user']['id'],
		'posted_on' => time(),
		'content_type' => $_POST['content_type'],
		'due_on' => $due_on,
		'event_on' => $event_on,
		'has_rsvp' => 0,
	];
	$app->db()->query('
		INSERT INTO content
		(slug, title, content, user_id, posted_on, content_type, due_on, event_on, has_rsvp)
		VALUES
		(:slug, :title, :content, :user_id, :posted_on, :content_type, :due_on, :event_on, :has_rsvp)
	',
		$record);
	$record['id'] = $app->db()->lastInsertId();

	if(isset($_POST['group'])) {
		if(!is_array($_POST['group'])) {
			$_POST['group'] = array($_POST['group']);
		}
		foreach($_POST['group'] as $group_id) {
			$app->db()->query('INSERT INTO eventgroup (group_id, event_id) VALUES (:group_id, :event_id)', ['group_id' => $group_id, 'event_id' => $record['id']]);
		}
	}

	return $record;
}


$app->route('add_new_post', '/admin/new', $authdata, function(Request $request, Response $response, Pack32 $app) {
	if(!$response['loggedin']) {
		header('location: /');
		die();
	}
	$record = add_content($request, $response, $app);
	header('location: ' . $app->get_url('add_new'));
	return 'ok';
})->post();

$app->route('paste_photo', '/admin/photo', $authdata, function(Request $request, Response $response, Pack32 $app) {
	$dir = __DIR__ . '/data/';

	$contentType = $_POST['contentType'];
	$data = base64_decode($_POST['data']);

	$filename = md5(date('YmdHis')) . '.png';
	$file = $dir . $filename;

	file_put_contents($file, $data);

	echo json_encode(array('filelink' => '/data/' .$filename));
});

$app->route('upload_photo', '/admin/photo', $authdata, function(Request $request, Response $response, Pack32 $app) {
	if(!$response['loggedin']) {
		header('location: /');
		die();
	}

	// files storage folder
	$dir = __DIR__ . '/data/';

	$_FILES['file']['type'] = strtolower($_FILES['file']['type']);

	if ($_FILES['file']['type'] == 'image/png'
		|| $_FILES['file']['type'] == 'image/jpg'
		|| $_FILES['file']['type'] == 'image/gif'
		|| $_FILES['file']['type'] == 'image/jpeg'
		|| $_FILES['file']['type'] == 'image/pjpeg')
	{
		// setting file's mysterious name
		do {
			$file = $dir . md5(date('YmdHis')).'.jpg';
		} while(file_exists($file));

		// copying
		move_uploaded_file($_FILES['file']['tmp_name'], $file);

		// displaying file
		$array = array(
			'filelink' => '/data/' . $file
		);

		echo stripslashes(json_encode($array));
	}
});

$app();


?>