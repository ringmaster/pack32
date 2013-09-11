<?php

namespace Microsite;

use Microsite\DB\PDO\DB;

include 'microsite.phar';

session_start();

define('ORG_NAME', 'Cub Scout Pack 32 - Pickering Valley');

class Pack32 extends App {
	/**
	 * @return \Microsite\DB\PDO\DB
	 */
	public function db() {
		return $this->dispatch_object('db', func_get_args());
	}

	public function require_login() {
		if(!$this->loggedin()) {
			header('location: /');
			die('Must be logged in.');
		};
	}

	public function loggedin() {
		return $this->response()['loggedin'];
	}

	public function require_editing() {
		if(!$this->can_edit()) {
			header('location: /');
			die('Must be logged in.');
		}
	}

	public function can_edit() {
		return $this->response()['user']['admin_level'] > 0;
	}

	public function profile_complete() {
		if($this->loggedin()) {
			return $this->db()->val('SELECT count(*) FROM usergroup WHERE user_id = :id', ['id' => $this->response()['user']['id']]) > 0;
		}
		else {
			return true;
		}
	}

	public function add_message($message, $type='info') {
		if(!isset($_SESSION['messages'])) {
			$_SESSION['messages'] = [];
		}
		$_SESSION['messages'][] = compact('message', 'type');
	}
}

$app = new Pack32();

// Assign a directory in which templates can be found for rendering
$app->template_dirs = [
	__DIR__ . '/views',
];

include 'includes/auth.php';

$app->middleware('menu', function(Response $response, Pack32 $app) {
	$response['menu'] = [
		[
			'href' => '/',
			'title' => 'Home',
		],
		[
			'href' => '/calendar',
			'title' => 'Calendar',
		],
		[
			'href' => '/documents',
			'title' => 'Documents',
		]
	];
	$response['submenu'] = [];
	if($app->loggedin()) {
		$response['menu']['options'] = [
			'href' => '#options',
			'title' => 'options <i class="icon-collapse"></i>',
			'class' => 'login toggle_menu',
			'submenu' => [
				[
					'href' => '/profile',
					'title' => 'Your Profile',
					'class' => '',
				],
				[
					'href' => 'javascript:navigator.id.logout()',
					'title' => 'Log Out',
				],
			],
		];
		if($app->can_edit()) {
			array_unshift($response['menu']['options']['submenu'], [
				'href' => '/admin/new#editor',
				'title' => 'Add Content',
				'class' => 'modaldlg',
			]);
		}
	}
	else {
		$response['menu'][] = [
			'href' => 'javascript:navigator.id.request()',
			'title' => 'Log In',
			'class' => 'login',
		];
	}
});



