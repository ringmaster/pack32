<?php include 'header.php'; ?>

<main id="content" class="event">
	<h1>Your Profile</h1>
	<p>You are logged in as <?= $_response['currentuser'] ?>.</p>
	<form method="post" action="" class="whiteform">
		<fieldset>
			<legend>About You</legend>
			<label for="profile_name">Your Full Name</label>
			<input type="text" id="profile_name" name="profile_name" value="<?= $_response['user']['username'] ?>">
		</fieldset>

		<fieldset>
			<legend>Other Emails</legend>
			<p>Multiple parents can associate their accounts to manage their scouts.</p>

			<?php if(count($other_accounts) > 0): ?>

				<p>The following addresses also manage this account:</p>
				<ul>
				<?php foreach($other_accounts as $account): ?>
					<li><?= $account['username'] ?> &middot; <?= $account['email'] ?></li>
				<?php endforeach; ?>
				</ul>

			<?php else: ?>
				<p>No additional email addresses currently manage this account.</p>
			<?php endif; ?>

		</fieldset>

		<fieldset>
			<legend>Subscribed Groups</legend>
			<ol>
				<li>Please create an association for <b>you and each scout</b> to one or more groups.</li>
				<li>You may add a person more than once, so that they can belong to multiple groups.</li>
				<li>Please use only a person's <em>first</em> name.</li>
				<li>To remove a member, clear the checkbox next to the row.</li>
				<li>To commit any changes, click the Update button.</li>
			</ol>

			<ul id="member_list">

			<?php foreach($subscribed as $subscribe): ?>
				<li><input type="checkbox" name="usergroup[<?= $subscribe['ug_id'] ?>][subscribed]" value="true" checked> <input name="usergroup[<?= $subscribe['ug_id'] ?>][name]" type="text" value="<?= $subscribe['name'] ?>" placeholder="Group Member's Name">
					<select name="usergroup[<?= $subscribe['ug_id'] ?>][group_id]">
						<?php foreach($groups as $group): ?>
							<option value="<?= $group['id'] ?>" <?= $group['id'] == $subscribe['id'] ? 'selected' : '' ?>><?= $group['name'] ?></option>
						<?php endforeach; ?>
					</select>

					<select name="usergroup[<?= $subscribe['ug_id'] ?>][role]">
						<option value="0" <?= $_app->selected(0, $subscribe['role']) ?>>Parent/Leader</option>
						<option value="1" <?= $_app->selected(1, $subscribe['role']) ?>>Scout</option>
						<option value="2" <?= $_app->selected(2, $subscribe['role']) ?>>Sibling</option>
						<option value="3" <?= $_app->selected(3, $subscribe['role']) ?>>Other</option>
					</select>

				</li>
			<?php endforeach; ?>
				<li id="new_member" style="display:none;">
					<input type="checkbox" name="new_member[exists][]" value="true">
					<input type="text" name="new_member[name][]" value="" placeholder="Member's Full Name">
						<select name="new_member[group][]">
						<?php foreach($groups as $group): ?>
							<option value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
						<?php endforeach; ?>
					</select>

					<select name="new_member[role][]">
						<option value="0" >Parent/Leader</option>
						<option value="1" selected>Scout</option>
						<option value="2" selected>Sibling</option>
						<option value="3" selected>Other</option>
					</select>

					<li><a href="#add_member" class="add_member">Click here to add a new member.</a></li>
			</ul>
		</fieldset>
		<input type="submit" class="button" value="Update">
	</form>
</main>

<script>
	$(function(){
		$('.add_member').on('click', function(){
			$html = $('<li>' + $('#new_member').html() + '</li>');
			$html.insertBefore('#new_member').show();
			$html.find('input[type=checkbox]')
				.attr('checked', true)
				.on('change', function(){
					$(this).closest('li').remove();
				});
		});
	});
</script>

<?php include 'footer.php'; ?>

