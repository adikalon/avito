<?php if ($logs): ?>
<div class="ui segment grid">
	<div class="four wide column">
		<div class="ui vertical fluid tabular menu">
			<?php foreach ($logs as $log): ?>
			<a href="?log=<?php echo $log; ?>" class="item<?php if ($log == $current) echo ' active'; ?>"><?php echo $log; ?></a>
			<?php endforeach; ?>
	  </div>
	</div>
	<div class="twelve wide stretched column">
		<div class="ui segment">
			<?php echo $text; ?>
		</div>
	</div>
</div>
<?php else: ?>
	<h2 class="ui center aligned icon header">
		<i class="delete calendar icon"></i>
		Логи отсутствют
	</h2>
<?php endif; ?>