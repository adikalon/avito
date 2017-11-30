<?php

$sql = "SELECT name, ident, status FROM accounts";
$accounts = DB::connect()->query($sql)->fetch();

if (isset($_POST['accounts'])) {
	$parse = Account::parse($_POST['accounts']);
	$authorisation = Account::auth($parse);
	var_dump($authorisation);
}