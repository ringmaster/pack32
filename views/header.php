<html>
<head>
	<title><?= $title ?></title>
	<link rel="stylesheet" href="/css/pack32.css" type="text/css">
	<link rel="stylesheet" href="/js/redactor/redactor.css" type="text/css">
	<link rel="stylesheet" href="/js/select2/select2.css" type="text/css">
	<link rel="stylesheet" href="/js/messenger/css/messenger.css" type="text/css">
	<link rel="stylesheet" href="/js/messenger/css/messenger-theme-future.css" type="text/css">
	<link rel="stylesheet" href="/js/messenger/css/messenger-spinner.css" type="text/css">
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" type="text/css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<!--[if lt IE 9]>
	<script src="/js/html5shiv.js"></script>
	<![endif]-->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	<script src="https://login.persona.org/include.js"></script>
	<script src="/js/messenger/js/messenger.min.js"></script>
	<script src="/js/messenger/js/messenger-theme-future.js"></script>
</head>
<body>
<div class="wrapper">
	<header id="header">
		<h1><a href="/">Cub Scout Pack 32</a></h1>
	</header>
</div>
<nav id="nav">
	<ol>
		<?php foreach ($menu as $item): ?>
			<li class="<?= isset($item['class']) ? $item['class'] : '' ?>"><a href="<?= $item['href'] ?>"><?= $item['title'] ?></a>
				<?php if(isset($item['submenu'])): ?>
				<ol class="submenu">
					<?php foreach($item['submenu'] as $subitem): ?>
					<li class="<?= isset($subitem['class']) ? $subitem['class'] : '' ?>"><a href="<?= $subitem['href'] ?>"><?= $subitem['title'] ?></a></li>
					<?php endforeach; ?>
				</ol>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
<div class="wrapper">
