<?php if ($accounts): ?>
<h4 class="ui horizontal divider header"><i class="users icon"></i>Текущие</h4>
<table class="ui fixed single line celled table">
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
		<tr>
			<td>John</td>
			<td>Approved</td>
			<td title="This is much too long to fit I'm sorry about that">This is much too long to fit I'm sorry about that</td>
			<td title="This is much too long to fit I'm sorry about that">This is much too long to fit I'm sorry about that</td>
			<td title="This is much too long to fit I'm sorry about that">This is much too long to fit I'm sorry about that</td>
		</tr>
		<tr>
			<td>Jamie</td>
			<td>Approved</td>
			<td>Shorter description</td>
			<td>Shorter description</td>
			<td>Shorter description</td>
		</tr>
		<tr>
			<td>Jill</td>
			<td>Denied</td>
			<td>Shorter description</td>
			<td>Shorter description</td>
			<td>Shorter description</td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<h4 class="ui horizontal divider header"><i class="add user icon"></i>Добавить</h4>

<form class="ui form success" action="" method="post">
	<div class="field">
		<textarea name="accounts"></textarea>
	</div>
	<div class="ui success message">
		<div class="header">Form Completed</div>
		<p>You're all signed up for the newsletter.</p>
	</div>
	<button type="submit" class="ui submit button">Добавить</button>
</form>

