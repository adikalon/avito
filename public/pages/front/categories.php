<?php if (isset($insertResponse)): ?>
<div class="ui positive message">
	<i class="close icon"></i>
	<div class="header">
		Результат:
	</div>
	<ul class="list">
		<li>
			Категоия <b><?php echo $insertResponse['name']; ?></b> <?php echo $insertResponse['new'] ? 'добавлена' : 'обновлена'; ?>
		</li>
	</ul>
</div>
<?php endif; ?>
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
<h4 class="ui horizontal divider header"><i class="add square icon"></i>Добавить</h4>
<form action="" method="post" class="ui labeled fluid action input">
	<div class="ui label">https://</div>
	<input type="text" name="link" placeholder="www.avito.ru/...">
	<button type="submit" class="ui primary button">Добавить</button>
</form>
<?php if ($categories): ?>
<h4 class="ui horizontal divider header"><i class="browser icon"></i>Категории</h4>
<table class="ui single line celled table">
	<thead>
		<tr>
			<th>Название</th>
			<th>Ссылка</th>
			<th>Удалить</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($categories as $category): ?>
		<tr>
			<td><?php echo $category->name; ?></td>
			<td>
				<a target="_blank" href="<?php echo $category->link; ?>"><?php echo $category->link; ?></a>
			</td>
			<td>
				<form action="" method="post">
					<input type="hidden" name="delete[id]" value="<?php echo $category->id; ?>">
					<input type="hidden" name="delete[name]" value="<?php echo $category->name; ?>">
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