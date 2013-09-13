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
	</article>
</main>

<?php include 'footer.php'; ?>

