<?php

if (isset($_POST['login']) and isset($_POST['pass'])) {
	$login = DB::connect()->quote(trim($_POST['login']));
	$pass = trim($_POST['pass']);
	$sql = "SELECT login, pass, role FROM users WHERE login=$login LIMIT 1";
	$user = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
	if ($user and password_verify($pass, $user->pass)) {
		$_SESSION['user'] = [
			'login' => $user->login,
			'role' => $user->role,
		];
		header('Location: '.DOMAIN);
	} else {
		$error = 'Неправильный логин или пароль';
	}
}