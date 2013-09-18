<footer id="footer">
	<a href="http://beascout.org">Be a Scout!</a><br>
	<a href="http://www.scouting.org/">Boy Scouts of America</a><br>
	<a href="http://www.cccbsa.org/">Chester County Council</a><br>
	<a href="http://www.cccbsa.org/CubScouts/Pack/32">CCC's Pack 32 Page</a><br>
	<span style="display:inline-block">Thanks for visiting <?= $config['org_name'] ?></span>
</footer>

<div id="dialog-form"></div>

<script src="/js/redactor/redactor.min.js"></script>
<script src="/js/select2/select2.min.js"></script>
<script src="/js/dropzone/dropzone.js"></script>
<script src="/js/unveil.js"></script>
<script src="/js/unslider.min.js"></script>

<script>
	<?php if($_app->can_edit()): ?>
	function openModal() {
		$('#new_group').select2();
		$('#new_content').redactor({
			minHeight: 200,
			imageUpload: '<?= $_app->get_url('upload_photo') ?>',
			clipboardUploadUrl: '<?= $_app->get_url('paste_photo') ?>',
			fileUpload: '<?= $_app->get_url('upload_file') ?>',
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
			//modal: true,
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

		$('.toggle_menu > a').on('click', function(ev){
			ev.preventDefault();
			$(this).closest('.toggle_menu').toggleClass('active');
		});

		$('#calendar').on('mousemove', '.event', function(){
			if($(this).offset().left + $(this).width() > $(window).width()) {
				$(this).css('right', 0);
			}
		});

		$("img").unveil();

		<?php if($_app->can_edit()): ?>
		$('.delete').on('click', function(ev){
			var href = $(this).attr('href');
			if(!confirm('Are you sure you want to delete this?')) {
				ev.preventDefault();
			}
		});
		<?php endif; ?>

		Messenger.options = {
			extraClasses: 'messenger-fixed messenger-on-top messenger-on-right',
			theme: 'future'
		}

		<?php if(!$_app->profile_complete()): ?>
		Messenger().post({
			message: "You have not completed your profile.",
			type: 'error',
			showCloseButton: true,
			actions: {
				profile: {
					label: 'Edit Your Profile',
					action: function(){
						location.href="/profile";
						return true;
					}
				}
			}
		});
		<?php endif; ?>
		<?php if(isset($_SESSION['public_notice'])): ?>
		Messenger().post({
			message: "Are you at a public terminal?",
			actions: {
				Yes: {
					label: 'Yes',
					action: function() {
						$.post(
							'<?= $_app->get_url('public_notice') ?>',
							{public: 'true'}
						);
						return this.cancel();
					}
				},
				No: {
					label: 'No',
					action: function() {
						$.post(
							'<?= $_app->get_url('public_notice') ?>',
							{public: 'false'}
						);
						return this.cancel();
					}
				}
			}
		});
		<?php endif; ?>
		<?php if(isset($_SESSION['messages'])): foreach($_SESSION['messages'] as $message): ?>
		Messenger().post({
			message: '<?= htmlspecialchars($message['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8') ?>',
			type: '<?= htmlspecialchars($message['type'], ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8') ?>'
		});
		<?php endforeach; unset($_SESSION['messages']); endif; ?>

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
	});

	function doLogout() {
		navigator.id.logout();
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
</script>
</div>
</body>
</html>