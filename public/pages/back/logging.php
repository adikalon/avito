<?php

if (isset($_POST['deletes'])) {
	$deletes = Logs::deletes();
	if ($deletes) {
		$success = "Логи очищены";
	} else {
		$error = 'Произошла неизвестная ошибка';
	}
}

$logs = Logs::files();
$current = Logs::log();
$text = Logs::text($current);
if (!$current) {
	$text = 'Лог указанной даты отсутствует';
}