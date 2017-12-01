<?php

if (isset($_POST['text'])) {
	$write = TextWriter::save($_POST['text']);
	if ($write) {
		$insert = $write;
		$success = 'Сообщение сохранено';
	} else {
		$error = 'Не удалось соединиться с базой. Повторите попытку позже';
	}
}

$sql = "SELECT text FROM settings LIMIT 1";
$text = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ)->text;