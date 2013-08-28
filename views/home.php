<html>
<head>
	<title><?= $title ?></title>
	<link rel="stylesheet" href="/css/pack32.css" type="text/css">
	<link rel="stylesheet" href="/js/redactor/redactor.css" type="text/css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<!--[if lt IE 9]>
	<script src="dist/html5shiv.js"></script>
	<![endif]-->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="https://login.persona.org/include.js"></script>
</head>
<body>
<div class="wrapper">
	<header id="header">
		<h1>Cub Scout Pack 32</h1>
	</header>
</div>
<nav id="nav">
	<ol>
		<?php foreach ($menu as $item): ?>
			<li class="<?= isset($item['class']) ? $item['class'] : '' ?>"><a href="<?= $item['href'] ?>"><?= $item['title'] ?></a></li>
		<?php endforeach; ?>
	</ol>
</nav>
<div class="wrapper">
	<main id="content">

		<article class="post">
			<?php if($loggedin): ?>
			<div class="toolkit">
				<a class="edit" href="#edit"><i class="icon-edit" title="edit article"></i></a>
				<a class="delete" href="#delete"><i class="icon-trash" title="delete article"></i></a>
			</div>
			<?php endif; ?>
			<h1>Cheerleader Chic</h1>

			<div class="content">
				<p>
					"Cheerleader Chic" offers insights from three different perspectives as to why the icon of the cheerleader has
					once again returned in favor in our schools. The ideas of the parody of the uniform, the attraction of mates,
					and the ideal woman are put forth as reasons for this <a href="http://google.com/">resurgence</a>.
				</p>

				<p>
					Robert Thompson thinks that the age of the cheerleader has come and gone. Nowadays, the cheerleader uniform is
					a
					mockery. The risque outfits that girls wear in school pale the uniform of the cheerleader. It exists only as a
					jest to days when we were more innocent.
				</p>

				<p>
					Helen Fisher believes that cheerleaders are able to present themselves as more worthy mates than other girls
					by
					their show of vitality. Sports fans typically find cheerleaders very near their robust male counterparts, thus
					extending Fisher's supposition. She says that a psychological mechanism allows the opposite sex to detect this
					display of vigor.
				</p>

				<p>
					Michael Porte expects that the cheerleader characterizes everything womanly about a woman and allows attached
					men to fantasize for everything that they would wont. The cheerleader fantasy allows men to escape from their
					dreary relations and imagine life with a woman that their wives are not.
				</p>

				<h2>Sample Ordered List:</h2>
				<ol>
					<li>This is the first item</li>
					<li>A second item is compulsory</li>
					<li>Three items are optional, but nice</li>
				</ol>

				<h3>Sample Unordered List:</h3>
				<ul>
					<li>These items could be in any order</li>
					<li>At least two items should be in a list</li>
					<ul>
						<li>Sometimes there are sub-lists</li>
						<li>The order of sublists shouldn't matter either</li>
					</ul>
				</ul>
			</div>
		</article>

	</main>
	<footer id="footer">

	</footer>

	<script src="/js/redactor/redactor.min.js"></script>
	<script>
		$(function ()
		{
			<?php if($loggedin): ?>
			$('.edit').on('click', function ()
			{
				var post = $(this).closest('.post');
				post.find('.content').redactor({
					//air: true
				});
			});
			<?php endif; ?>

			navigator.id.watch({
				<?php if($loggedin): ?>
				loggedInUser: '<?= $currentuser ?>',
				<?php endif; ?>
				onlogin: function(assertion) {
					$.ajax({
						type: 'POST',
						url: '/auth/login',
						data: {assertion: assertion},
						success: function(res, status, xhr) {
							window.location.reload();
						},
						error: function(xhr, status, err) {
							navigator.id.logout();
							alert("Login failure: " + err);
						}
					});
				},
				onlogout: function() {
					$.ajax({
						type: 'POST',
						url: '/auth/logout',
						success: function(res, status, xhr) {
							console.log(res);
							if(res == 'true') {
								window.location.reload();
							}
						},
						error: function(xhr, status, err) { alert("Logout failure: " + err); }
					});
				}
			})
		})
	</script>
</div>
</body>
</html>