<div class="auth-form">
	<div class="ui top attached header secondary segment">
		Авторизация
	</div>
	<div class="ui attached segment">
<?php if (isset($error)): ?>
		<div class="ui error message">
			<p><?php echo $error; ?></p>
		</div>
<?php endif; ?>
		<form action="" method="post" class="ui form">
			<div class="field">
				<input type="text" name="login" placeholder="Логин">
			</div>
			<div class="field">
				<input type="password" name="pass" placeholder="Пароль">
			</div>
			<div class="field auth-enter">
				<button class="positive ui button auth-button" type="submit">Войти</button>
			</div>
		</form>
	</div>
</div>