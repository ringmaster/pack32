<?php include 'header.php'; ?>

<main id="content" class="event">
	<h1>Your Profile</h1>
	<p>You are logged in as <?= $_response['currentuser'] ?>.</p>
	<form method="post" action="" class="whiteform">
		<label for="profile_name">Your Name</label>
		<input type="text" id="profile_name" value="<?= $_response['user']['username'] ?>">
		<fieldset>
			<legend>Subscribed Groups</legend>
			<p>
				Please create an association for each boy to his specific den/group.<br>
				If you are personally associated to a den/committee, add your name as a member and associate yourself to that group.<br>
				For each group member, please use that person's <em>full name</em>.<br>
				To remove a member, clear the checkbox next to the row.<br>
				To commit any changes, click the Update button.
			</p>
			<ul>
			<?php foreach($subscribed as $subscribe): ?>
				<li><input type="checkbox" name="usergroup[<?= $subscribe['id'] ?>][subscribed]" value="<?= $subscribe['id'] ?>" checked placeholder="Group Member's Name"> <input name="usergroup[<?= $subscribe['id'] ?>][name]" type="text" value="<?= $subscribe['name'] ?>"> <select name="usergroup[<?= $subscribe['ug_id'] ?>][group_id]">
					<?php foreach($groups as $group): ?>
						<option value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
					<?php endforeach; ?>
				</select></li>
			<?php endforeach; ?>
				<li><input type="checkbox" name="new_member" value="" checked placeholder="Group Member's Name"> <input type="text" name="new_member_name" value=""> <select name="new_member_group">
						<?php foreach($groups as $group): ?>
							<option value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
						<?php endforeach; ?>
					</select></li>
			</ul>
		</fieldset>
		<input type="submit" class="button" value="Update">
	</form>
</main>

<?php include 'footer.php'; ?>

