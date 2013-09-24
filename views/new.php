<html>
<head>
	<title>Add New Content</title>
	<link rel="stylesheet" href="/theme/pack32/pack32.css" type="text/css">
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

		<textarea id="new_content" name="content" class="input-1" style="min-height:200px;"><?= htmlspecialchars($post['content'], ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8') ?></textarea>

		<fieldsset>
			<legend>Event Dates</legend>
			<label for="new_start_date">Start Date</label>
			<input type="date" name="start_date" id="new_start_date" value="<?= $start_date ?>">
			<label for="new_start_time">Start Time</label>
			<input type="time" name="start_time" id="new_start_time" value="<?= $start_time ?>">
			<br>

			<label for="new_end_date">End Date</label>
			<input type="date" name="end_date" id="new_end_date" value="<?= $end_date ?>">
			<label for="new_end_date">End Time</label>
			<input type="time" name="end_time" id="new_end_time" value="<?= $end_time ?>">
		</fieldsset>

		<fieldset>
			<legend>Extended Information</legend>
			<label for="new_status">Status:</label>
			<select id="new_status" name="status">
				<option value="1" <?= $_app->selected(1, $post['status']) ?>>Tentative</option>
				<option value="2" <?= $_app->selected(2, $post['status']) ?>>Confirmed</option>
				<option value="3" <?= $_app->selected(3, $post['status']) ?>>Canceled</option>
			</select>
			<label for="has_rsvp"><input id="has_rsvp" type="checkbox" name="has_rsvp" value="1" <?= $_app->checked(1, $post['has_rsvp']) ?>> Requires RSVP</label>

		</fieldset>

	</form>

</main>

<script src="/js/redactor/redactor.min.js"></script>
<script src="/js/select2/select2.min.js"></script>
<script>
	$(function(){
		$('#new_group').select2();
		$('#new_content').redactor({
			minHeight: 200,
			imageUpload: '<?= $_app->get_url('upload_photo') ?>',
			clipboardUploadUrl: '<?= $_app->get_url('paste_photo') ?>'
		});
	});
</script>

</body>
</html>