$app->route('home', '/', function (Response $response, Pack32 $app) {
	$response['title'] = ORG_NAME . ' - Events, News, Calendar, &amp; Communication Center';
	$response['articles'] = $app->db()->results('
		SELECT *, content.id as id
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
	if($app->loggedin()) {
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
			AND event_on > :now
		ORDER BY content.posted_on";
	}
	else {
		$sql = '
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
		';
	}
	$response['upcoming'] = $app->db()->results($sql, ['now' => time()]);
	return $response->render('home.php');
});

$calendar = function(Request $request, Response $response, Pack32 $app){
	$response['title'] = 'Calendar - ' . ORG_NAME . ' - Pickering Valley';

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

	$groups_to_get = [];
	if($app->loggedin()) {
		$groups_to_get = $app->db()->col('SELECT groups.id FROM groups LEFT JOIN usergroup ON groups.id = usergroup.group_id WHERE groups.is_global = 1 OR usergroup.user_id = :user_id', ['user_id' => $response['user']['id']]);
	}
	if(isset($_GET['groups'])) {
		$groups_to_get = array_filter(array_map('intval', explode(',', $_GET['groups'])));
	}
	if(isset($_POST['groups'])) {
		$groups_to_get = array_filter(array_map('intval', $_POST['groups']));
	}
	if(count($groups_to_get) == 0 && !isset($_GET['groups']) && !isset($_POST['groups'])) {
		$groups_to_get = $app->db()->col('SELECT groups.id FROM groups WHERE groups.is_global = 1');
	}

	if(isset($_GET['all'])) {
		$sql = 'SELECT *, content.id as id
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			content_type = "event"
			AND event_on BETWEEN :start_date AND :end_date
		GROUP BY content.id
		ORDER BY content.posted_on';
	}
	else {
		if($groups_to_get) {
			$usergroups = implode(',', $groups_to_get);
		}
		else {
			$usergroups = '0';
		}
		$sql = "SELECT *, content.id as id
		FROM content
		INNER JOIN
		  eventgroup ON eventgroup.event_id = content.id
		INNER JOIN
			groups ON groups.id = eventgroup.group_id
		WHERE
			groups.id IN ({$usergroups})
			AND content_type = 'event'
			AND event_on BETWEEN :start_date AND :end_date
		GROUP BY content.id
		ORDER BY content.posted_on";
	}

	$response['events'] = $app->db()->results(
		$sql,
		[
			'start_date' => $start_date->getTimestamp(),
			'end_date' => $end_date->getTimestamp(),
		]
	);

	foreach($response['events'] as &$event) {
		$event['groups'] = $app->db()->results('SELECT * FROM eventgroup INNER JOIN groups ON group_id = groups.id WHERE event_id = :event_id', ['event_id' => $event->id]);
	}
	$response['groups'] = $app->db()->results('SELECT * FROM groups ORDER BY name ASC');
	foreach($response['groups'] as &$group) {
		$group['selected'] = in_array($group['id'], $groups_to_get) ? 'selected' : '';
	}
	$response['start_date'] = $start_date;
	$response['end_date'] = $end_date;
	$response['sel_date'] = $sel_date;
	$response['groups_to_get'] = $groups_to_get;
	return $response->render('calendar.php');
};

$app->route('calendar', '/calendar', $calendar);
$app->route('calendar_date', '/calendar/:month/:year', $calendar);

$app->route('edit', '/admin/article/:id', function(Request $request, Response $response, Pack32 $app){
	$app->require_editing();

	$id = $request['id'];
	$post = $app->db()->row('SELECT * FROM content WHERE id = :id', compact('id'));
	if(!$post) {
		header('location: /');
		die('not found');
	}
	$post['groups'] = $app->db()->col('SELECT group_id FROM eventgroup WHERE event_id = :id', compact('id'));

	$response['global_groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global = 1 ORDER BY name');
	$response['groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global <> 1 ORDER BY name');
	$response['post'] = $post;
	$response['action'] = $app->get_url('edit_post', compact('id'));
	return $response->render('new.php');
})->get();

$app->route('edit_post', '/admin/article/:id', function(Request $request, Response $response, Pack32 $app){
	$app->require_editing();
	$id = $request['id'];
	$event_on = strtotime($_POST['event_on']);
	$due_on = strtotime($_POST['due_on']);

	$record = [
		'id' => $id,
		'title' => $_POST['title'],
		'content' => $_POST['content'],
		'content_type' => $_POST['content_type'],
		'due_on' => $due_on,
		'event_on' => $event_on,
		'has_rsvp' => 0,
	];
	$app->db()->query('
		UPDATE content
		SET title = :title, content = :content, content_type = :content_type, due_on = :due_on, event_on = :event_on, has_rsvp = :has_rsvp
		WHERE id = :id
	',
		$record);

	$app->db()->query('DELETE FROM eventgroup WHERE event_id = :id', compact('id'));
	if(isset($_POST['group'])) {
		if(!is_array($_POST['group'])) {
			$_POST['group'] = array($_POST['group']);
		}
		foreach($_POST['group'] as $group_id) {
			$app->db()->query('INSERT INTO eventgroup (group_id, event_id) VALUES (:group_id, :event_id)', ['group_id' => $group_id, 'event_id' => $id]);
		}
	}
	$app->add_message('Updated content.', 'success');
	header('location: ' . $_SERVER['HTTP_REFERER']);
	die('updated');

})->post();

$app->share('db', function() {
	$db = new DB('mysql:host=localhost;dbname=pack32', 'root', '');
	return $db;
});

$app->route('test', '/den/:den', function (Request $request) {
	echo "Den: {$request['den']}";
})->validate_fields([':den' => '[0-9]+']);

$app->route('event', '/events/:slug', function(Request $request, Response $response, Pack32 $app) {
	$article = $app->db()->row('SELECT * FROM content WHERE slug = :slug', ['slug' => $request['slug']]);
	if($article) {
		$response['article'] = $article;
		$response['groups'] = $app->db()->results('SELECT * FROM eventgroup INNER JOIN groups ON group_id = groups.id WHERE event_id = :event_id', ['event_id' => $article->id]);
		$response['title'] = $article['title'] . ' - ' . ORG_NAME;
		return $response->render('event.php');
	}
	header('location: /');
	return 'not found';
});

$app->route('documents', '/documents', function(Request $request, Response $response, Pack32 $app) {
	$article = $app->db()->row('SELECT * FROM content WHERE slug = "documents"');
	$response['article'] = $article;
	$response['title'] = $article['title'] . ' - ' . ORG_NAME;
	return $response->render('documents.php');
});

$app->route('delete', '/admin/delete/:id', function(Request $request, Response $response, Pack32 $app) {
	$app->require_editing();
	$app->db()->query('DELETE FROM content WHERE id = :id', ['id' => $request['id']]);
	header('location: /');
	echo "deleted";
});

$app->route('add_new', '/admin/new', function(Request $request, Response $response, Pack32 $app) {
	$app->require_editing();
	$user_groups = $app->db()->col('SELECT group_id FROM usergroup WHERE user_id = :user_id', ['user_id' => $response['user']['id']]);
	$response['post'] = [
		'title' => '',
		'content_type' => 'event',
		'content' => '',
		'event_on' => isset($_GET['etime']) ? $_GET['etime'] : time(),
		'groups' => $user_groups ? $user_groups : [],
	];
	$response['action'] = $app->get_url('add_new_post');
	$response['groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global = 0 ORDER BY name');
	$response['global_groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global <> 0 ORDER BY name');
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


$app->route('add_new_post', '/admin/new', function(Request $request, Response $response, Pack32 $app) {
	$app->require_editing();
	$record = add_content($request, $response, $app);
	header('location: ' . $app->get_url('add_new'));
	$app->add_message('Added content.', 'success');
	return 'ok';
})->post();

$app->route('paste_photo', '/admin/paste', function(Request $request, Response $response, Pack32 $app) {
	$dir = __DIR__ . '/data/';

	$data = base64_decode($_POST['data']);

	$filename = md5(date('YmdHis')) . '.png';
	$file = $dir . $filename;

	file_put_contents($file, $data);

	echo json_encode(array('filelink' => '/data/' .$filename));
});

$app->route('upload_photo', '/admin/upload/photo', function(Request $request, Response $response, Pack32 $app) {
	$app->require_editing();

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
			'filelink' => '/data/' . basename($file)
		);

		echo json_encode($array);
	}
});

$app->route('upload_file', '/admin/upload/file', function(Request $request, Response $response, Pack32 $app) {
	$app->require_editing();

	// files storage folder
	$dir = __DIR__ . '/data/';

	// setting file's mysterious name
	list($name, $ext) = preg_split('#\.(?=[^\.]+$)#', $_FILES['file']['name']);
	$try = '';
	do {
		$file = $dir . $name . $try . '.' . $ext;
		$try = empty($try) ? 1 : intval($try) + 1;
	} while(file_exists($file));

	// copying
	move_uploaded_file($_FILES['file']['tmp_name'], $file);

	// displaying file
	$array = [
		'filelink' => '/data/' . basename($file),
		'filename' => $name . '.' . $ext,
	];

	echo json_encode($array);
});

$app->route('profile', '/profile', function(Request $request, Response $response, Pack32 $app){
	$app->require_login();
	$response['title'] = 'Your Profile - ' . ORG_NAME;
	$response['groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global = 0 ORDER BY name');
	$user_id = $response['user']['id'];
	$response['subscribed'] = $app->db()->results('SELECT groups.id, groups.name as group_name, usergroup.id as ug_id, usergroup.name FROM groups INNER JOIN usergroup ON groups.id = usergroup.group_id WHERE groups.is_global = 0 AND user_id = :user_id ORDER BY groups.name', compact('user_id'));
	return $response->render('profile.php');
})->get();

$app->route('profile_post', '/profile', function(Request $request, Response $response, Pack32 $app){
	$app->require_login();

	$user_id = $response['user']['id'];

	$name = $_POST['profile_name'];
	$app->db()->query('UPDATE users SET username = :name WHERE id = :user_id', compact('name', 'user_id'));

	$app->db()->query('DELETE FROM usergroup WHERE user_id = :user_id', compact('user_id'));
	if(isset($_POST['usergroup'])) {
		foreach($_POST['usergroup'] as $member) {
			if(isset($member['subscribed']) && $member['subscribed'] == 'true') {
				$name = $member['name'];
				$group_id = $member['group_id'];
				$app->db()->query('INSERT INTO usergroup (name, group_id, user_id) values (:name, :group_id, :user_id)', compact('name', 'group_id', 'user_id'));
			}
		}
	}

	if(isset($_POST['new_member']) && $_POST['new_member'] == 'true') {
		$name = $_POST['new_member_name'];
		$group_id = $_POST['new_member_group'];
		$app->db()->query('INSERT INTO usergroup (name, group_id, user_id) values (:name, :group_id, :user_id)', compact('name', 'group_id', 'user_id'));
	}
	$app->add_message('Updated profile.', 'success');

	header('location: ' . $app->get_url('profile'));
	die('redirecting');
})->post();

$app();


?>