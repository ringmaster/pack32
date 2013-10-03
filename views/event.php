<?php include 'header.php'; ?>

<main id="content" class="event">
	<article class="post" data-article-id="<?= $article['id'] ?>">

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
			<h2>Responses</h2>

			<p>Photos and responses are available only to logged-in users. Please log in via the button on the toolbar at the
				top of the page to view and add responses.</p>

			<p>There are <?= count($attachments) ?> photos and <?= count($responses) ?> responses currently available to view
				on this event.</p>
		</div>

	</article>
</main>

<?php include 'footer.php'; ?>

