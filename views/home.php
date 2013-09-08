<?php include 'header.php'; ?>

<main id="content">
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
</main>

<?php include 'footer.php'; ?>

