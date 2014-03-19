<?php include 'header.php'; ?>

<main id="content" class="photos">

	<h1>Photos</h1>

	<?php foreach ($events as $event_id => $attachments): ?>

		<article>
			<h2><a href="<?= $_app->get_url('event', ['id'=>$event_id, 'slug'=>$event_data[$event_id]['slug']]); ?>"><?= $event_data[$event_id]['title'] ?></a></h2>

			<div class="attachments small <?= count($attachments) > 0 ? '' : 'empty' ?>">
				<ul style="width:<?= (count($attachments) + 3) * 100 ?>%;">
					<?php foreach ($attachments as $photo): ?>
						<li class="<?= $photo['active'] == 1 ? 'active' : 'inactive' ?>"><?php if ($photo['deactivate']): ?>
								<?php if ($photo['active'] == 1) { ?>
									<span class="deactivate"><a href="<?= $_app->get_url('deactivate_attachment', $photo) ?>"><i
												class="icon-trash"></i></a></span>
								<?php
								}
								else {
									?>
									<span class="activate"><a href="<?= $_app->get_url('reactivate_attachment', $photo) ?>">DELETED
											<small>This image is queued for deletion. It is not visible to others. Click here to restore.
											</small></span></a>
								<?php } ?>
							<?php endif; ?>
							<a href="<?= $_app->get_url('get_file', $photo) ?>" class="orig_link"><img
									src="<?= $_app->get_url('get_thumbnail', $photo) ?>"></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

		</article>
	<?php endforeach; ?>


</main>

<?php include 'footer.php'; ?>

