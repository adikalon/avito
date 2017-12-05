<?php if (isset($success)): ?>
<div class="ui positive message">
	<i class="close icon"></i>
	<div class="header">
		Результаты:
	</div>
	<ul class="list">
		<li><?php echo $success; ?></li>
	</ul>
</div>
<?php endif; ?>
<?php if (isset($error)): ?>
<div class="ui negative message">
	<i class="close icon"></i>
	<div class="header">
		Ошибка:
	</div>
	<ul class="list">
		<li><?php echo $error; ?></li>
	</ul>
</div>
<?php endif; ?>
<?php if ($logs): ?>
<div class="ui segment grid">
	<div class="four wide column">
		<button type="submit" class="ui negative submit labeled icon button" onclick="$('.ui.tiny.modal').modal('show');">
			<i class="trash icon"></i>
			Очистить логи
		</button>
		<div class="ui vertical fluid tabular menu">
			<?php foreach ($logs as $log): ?>
			<a href="<?php echo DOMAIN; ?>/?page=logging&log=<?php echo $log; ?>" class="item<?php if ($log == $current) echo ' active'; ?>"><?php echo $log; ?></a>
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

<div class="ui tiny modal">
	<div class="header"><i class="trash icon"></i>Очистка логов</div>
	<div class="content">
		<p>Данное действие удалит все лог-файлы. Восстановить будет невозможно. Продолжить?</p>
	</div>
	<form class="actions" action="" method="post">
		<div class="ui black deny button">Нет</div>
		<button class="ui positive right labeled icon button" name="deletes" type="submit">
			Да
			<i class="checkmark icon"></i>
		</button>
	</form>
</div>