<?php

// Реавторизация аккаунта
if (isset($_POST['update'])) {
	$idUpdate = $_POST['update'];
	$accountString = AccountWriter::getAccount($idUpdate);
	if ($accountString) {
		$resultUpdate = Account::setAccounts($accountString);
		if (is_array($resultUpdate)) {
			$insertResponse = $resultUpdate;
		} else {
			switch ($resultUpdate) {
			case 1:
				$error = 'Список аккаунтов составлен некорректно';
				break;
			case 2:
				$error = 'Не удалось соеденитья с avito.ru. Попробуйте повторить попытку позже';
				break;
			default:
				$error = 'Неизвестная ошибка. Попробуйте повторить попытку позже';
		}
		}
	} else {
		$error = 'Неизвестная ошибка. Попробуйте повторить попытку позже';
	}
}

// Удаление аккаунта
if (isset($_POST['delete'])) {
	$id = $_POST['delete']['id'];
	$login = $_POST['delete']['login'];
	$delete = AccountWriter::delete($id);
	if ($delete) {
		$success = "Аккаунт <b>$login</b> удален";
	} else {
		$error = 'Аккаунт отсутствует в базе данных';
	}
}

// Добавление и обновление аккаунтов по переданому списку
if (isset($_POST['accounts'])) {
	$resultAccounts = Account::setAccounts($_POST['accounts']);
	if (is_array($resultAccounts)) {
		$insertResponse = $resultAccounts;
	} else {
		switch ($resultAccounts) {
			case 1:
				$error = 'Список аккаунтов составлен некорректно';
				break;
			case 2:
				$error = 'Не удалось соеденитья с avito.ru. Попробуйте повторить попытку позже';
				break;
			default:
				$error = 'Неизвестная ошибка. Попробуйте повторить попытку позже';
		}
	}
}

$sql = "SELECT id, name, login, status FROM accounts";
$accounts = DB::connect()->query($sql)->fetchAll(PDO::FETCH_OBJ);