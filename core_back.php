<?php

error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');

// Основные константы
define('CORE', __DIR__);
define('APP', CORE.'/app');
define('LOCKS', CORE.'/locks');
define('CLASSES', CORE.'/classes');
define('MODULES', CORE.'/modules');
define('LOGS', CORE.'/logs');
define('PUBL', CORE.'/public');
define('PAGES', PUBL.'/pages');
define('DATABASES', CORE);
define('COMPOSER', CORE.'/vendor/autoload.php');

// Автозагрузка классов
spl_autoload_register(function ($class) {
	require CLASSES.'/'.$class.'.class.php';
});

// Автозагрузка модулей
$autoload = spl_autoload_register(function ($class) {
	require MODULES.'/'.$class.'.php';
});

if (!$autoload) {
	Logger::send("|START|ERROR| - Неизвестный класс");
	exit();
}

// Проверка зависимостей композера и автозагрузка
if (!file_exists(COMPOSER) or is_dir(COMPOSER)) {
	Logger::send("|START|ERROR| - Не установлены зависимости от composer");
	exit();
}
require COMPOSER;

// Создание директории для хранения лок-файлов
if (!file_exists(LOCKS) or !is_dir(LOCKS)) {
	mkdir(LOCKS);
}

// Подключение локера
$factory = new TH\Lock\FileFactory(LOCKS);
$lock = $factory->create('avito');
try {
	$lock->acquire();
} catch (Exception $ex) {
	Logger::send("|START|BLOCK| - Скрипт уже запущен");
	exit();
}