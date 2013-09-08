<?php include 'header.php'; ?>

<main id="content">
	<h1><?= $sel_date->format('F Y') ?></h1>

	<?php
	$next_month = clone $sel_date;
	$next_month->add(new \DateInterval('P1M'));
	$prev_month = clone $sel_date;
	$prev_month->sub(new \DateInterval('P1M'));
	?>
	<div class="cal_nav">
		<a href="<?= $app->get_url('calendar_date', ['month' => $prev_month->format('m'), 'year' => $prev_month->format('Y')]) ?>">&laquo;<?= $prev_month->format('F Y') ?></a>
		<a href="<?= $app->get_url('calendar_date', ['month' => $next_month->format('m'), 'year' => $next_month->format('Y')]) ?>"><?= $next_month->format('F Y') ?>&raquo;</a>
	</div>

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
	$current_date = $start_date;
	$add_one_day = new \DateInterval('P1D');
	while($current_date <= $end_date):
		$cur_month = $sel_date->format('m') == $current_date->format('m');
		$past = $current_date < new DateTime('-1 day');
		$empty = 'empty';
		foreach($events as $event) {
			if($event['event_on'] > $current_date->getTimestamp() && $event['event_on'] < $current_date->getTimestamp() + 86400) {
				$empty = '';
			}
		}
	?>
		<li class="<?= $cur_month ? 'current_month' : 'other_month'; ?> <?= $past ? 'past' : 'upcoming'; ?> <?= $empty ?>"><span class="date_weekday"><?= $current_date->format('D'); ?></span><span class="date_number"><?= $current_date->format('j') ?></span>
			<?php
			foreach($events as $event) :
				if($event['event_on'] > $current_date->getTimestamp() && $event['event_on'] < $current_date->getTimestamp() + 86400):
			?>
					<div class="event <?= $event['is_global'] ? 'global' : 'user' ?>"><small class="group"><?= $event['name'] ?></small> <a href="<?= $app->get_url('event', ['slug' => $event['slug']]) ?>"><?= $event['title'] ?></a></div>
			<?php
				endif;
			endforeach;
			?>
		</li>
	<?php
		$current_date->add($add_one_day);
	endwhile;
	?>
	</ol>

</main>

<?php include 'footer.php'; ?>

