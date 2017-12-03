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
<h4 class="ui horizontal divider header"><i class="setting icon"></i>Настройки</h4>
<form class="ui segment form" action="" method="post">
	<div class="inline field">
		<div class="ui toggle checkbox">
			<input type="checkbox" name="block" class="hidden"<?php if ($settings->block == 1) echo ' checked'; ?>>
			<label>Рассылка разрешена?</label>
		</div>
	</div>
	<div class="inline field">
		<div class="ui toggle checkbox">
			<input type="checkbox" name="random" class="hidden"<?php if ($settings->random == 1) echo ' checked'; ?>>
			<label>Выбирать произвольный аккаунт при рассылке?</label>
		</div>
	</div>
	<div class="field">
		<label>Пауза между отправкой сообщения (в секундах)</label>
		<div class="fields">
			<div class="field">
				<input type="number" name="pause[from]" placeholder="От" value="<?php echo $settings->pause_from; ?>">
			</div>
			<div class="field">
				<input type="number" name="pause[to]" placeholder="До" value="<?php echo $settings->pause_to; ?>">
			</div>
		</div>
	</div>
	<div class="field">
		<label>Не ходить дальше N страницы категории (0 - обходить все)</label>
		<div class="fields">
			<div class="field">
				<input type="number" name="break" placeholder="0" value="<?php echo $settings->break; ?>">
			</div>
		</div>
	</div>
	<button type="submit" name="set" class="ui primary submit labeled icon button">
		<i class="save icon"></i>
		Сохранить
	</button>
</form>
<h4 class="ui horizontal divider header"><i class="lock icon"></i>Изменить пароль</h4>
<form class="ui segment form" action="" method="post">
	<div class="three fields">
		<div class="field">
			<label>Старый пароль</label>
			<input type="password" name="oldpass" placeholder="Старый пароль">
		</div>
		<div class="field">
			<label>Новый пароль</label>
			<input type="password" name="newpass" placeholder="Новый пароль">
		</div>
		<div class="field">
			<label>Новый пароль еще раз</label>
			<input type="password" name="renewpass" placeholder="Новый пароль еще раз">
		</div>
	</div>
	<button type="submit" class="ui primary submit labeled icon button">
		<i class="unlock icon"></i>
		Изменить
	</button>
</form>