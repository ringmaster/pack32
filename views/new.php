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


	<form method="post" action="<?= $action ?>">

		<label for="new_title">Title</label>
		<input id="new_title" name="title" type="text" placeholder="Title" class="input-1" value="<?= $post['title'] ?>">

		<fieldset>
			<label><input type="radio" name="content_type" value="article" <?= $post['content_type'] == 'article' ? 'checked' : '' ?>> Article</label>
			<label><input type="radio" name="content_type" value="event" <?= $post['content_type'] == 'event' ? 'checked' : '' ?>> Event</label>
		</fieldset>

		<select multiple name="group[]" id="new_group" class="input-1">
			<optgroup label="Public">
			<?php foreach($global_groups as $group): ?>
					<option value="<?= $group['id'] ?>" <?= in_array($group['id'], $post['groups']) ? 'selected' : '' ?>><?= $group['name'] ?></option>
			<?php endforeach; ?>
			</optgroup>
			<optgroup label="Private">
				<?php foreach($groups as $group): ?>
					<option value="<?= $group['id'] ?>" <?= in_array($group['id'], $post['groups']) ? 'selected' : '' ?>><?= $group['name'] ?></option>
				<?php endforeach; ?>
			</optgroup>
		</select>

		<textarea id="new_content" name="content" class="input-1" style="min-height:200px;"><?= htmlspecialchars($post['content']) ?></textarea>

		<fieldsset>
			<label for="new_event_on">Event On</label>
			<input type="date" name="event_on" id="new_event_on" value="<?= date('Y-m-d', $post['event_on']) ?>">
		</fieldsset>

	</form>

</main>

<script src="/js/redactor/redactor.min.js"></script>
<script src="/js/select2/select2.min.js"></script>
<script>
	$(function(){
		$('#new_group').select2();
		$('#new_content').redactor({
			minHeight: 200,
			imageUpload: '<?= $app->get_url('upload_photo') ?>',
			clipboardUploadUrl: '<?= $app->get_url('paste_photo') ?>'
		});
	});
</script>

</body>
</html>