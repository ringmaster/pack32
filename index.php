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

	public function selected($match, $value) {
		if($match == $value) {
			return 'selected';
		}
		return '';
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
		ORDER BY content.posted_on
		LIMIT 8";
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
			LIMIT 8;
		';
	}
	$response['upcoming'] = $app->db()->results($sql, ['now' => time()]);
	return $response->render('home.php');
});

$fetch_events = function(Response $response, Request $request, Pack32 $app) {
//	date_default_timezone_set('UTC');

	if(isset($request['year']) && isset($request['month'])) {
		$sel_date = new \DateTime(intval($request['year']) . '-' . intval($request['month']) . '-01' );
	}
	else {
		$sel_date = new \DateTime(gmdate('Y-m-1'));
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

	if(isset($request['all'])) {
		$start_date = new \DateTime('today -1 year');
		$end_date = new \DateTime('today +1 year');
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
		$groups_to_get = [1];
		//$groups_to_get = $app->db()->col('SELECT groups.id FROM groups WHERE groups.is_global = 1');
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

	$response['groups_to_get'] = $groups_to_get;
	$response['events'] = $app->db()->results(
		$sql,
		[
			'start_date' => $start_date->getTimestamp(),
			'end_date' => $end_date->getTimestamp(),
		]
	);
	foreach($response['events'] as &$event) {
		if($event['event_on'] == $event['event_end']) {
			if(date('Hi', $event['event_on']) == '0000') {
				$event['event_time'] = '';
			}
			else {
				$event['event_time'] = date('g:i a', $event['event_on']) . ' - ';
			}
		}
		elseif($event['event_end'] == 0) {
			$event['event_time'] = date('g:i a', $event['event_on']) . ' - ';
		}
		elseif(date('Hi', $event['event_on']) == '0000' && date('Hi', $event['event_end']) == '0000') {
			$event['event_time'] = '→' . date('M j', $event['event_end']) . ' - ';
		}
		else {
			$event['event_time'] = date('g:i a', $event['event_on']) . ' - ' . date('g:i a', $event['event_end']) . ' - ';
		}
		$event['groups'] = $app->db()->results('SELECT * FROM eventgroup INNER JOIN groups ON group_id = groups.id WHERE event_id = :event_id', ['event_id' => $event->id]);
	}
	$response['start_date'] = $start_date;
	$response['end_date'] = $end_date;
	$response['sel_date'] = $sel_date;
};

$calendar = function(Request $request, Response $response, Pack32 $app){
	$response['title'] = 'Calendar - ' . ORG_NAME . ' - Pickering Valley';

	$response['groups'] = $app->db()->results('SELECT * FROM groups ORDER BY name ASC');
	foreach($response['groups'] as &$group) {
		$group['selected'] = in_array($group['id'], $response['groups_to_get']) ? 'selected' : '';
	}
	return $response->render('calendar.php');
};

$app->route('calendar', '/calendar', $fetch_events, $calendar);
$app->route('calendar_date', '/calendar/:month/:year', $fetch_events, $calendar);

$ical = function(Request $request, Response $response, Pack32 $app) {

	$host = $_SERVER['HTTP_HOST'];
	$output = <<< VCAL_HEADER
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VTIMEZONE
TZID:Eastern Standard Time
BEGIN:STANDARD
DTSTART:16011104T020000
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=11
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:16010311T020000
RRULE:FREQ=YEARLY;BYDAY=2SU;BYMONTH=3
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
END:DAYLIGHT
END:VTIMEZONE

VCAL_HEADER;
	foreach($response['events'] as $event) {
		$event_on = gmdate('Ymd\THis\Z', $event['event_on']);
		$event_end = gmdate('Ymd\THis\Z', $event['event_end']);
		$posted_on = gmdate('Ymd\THis\Z', $event['posted_on']);
		$description = $event['content'];
		$description = explode("\n", wordwrap($description));
		$description = implode("\n ", $description);
		$textdescription = strip_tags($description);
		$statuses = [0=>'TENTATIVE', 1=>'TENTATIVE', 2=>'CONFIRMED', 3=>'CANCELLED'];
		$status = $statuses[$event['status']];
		$groups = [];
		foreach($event['groups'] as $group) {
			$groups[] = $group['name'];
		}
		$groups = implode(",", $groups);
		$output .= <<< VCAL_EVENT
BEGIN:VEVENT
CLASS:PUBLIC
CREATED:{$posted_on}
UID:{$event['id']}@{$host}
DTSTAMP:{$event_on}
DTSTART:{$event_on}
DTEND:{$event_end}
STATUS:$status
SUMMARY:{$event['title']}
CATEGORIES:{$groups}
DESCRIPTION:{$textdescription}
X-ALT-DESC;FMTTYPE=text/html:{$description}
END:VEVENT

VCAL_EVENT;

	}
	$output .= <<< VCAL_END
END:VCALENDAR

VCAL_END;

//	header('Content-type: text/calendar; charset=utf-8');
//	header('Content-Disposition: inline; filename="calendar.ics"');

	header('Content-type: text/plain; charset=utf-8');

	echo $output;
};

$app->route('ical', '/ical', function(Request $request){$request['all'] = true;}, $fetch_events, $ical);

$app->route('edit', '/admin/article/:id', function(Request $request, Response $response, Pack32 $app){
	$app->require_editing();

	$id = $request['id'];
	$post = $app->db()->row('SELECT * FROM content WHERE id = :id', compact('id'));
	if(!$post) {
		header('location: /');
		die('not found');
	}
	$post['groups'] = $app->db()->col('SELECT group_id FROM eventgroup WHERE event_id = :id', compact('id'));

	$response['start_date'] = $post['event_on'] ? date('Y-m-d', $post['event_on']) : '';
	$response['start_time'] = $post['event_on'] ? date('H:i', $post['event_on']) : '';
	$response['end_date'] = $post['event_end'] ? date('Y-m-d', $post['event_end']) : '';
	$response['end_time'] = $post['event_end'] ? date('H:i', $post['event_end']) : '';

	$response['global_groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global = 1 ORDER BY name');
	$response['groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global <> 1 ORDER BY name');
	$response['post'] = $post;
	$response['action'] = $app->get_url('edit_post', compact('id'));
	return $response->render('new.php');
})->get();

$app->route('edit_post', '/admin/article/:id', function(Request $request, Response $response, Pack32 $app){
	$app->require_editing();
	$id = $request['id'];
	$event_on = trim($_POST['start_date'] . ' ' . $_POST['start_time']) == '' ? 0 : strtotime($_POST['start_date'] . ' ' . $_POST['start_time']);
	$event_end = trim($_POST['end_date'] . ' ' . $_POST['end_time']) == '' ? $event_on : strtotime($_POST['end_date'] . ' ' . $_POST['end_time']);
	$due_on = isset($_POST['due_on']) ? strtotime($_POST['due_on']) : 0;

	$record = [
		'id' => $id,
		'title' => $_POST['title'],
		'content' => $_POST['content'],
		'content_type' => $_POST['content_type'],
		'due_on' => $due_on,
		'event_on' => $event_on,
		'event_end' => $event_end,
		'status' => $_POST['status'],
		'has_rsvp' => 0,
	];
	$app->db()->query('
		UPDATE content
		SET title = :title, content = :content, content_type = :content_type, due_on = :due_on, event_on = :event_on, event_end = :event_end, has_rsvp = :has_rsvp, status = :status
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

		$start_date = date('D, M j, Y', $article['event_on']);
		$end_date = date('D, M j, Y', $article['event_end']);
		if($start_date == $end_date) {
			if($article['event_on'] == $article['event_end']) {
				$event_date = $start_date;
			}
			else {
				$event_date = $start_date . ' ' . date('g:ip', $article['event_on']) . ' - ' . date('g:ip', $article['event_end']);
			}
		}
		elseif($article['event_end'] == 0) {
			$event_date = $start_date . ' ' . date('g:ip', $article['event_on']);
		}
		else {
			$event_date = $start_date . ' ' . date('g:ip', $article['event_on']) . ' - ' . $end_date . ' ' . date('g:ip', $article['event_end']);
		}

		$response['event_date'] = $event_date;

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
		'status' => 2,
	];

	$response['start_date'] = date('Y-m-d', isset($_GET['etime']) ? $_GET['etime'] : time());
	$response['start_time'] = '18:30';
	$response['end_date'] = date('Y-m-d', isset($_GET['etime']) ? $_GET['etime'] : time());
	$response['end_time'] = '20:00';

	$response['action'] = $app->get_url('add_new_post');
	$response['groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global = 0 ORDER BY name');
	$response['global_groups'] = $app->db()->results('SELECT * FROM groups WHERE is_global <> 0 ORDER BY name');
	return $response->render('new.php');
})->get();

function add_content(Request $request, Response $response, Pack32 $app) {
	$slug = trim(strtolower(preg_replace('#[^a-z0-9_]+#i', '-', $_POST['title'])), '-');
	$event_on = trim($_POST['start_date'] . ' ' . $_POST['start_time']) == '' ? 0 : strtotime($_POST['start_date'] . ' ' . $_POST['start_time']);
	$event_end = trim($_POST['end_date'] . ' ' . $_POST['end_time']) == '' ? $event_on : strtotime($_POST['end_date'] . ' ' . $_POST['end_time']);
	$due_on = isset($_POST['due_on']) ? strtotime($_POST['due_on']) : 0;

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
		'event_end' => $event_end,
		'status' => $_POST['status'],
		'has_rsvp' => 0,
	];
	$app->db()->query('
		INSERT INTO content
		(slug, title, content, user_id, posted_on, content_type, due_on, event_on, event_end, status, has_rsvp)
		VALUES
		(:slug, :title, :content, :user_id, :posted_on, :content_type, :due_on, :event_on, :event_end, :status, :has_rsvp)
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
	header('location: ' . $_SERVER['HTTP_REFERER']);
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