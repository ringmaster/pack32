<?php include 'header.php'; ?>

<main id="content">
	<p class="ical_subscribe"><a href="webcal://<?= $_SERVER['HTTP_HOST'] ?>/ical?groups=<?= implode(',', $groups_to_get) ?>" class="cal_link button"><i class="icon-calendar"></i> Subscribe</a></p>
	<h1><?= $sel_date->format('F Y') ?></h1>

	<?php
	$next_month = clone $sel_date;
	$next_month->add(new \DateInterval('P1M'));
	$prev_month = clone $sel_date;
	$prev_month->sub(new \DateInterval('P1M'));
	?>
	<div class="cal_nav">
		<a class="cal_link" href="<?= $_app->get_url('calendar_date', ['month' => $prev_month->format('m'), 'year' => $prev_month->format('Y')]) ?>?groups=<?= implode(',', $groups_to_get) ?>">&laquo;<?= $prev_month->format('F Y') ?></a>
		<a class="cal_link" href="<?= $_app->get_url('calendar_date', ['month' => $next_month->format('m'), 'year' => $next_month->format('Y')]) ?>?groups=<?= implode(',', $groups_to_get) ?>"><?= $next_month->format('F Y') ?>&raquo;</a>
		<span id="group_picker">
			<label for="pick_group">Show Groups:</label>
			<select multiple id="pick_group" style="min-width: 50%;">
				<?php foreach($groups as $group): ?>
					<option value="<?= $group['id'] ?>" <?= $group['selected'] ?>><?= $group['name'] ?></option>
				<?php endforeach; ?>
			</select>
		</span>
	</div>

	<div id="calendar">
		<ol class="calendar clearfix">
			<li class="daynames">Sunday</li>
			<li class="daynames">Monday</li>
			<li class="daynames">Tuesday</li>
			<li class="daynames">Wednesday</li>
			<li class="daynames">Thursday</li>
			<li class="daynames">Friday</li>
			<li class="daynames">Saturday</li>
		<?php
		/** @var DateTime $current_date */
		/** @var DateTime $end_date */
		$current_date = clone $start_date;
		while($current_date <= $end_date):
			$cur_month = $sel_date->format('m') == $current_date->format('m');
			$today = $current_date ==  new DateTime('today') ? 'today' : '';
			$past = $current_date < new DateTime('-1 day');
			$empty = 'empty';
			foreach($events as $event) {
				if($event['event_on'] >= $current_date->getTimestamp() && $event['event_on'] < $current_date->getTimestamp() + 86400) {
					$empty = '';
				}
			}
		?>
			<li data-date="<?= $current_date->getTimestamp() ?>" data-date-formatted="<?= $current_date->format('Y-m-d H:i:s') ?>" class="cal_cell <?= $cur_month ? 'current_month' : 'other_month'; ?> <?= $past ? 'past' : 'upcoming'; ?> <?= $empty ?> <?= $today ?>">
				<span class="date_weekday"><?= $current_date->format('D'); ?> </span><span class="date_number"><?= $current_date->format('j') ?></span>
				<ul>
				<?php
				foreach($events as $event) :
					if($event['event_on'] >= $current_date->getTimestamp() && $event['event_on'] < $current_date->getTimestamp() + 86400):
				?>
						<li><div class="event <?= $event['is_global'] ? 'global' : 'user' ?> <?= $event['status_name'] ?>">
								<a class="event_link" href="<?= $_app->get_url('event', ['slug' => $event['slug']]) ?>">
								<?php foreach($event['groups'] as $group): ?>
								<small class="group"><?= $group['name'] ?></small>
								<?php endforeach; ?>
								<span class="event_title"><span class="event_time"><?= $event['event_time'] ?></span><?= $event['title'] ?></a>
								<?php if($_app->can_edit()): ?>
								<a href="<?= $_app->get_url('edit', $event) ?>#editor" class="event_edit edit modaldlg">[edit]</a>
								<?php endif; ?>
							</div></li>
				<?php
					endif;
				endforeach;
				?>
				</ul>
			</li>
		<?php
			$current_date->modify('+23 hours');
		endwhile;
		?>
		</ol>
	</div>
</main>

<script>
	$(function(){
		$('.cal_cell').on('dblclick', function(){
			var href = '<?= $_app->get_url('add_new') ?>?etime=' + $(this).data('date') + ' #editor';
			$( "#dialog-form" ).load(href, openModal);
		});
		$('#pick_group')
			.select2()
			.change(function(c){
				console.log(c);
				var data = {groups: c.val};
				$('#calendar').load(location.origin + location.pathname + ' #calendar > *', data);
				$('.cal_link').each(function(){
					$this = $(this);
					$this.attr('href', $this.attr('href').replace(/\?.+$|$/, '?groups=' + c.val.join(',') ));
				});
			});
	})
</script>

<?php include 'footer.php'; ?>

