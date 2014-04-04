<?php include 'header.php'; ?>

<main id="content" class="messages">
	<section class="main">
		<h1>Message: <?= $thread['title'] ?></h1>
		<table>
		<?php foreach($messages as $message): ?>
			<tr class="message">
				<td class="metadata"><span class="author"><?= $message->username ?></span>
					<span class="time"><?= date('g:ia', $message->received_on) ?></span>
					<span class="date"><?= date('M j, Y', $message->received_on) ?></span>
				</td>
				<td class="message_content">
					<?php
					$email = new Email($message->content);
					echo $email->render();
					?>
				</td>
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
