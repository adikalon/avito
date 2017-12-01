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
			if (null === $resultAccounts) {
				$error = 'Не удалось соеденитья с avito.ru. Попробуйте повторить попытку позже';
			} else {
				$error = 'Список аккаунтов составлен некорректно';
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
		if (null === $resultAccounts) {
			$error = 'Не удалось соеденитья с avito.ru. Попробуйте повторить попытку позже';
		} else {
			$error = 'Список аккаунтов составлен некорректно';
		}
	}
}

$sql = "SELECT id, name, login, auth, captcha, block, nologpas FROM accounts";
$accounts = DB::connect()->query($sql)->fetchAll(PDO::FETCH_OBJ);