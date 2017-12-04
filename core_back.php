<?php

error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');

// Основные константы
define('CORE', __DIR__);
define('APP', CORE.'/app');
define('TEMP', CORE.'/temp');
define('LOCKS', CORE.'/locks');
define('CLASSES', CORE.'/classes');
define('LOGS', CORE.'/logs');
define('PUBL', CORE.'/public');
define('DOMEN', 'http://avito.loc/public');
define('PAGES', PUBL.'/pages');
define('DATABASES', CORE);
define('COMPOSER', CORE.'/vendor/autoload.php');

// Автозагрузка классов
$autoload = spl_autoload_register(function ($class) {
	require CLASSES.'/'.$class.'.class.php';
});

if (!$autoload) {
	Logger::send("Неизвестный класс\n");
	exit();
}

// Проверка зависимостей композера и автозагрузка
if (!file_exists(COMPOSER) or is_dir(COMPOSER)) {
	Logger::send("Не установлены зависимости от composer\n");
	exit();
}
require COMPOSER;

// Создание директории для логов
if (!file_exists(LOGS) or !is_dir(LOGS)) {
	mkdir(LOGS);
}

// Создание директории для хранения временных
if (!file_exists(TEMP) or !is_dir(TEMP)) {
	mkdir(TEMP);
}

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
	Logger::send("Скрипт уже запущен\n");
	exit();
}