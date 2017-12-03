<?php

$logs = Logs::files();
$current = Logs::log();
$text = Logs::text($current);
if (!$current) {
	$text = 'Лог указанной даты отсутствует';
}