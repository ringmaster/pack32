<?php include 'header.php'; ?>

<main id="content">
	<section class="main">
	<?php foreach ($articles as $article): ?>
		<article class="post" data-article-id="<?= $article['id'] ?>">
			<?php if ($_app->can_edit()): ?>
				<div class="toolkit">
					<a class="edit modaldlg" href="<?= $_app->get_url('edit', $article) ?>#editor"><i class="icon-edit" title="edit article"></i></a>
					<a class="delete" href="<?= $_app->get_url('delete', $article) ?>"><i class="icon-trash" title="delete article"></i></a>
				</div>
			<?php endif; ?>
			<h1><?= $article['title'] ?></h1>

			<div class="content">
				<?= $article['content'] ?>
			</div>
		</article>
	<?php endforeach; ?>
	</section>
	<section class="sidebar">
		<aside id="upcoming">
			<h2>Upcoming Events</h2>
			<?php if(!$loggedin): ?>
			<p>To see all events, you must log in using the button above.</p>
			<?php endif; ?>
			<ol>
				<?php foreach($upcoming as $event): ?>
				<li><?= date('D, M j', $event['event_on']) ?> - <a href="<?= $_app->get_url('event', $event) ?>"><?= $event['title'] ?></a></li>
				<?php endforeach; ?>
			</ol>
		</aside>
	</section>
</main>

<?php include 'footer.php'; ?>

