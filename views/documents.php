<?php include 'header.php'; ?>

<main id="content" class="event">
	<article class="post" data-article-id="<?= $article['id'] ?>">
		<?php if ($_app->can_edit()): ?>
			<div class="toolkit">
				<a class="edit modaldlg" href="<?= $_app->get_url('edit', $article) ?>#editor"><i class="icon-edit" title="edit article"></i></a>
			</div>
		<?php endif; ?>
		<h1><?= $article['title'] ?></h1>

		<div class="content">
			<?= $article['content'] ?>
		</div>
	</article>
</main>

<?php include 'footer.php'; ?>

