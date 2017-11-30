<?php

session_start();

$pages = [
	'login' => 'Авторизация',
	'exit' => 'Выход',
	'404' => 'Страница не найдена',
	'accounts' => 'Аккаунты',
	'categories' => 'Категории',
	'texts' => 'Тексты',
	'settings' => 'Настройки',
	'logs' => 'Логи',
	'info' => 'Информация',
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