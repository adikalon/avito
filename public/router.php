<?php

session_start();

$pages = [
	'login' => 'Avito: Авторизация',
	'exit' => 'Avito: Выход',
	'404' => 'Avito: Страница не найдена',
	'accounts' => 'Avito: Аккаунты',
	'categories' => 'Avito: Категории',
	'texts' => 'Avito: Тексты',
	'settings' => 'Avito: Настройки',
	'logging' => 'Avito: Логи',
	'info' => 'Avito: Справка',
];

if (isset($_GET['route'])) {
	$get=str_replace('/', '', $_GET['route']);
	if (array_key_exists($get, $pages) and file_exists(PAGES.'/back/'.$get.'.php') and file_exists(PAGES.'/front/'.$get.'.php')) {
		$page = $get;
	} else {
		$page = '404';
	}
} else {
	$page = 'accounts';
}

if (!isset($_SESSION['login'])) {
	$page = 'login';
}

if ($page == 'login' and isset($_SESSION['login'])) {
	$page = '404';
}