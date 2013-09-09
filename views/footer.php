<footer id="footer">
	<a href="http://beascout.org">Be a Scout!</a><br>
	<a href="http://www.scouting.org/">Boy Scouts of America</a><br>
</footer>

<div id="dialog-form"></div>

<script src="/js/redactor/redactor.min.js"></script>
<script src="/js/select2/select2.min.js"></script>

<script>
	<?php if($loggedin): ?>
	function openModal() {
		$('#new_group').select2();
		$('#new_content').redactor({
			minHeight: 200,
			imageUpload: '<?= $_app->get_url('upload_photo') ?>',
			clipboardUploadUrl: '<?= $_app->get_url('paste_photo') ?>',
			autoresize: false
		});
		$( "#dialog-form").dialog('open');
	}

	function add_content() {
		$( "#dialog-form" ).load('<?= $_app->get_url('add_new'); ?> #editor', openModal);
	}
	<?php endif; ?>

	$(function ()
	{
		$( "#dialog-form" ).dialog({
			title: '',
			autoOpen: false,
			height: 620,
			width: 680,
			modal: true,
			buttons: {
				Cancel: function() {
					$(this).dialog( "close" );
				},
				Submit: function() {
					$(this).find('form').submit();
				}
			}
		});

		$('.modaldlg a, a.modaldlg').on('click', function(ev){
			var href = $(this).attr('href').replace(/(#.\w+$)/, ' $1');
			$( "#dialog-form" ).load(href, openModal);
			ev.preventDefault();
			return false;
		});



		<?php if($loggedin): ?>
		$('.delete').on('click', function(ev){
			var href = $(this).attr('href');
			if(!confirm('Are you sure you want to delete this?')) {
				ev.preventDefault();
			}
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