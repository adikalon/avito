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
<?php if (isset($insertResponse)): ?>
<div class="ui info message">
	<i class="close icon"></i>
	<div class="header">
		Результат:
	</div>
	<ul class="list">
		<?php foreach ($insertResponse as $account): ?>
		<li>
			<?php echo Account::getAuthMessage($account); ?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
<h4 class="ui horizontal divider header"><i class="add user icon"></i>Добавить</h4>
<div class="ui icon message">
	<i class="help circle outline icon"></i>
	<div class="content">
		<div class="header">
			Как добавить аккаунт?
		</div>
		<p>Данные аккаунта необходимо передавать в формате <b>login:password</b>. Каждый аккаунт с новой строки. Привязать HTTP прокси сервер: <b>login:password{127.0.0.1:8080}</b></p>
	</div>
</div>
<form class="ui form success" action="" method="post">
	<div class="field">
		<textarea name="accounts" placeholder="login:password&#13;&#10;login:password{127.0.0.1:8080}&#13;&#10;login:password&#13;&#10;..."></textarea>
	</div>
	<button type="submit" class="ui primary submit labeled icon button">
		<i class="add user icon"></i>
		Добавить
	</button>
</form>
<?php if ($accounts): ?>
<h4 class="ui horizontal divider header"><i class="users icon"></i>Текущие</h4>
<table class="ui single line celled table">
	<thead>
		<tr>
			<th>Имя</th>
			<th>Логин</th>
			<th>Прокси</th>
			<th>Статус</th>
			<th>Причина</th>
			<th>Перезайти</th>
			<th>Удалить</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($accounts as $account): ?>
		<tr>
			<td><?php echo $account->name; ?></td>
			<td><?php echo $account->login; ?></td>
			<td><?php echo $account->proxy; ?></td>
			<td><?php echo $account->auth ? 'Активен' : 'Не активен'; ?></td>
			<td><?php echo Account::getNonAuthCause($account); ?></td>
			<td>
				<form action="" method="post">
					<input type="hidden" name="update" value="<?php echo $account->id; ?>">
					<button type="submit" class="ui positive icon button">
						<i class="refresh icon"></i>
					</button>
				</form>
			</td>
			<td>
				<form action="" method="post">
					<input type="hidden" name="delete[id]" value="<?php echo $account->id; ?>">
					<input type="hidden" name="delete[login]" value="<?php echo $account->login; ?>">
					<button type="submit" class="ui negative icon button">
						<i class="remove icon"></i>
					</button>
				</form>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>