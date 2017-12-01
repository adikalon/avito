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
<h4 class="ui horizontal divider header"><i class="edit icon"></i>Текст</h4>
<div class="ui icon message">
	<i class="help circle outline icon"></i>
	<div class="content">
		<div class="header">
			Как составить сообщение?
		</div>
		<p>Сообщение может содержать спинтаксы - группы фраз-синонимов объединенных в фигурные скобки <b>{текст1|текст2|текст3}</b>. В результате будет подставлен один из вариантов, разделяемых прямой чертой.</p>
	</div>
</div>
<form class="ui form success" action="" method="post">
	<div class="field">
		<textarea name="text" placeholder="Пример {простого|несложного} текста"><?php if (!empty($text)) echo $text; ?></textarea>
	</div>
	<button type="submit" class="ui primary submit labeled icon button">
		<i class="save icon"></i>
		Сохранить
	</button>
</form>
<?php if (!empty($text)): ?>
<h4 class="ui horizontal divider header"><i class="print icon"></i>Возможные варианты</h4>
<div class="ui message">
	<div class="content">
		<div class="header">
			Пример №1
		</div>
		<p><?php echo Text::rand($text); ?></p>
	</div>
</div>
<div class="ui message">
	<div class="content">
		<div class="header">
			Пример №2
		</div>
		<p><?php echo Text::rand($text); ?></p>
	</div>
</div>
<div class="ui message">
	<div class="content">
		<div class="header">
			Пример №3
		</div>
		<p><?php echo Text::rand($text); ?></p>
	</div>
</div>
<?php endif; ?>