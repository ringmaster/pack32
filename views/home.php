<?php include 'header.php'; ?>

<main id="content">
	<section class="main">
	<?php foreach ($articles as $article): ?>
		<article class="post" data-article-id="<?= $article['id'] ?>">
			<?php if ($loggedin): ?>
				<div class="toolkit">
					<a class="edit" href="#edit"><i class="icon-edit" title="edit article"></i></a>
					<a class="delete" href="#delete"><i class="icon-trash" title="delete article"></i></a>
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
			<ol>
				<?php foreach($upcoming as $event): ?>
				<li><?= date('D, M j', $event['event_on']) ?> - <a href="<?= $app->get_url('event', $event) ?>"><?= $event['title'] ?></a></li>
				<?php endforeach; ?>
			</ol>
		</aside>
	</section>
</main>

<?php include 'footer.php'; ?>

