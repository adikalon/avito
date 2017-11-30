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
<div class="ui message">
	<i class="close icon"></i>
	<div class="header">
		Результат:
	</div>
	<ul class="list">
		<?php foreach ($insertResponse as $account): ?>
		<li>
			<?php if ($account['captcha']): ?>
			Не удалось <?php echo $account['new'] ? 'добавить' : 'обновить'; ?> аккаунт <b><?php echo $account['login']; ?></b>. Требуется ввод каптчи
			<?php elseif(!$account['status']): ?>
			Не удалось <?php echo $account['new'] ? 'добавить' : 'обновить'; ?> аккаунт <b><?php echo $account['login']; ?></b>. Неверный логин или пароль, либо аккаунт заблокирован
			<?php else: ?>
			Аккаунт <b><?php echo $account['login']; ?></b> успешно <?php echo $account['new'] ? 'добавилен' : 'обновлен'; ?>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
<?php if ($accounts): ?>
<h4 class="ui horizontal divider header"><i class="users icon"></i>Текущие</h4>
<table class="ui single line celled table">
	<thead>
		<tr>
			<th>Имя</th>
			<th>Логин</th>
			<th>Статус</th>
			<th>Перезайти</th>
			<th>Удалить</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($accounts as $account): ?>
		<tr>
			<td><?php echo $account->name; ?></td>
			<td><?php echo $account->login; ?></td>
			<td><?php echo $account->status ? 'Активен' : 'Не активен'; ?></td>
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

<h4 class="ui horizontal divider header"><i class="add user icon"></i>Добавить</h4>

<form class="ui form success" action="" method="post">
	<div class="field">
		<textarea name="accounts"></textarea>
	</div>
	<button type="submit" class="ui primary submit button">Добавить</button>
</form>