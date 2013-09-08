<html>
<head>
	<title>Add New Content</title>
	<link rel="stylesheet" href="/css/pack32.css" type="text/css">
	<link rel="stylesheet" href="/js/redactor/redactor.css" type="text/css">
	<link rel="stylesheet" href="/js/select2/select2.css" type="text/css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<!--[if lt IE 9]>
	<script src="dist/html5shiv.js"></script>
	<![endif]-->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="https://login.persona.org/include.js"></script>
</head>
<body class="editor_page">

<main id="editor">


	<form method="post" action="<?= $app->get_url('add_new_post') ?>">

		<label for="new_title">Title</label>
		<input id="new_title" name="title" type="text" placeholder="Title" class="input-1">

		<fieldset>
			<label><input type="radio" name="content_type" value="article"> Article</label>
			<label><input type="radio" name="content_type" value="event" checked> Event</label>
		</fieldset>

		<select multiple name="group" id="new_group" class="input-1">
			<?php foreach($groups as $group): ?>
			<option value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
			<?php endforeach; ?>
		</select>

		<textarea id="new_content" name="content" class="input-1" style="min-height:200px;"></textarea>

		<fieldsset>
			<label for="new_event_on">Event On</label>
			<input type="date" name="event_on" id="new_event_on">
		</fieldsset>

		<input type="submit" class="button" value="Save &amp; Send">
	</form>

</main>

<script src="/js/redactor/redactor.min.js"></script>
<script src="/js/select2/select2.min.js"></script>
<script>
	$(function(){
		$('#new_group').select2();
		$('#new_content').redactor({
			minHeight: 200
		});
	});
</script>

</body>
</html>