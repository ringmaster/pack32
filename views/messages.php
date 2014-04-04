<?php include 'header.php'; ?>

<main id="content" class="messages">
	<section class="main">
		<h1>Latest Messages<?= $forgroup ?></h1>
		<table class="messages">
			<?php foreach($latest as $message): ?>
				<tr>
					<td><a href="<?= $_app->get_url('message_thread', $message) ?>"><?= $message['thread_title'] ?></a></td>
					<td><?= date('g:ia M j', $message['received_on']) ?></td>
					<td><?= $message['username'] ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</section>
	<section class="sidebar">
		<h2>Your Groups</h2>
		<ul>
		<?php foreach($usergroups as $group): ?>
			<li><a href="<?= $_app->get_url('messages_group', $group); ?>"><?= $group['group_name'] ?></a></li>
		<?php endforeach; ?>
		</ul>
	</section>
</main>

<?php include 'footer.php'; ?>
