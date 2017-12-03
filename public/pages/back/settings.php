<?php

// Смена пароля
if (isset($_POST['oldpass']) and isset($_POST['newpass']) and isset($_POST['renewpass'])) {
	$check = Password::rePass($_SESSION['login'], $_POST['oldpass'], $_POST['newpass'], $_POST['renewpass']);
	if ($check === true) {
		$success = 'Пароль изменен';
	} elseif ($check == 1) {
		$error = 'Неправильный старый пароль';
	} elseif ($check == 2) {
		$error = 'Новый пароль не совпадает с повторно введенным';
	} else {
		$error = 'Неизвестная ошибка. Попробуйте повторить попытку позже';
	}
}

// Сохранение настоек
if (isset($_POST['set'])) {
	$sql = "SELECT block, random, pause_from, pause_to, break FROM settings LIMIT 1";
	$settings = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
	$block = isset($_POST['block']) ? 1 : 0;
	$random = isset($_POST['random']) ? 1 : 0;
	$pause = isset($_POST['pause']) ? $_POST['pause'] : ['pause_from' => $settings->pause_from, 'pause_to' => $settings->pause_to];
	$break = isset($_POST['break']) ? $_POST['break'] : $settings->break;
	$set = Settings::set($block, $random, $pause, $break);
	if ($set) {
		$success = 'Настойки сохранены';
	} elseif ($set === false) {
		$error = 'Переданы некорректные параметры';
	} else {
		$error = 'Неизвестная ошибка. Попробуйте повторить попытку позже';
	}
}

// Выборка и БД
$sql = "SELECT block, random, pause_from, pause_to, break FROM settings LIMIT 1";
$settings = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);