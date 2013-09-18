<?php include 'header.php'; ?>

<main id="content" class="event">
	<article class="post" data-article-id="<?= $article['id'] ?>">
		<?php if ($_app->can_edit()): ?>
			<div class="toolkit">
				<a class="edit modaldlg" href="<?= $_app->get_url('edit', $article) ?>#editor"><i class="icon-edit" title="edit article"></i></a>
				<a class="delete" href="<?= $_app->get_url('delete', $article) ?>"><i class="icon-trash" title="delete article"></i></a>
			</div>
		<?php endif; ?>
		<h1><?= $article['title'] ?></h1>
		<h2><?= $event_date ?></h2>

		<div class="groups">
			Participants:
			<?php foreach($groups as $group): ?>
			<span class="group <?= $group['is_global'] != 0 ? 'global' : '' ?>"><?= $group['name'] ?></span>
			<?php endforeach; ?>
		</div>

		<div class="content">
			<?= $article['content'] ?>
		</div>

		<div class="responses">
			<h2>Responses</h2>
		<?php if($_app->loggedin()) : ?>

			<div class="attachments">
				<ul style="width:<?= (count($attachments) + 3) * 100 ?>%;">
				<?php foreach($attachments as $photo): ?>
					<li><img src="<?= $_app->get_url('get_file', $photo) ?>"></li>
				<?php endforeach; ?>
				</ul>
			</div>

			<div class="dropzone"><span class="notice">Drop images into this space from your computer or <span style="text-decoration: underline">click here</span> to attach them to this event.</span></div>

		<?php else: ?>
			<p>Responses are available only to logged-in users.  Please log in via the button on the toolbar at the top of the page to view and add responses.</p>
			<p>There are <?= count($attachments) ?> photos and <?= count($responses) ?> responses currently available to view on this event.</p>
		<?php endif; ?>
		</div>

	</article>
</main>

<script>
	$(function(){
		Dropzone.autoDiscover = false;
		$('.dropzone')
			.dropzone({
				url: '<?= $_app->get_url('attach_photo', ['event_id' => $article['id']]) ?>',
				acceptedFiles: 'image/*,application/pdf,video/*',
				paramName: 'file',
				clickable: true
			});
		Dropzone.forElement(".dropzone")
			.on('selectedfiles', function(){
				$('.dropzone .notice').hide();
				$('.dropzone .dz-success').remove();
			})
			.on('complete', function(){
				$('.attachments').load('# .attachments > *');
			})
	});
</script>

<?php include 'footer.php'; ?>

