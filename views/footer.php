<footer id="footer">

</footer>

<div id="dialog-form"></div>

<script src="/js/redactor/redactor.min.js"></script>
<script src="/js/select2/select2.min.js"></script>

<script>
	<?php if($loggedin): ?>
	function add_content() {
		$( "#dialog-form" ).load('<?= $app->get_url('add_new'); ?> #editor', function(){
			$('#new_group').select2();
			$('#new_content').redactor({
				minHeight: 200,
				imageUpload: '<?= $app->get_url('upload_photo') ?>',
				clipboardUploadUrl: '<?= $app->get_url('paste_photo') ?>',
				autoresize: false
			});
			$( "#dialog-form").dialog('open');
		});
	}
	<?php endif; ?>

	$(function ()
	{
		$( "#dialog-form" ).dialog({
			title: 'Add Content',
			autoOpen: false,
			height: 620,
			width: 680,
			modal: true,
			buttons: {
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});

		$('.modaldlg a').on('click', function(ev){
			add_content();
			ev.preventDefault();
			return false;
		});


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