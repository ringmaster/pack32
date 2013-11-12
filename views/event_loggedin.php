<?php include 'header.php'; ?>

<main id="content" class="event">
	<section class="main">
		<article class="post" data-article-id="<?= $article['id'] ?>">
			<?php if ($_app->can_edit()): ?>
				<div class="toolkit">
					<a class="edit modaldlg" href="<?= $_app->get_url('edit', $article) ?>#editor"><i class="icon-edit"
																																														title="edit article"></i></a>
					<a class="delete" href="<?= $_app->get_url('delete', $article) ?>"><i class="icon-trash"
																																								title="delete article"></i></a>
				</div>
			<?php endif; ?>
			<h1><?= $article['title'] ?></h1>

			<h2><?= $event_date ?></h2>

			<div class="groups">
				Participants:
				<?php foreach ($groups as $group): ?>
					<span class="group <?= $group['is_global'] != 0 ? 'global' : '' ?>"><?= $group['name'] ?></span>
				<?php endforeach; ?>
			</div>

			<div class="content">
				<?= $article['content'] ?>
			</div>

			<div class="responses">

				<div class="comments">
				<?php if(count($responses) > 0): ?>

					<h2>Responses</h2>

					<?php foreach($responses as $response): ?>
						<aside>

						<h3>By <?= $response['username'] ?> on <?= date('M j, Y', $response['added_on']) ?></h3>

							<div>
								<?= $response['content']; ?>
							</div>

						</aside>
					<?php endforeach; ?>

				<?php endif; ?>

				</div>

				<h2>Photos</h2>

				<div class="attachments <?= count($attachments) > 0 ? '' : 'empty' ?>">
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
								<a href="<?= $_app->get_url('get_file', $photo) ?>"><img
										src="<?= $_app->get_url('get_thumbnail', $photo) ?>"></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="dropzone">
					<div class="dz-default dz-message">Drop images into this space from your computer or <span
							style="text-decoration: underline;display:inline;">click here</span> to attach them to this event.
					</div>
				</div>
			</div>

		</article>
	</section>
	<section class="sidebar">
		<h2>Event RSVP</h2>

		<?php if ($article['has_rsvp'] == 0): ?>
			<p>RSVP for this event is not required.</p>
		<?php else: ?>
			<p>You must RSVP to attend this event.</p>

			<?php if (count($members) == 0): ?>
				<p>Please <a href="<?= $_app->get_url('profile') ?>">edit your profile</a> to add members that can RSVP to this
					event. </p>

			<?php else: ?>
				<p>Please select the members of this group who will attend:</p>
				<form action="<?= $_app->get_url('rsvp_post', $article) ?>" method="POST">
					<table class="rsvp">
						<thead>
						<tr>
							<th>Name</th>
							<?php if($_response['user']['admin_level'] > 0): ?>
								<th>?</th>
							<?php endif; ?>
							<th>No</th>
							<th>Yes</th>
						</tr>
						</thead>
						<tbody>
						<?php $other_toggle = false; foreach ($members as $member): ?>
							<?php
							if($_response['user']['admin_level'] > 0 && $member['account_id'] != $_response['user']['account_id'] && !$other_toggle) {
								$other_toggle = true;
								?>
								<tr class="other_toggle"><td colspan="4"><a href="#edit_all">Toggle display of all families</a></td></tr>
								<?php
							}
							?>
							<tr class="<?= $member['role'] == 0 ? 'parent' : 'other' ?> <?= $member['account_id'] == $_response['user']['account_id'] ? 'your_family' : 'other_family' ?>">
								<th><i class="<?= $member['role'] == 0 ? 'icon-group' : 'icon-user' ?>"></i> <?= $member['name'] ?>

								<span class="group <?= $member['is_global'] == 1 ? 'global' : '' ?>"><?= $member['group_name'] ?></span>
								</th>

								<?php if($_response['user']['admin_level'] > 0): ?>
								<td><input class="rsvp" type="radio" name="rsvp[<?= $member['id'] ?>]"
													 value="0" <?= $_app->checked($member['is_rsvp'], 0) ?>></td>
								<?php endif; ?>

								<td><input class="rsvp" type="radio" name="rsvp[<?= $member['id'] ?>]"
													 value="1" <?= $_app->checked($member['is_rsvp'], 1) ?>></td>
								<td><input class="rsvp" type="radio" name="rsvp[<?= $member['id'] ?>]"
													 value="2" <?= $_app->checked($member['is_rsvp'], 2) ?>></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</form>
				<p style="font-size:smaller;font-weight:bold;">RSVP Notes:</p>
				<ol style="font-size:smaller;">
					<li>It may be required for both a parent and scount to attend -- refer to the event description.</li>
					<li>If a name you expect to see in this list is missing, <a href="<?= $_app->get_url('profile') ?>">check your profile</a> to ensure that a member is associated to this group.</li>
				</ol>

			<?php endif; ?>

			<div id="attendees">
				<h2><?= $rsvp_count ?> Attendees</h2>

				<ol class="attendees">
				<?php
				$last_role = 0;
				foreach ($rsvps as $account):
					$first = array_shift($account);
					?>
					<li class="<?= $first['role'] == 0 ? 'parent' : 'other' ?>"><i class="<?= $first['role'] == 0 ? 'icon-group' : 'icon-user' ?>"></i> <?= $first['name'] ?>
					<?php
					if(count($account)){
						if($first['role'] == 0) echo '<ol>';
						foreach($account as $member):
							?>
							<li class="<?= $member['role'] == 0 ? 'parent' : 'other' ?>"><i class="<?= $member['role'] == 0 ? 'icon-group' : 'icon-user' ?>"></i> <?= $member['name'] ?></li>
							<?php
						endforeach;
						if($first['role'] == 0) echo '</ol>';
					}
					?>
					</li>
					<?php
				endforeach;
				?>
				</ol>
			</div>


		<?php endif; ?>

	</section>
</main>

<script>
	$(function ()
	{
		$('.dropzone')
			.dropzone({
				url: '<?= $_app->get_url('attach_photo', ['event_id' => $article['id']]) ?>',
				acceptedFiles: 'image/*,application/pdf,video/*',
				paramName: 'file',
				clickable: true
			});
		if( $('.dropzone').length ) {
			Dropzone.forElement(".dropzone")
				.on('selectedfiles', function ()
				{
					$('.dropzone .notice').hide();
					$('.dropzone .dz-success').remove();
				})
				.on('complete', function ()
				{
					$('.attachments').load('# .attachments > *').removeClass('empty');
				})
		}

		$(document).on('click', '.deactivate a,.activate a', function (ev)
		{
			var href = $(this).attr('href');
			$.post(href, function ()
			{
				$('.attachments').load('# .attachments > *');
			});

			ev.preventDefault();
		});

		$(document).on('change', 'input.rsvp', function(){
			var $form = $(this).closest('form');
			var $input = $(this);
			var action = $form.attr('action');
			params = {};
			params[$input.attr('name')] = $input.val();
			$.post(
				action,
				params,
				function(result){
					Messenger().post(result);
					$('#attendees').load(location.href + ' #attendees > *');
				}
			)
		});

		$(document).on('click', '.other_toggle a', function(){
			$('.other_family').toggle();
		});
	});
</script>

<?php include 'footer.php'; ?>
