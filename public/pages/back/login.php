<?php

if (isset($_POST['login']) and isset($_POST['pass'])) {
	$login = DB::connect()->quote(trim($_POST['login']));
	$pass = trim($_POST['pass']);
	$sql = "SELECT login, pass FROM users WHERE login=$login LIMIT 1";
	$user = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
	if ($user and password_verify($pass, $user->pass)) {
		$_SESSION['login'] = $user->login;
		header('Location: '.DOMAIN);
	} else {
		$error = 'Неправильный логин или пароль';
	}
}