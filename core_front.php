<?php

error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');

// Подключение констант
require_once __DIR__.'/constants.php';

// Автозагрузка классов
$autoload = spl_autoload_register(function ($class) {
	require CLASSES.'/'.$class.'.class.php';
});

if (!$autoload) {
	Logger::file("Неизвестный класс\n");
	exit();
}

// Проверка зависимостей композера и автозагрузка
if (!file_exists(COMPOSER) or is_dir(COMPOSER)) {
	Logger::file("Не установлены зависимости от composer\n");
	exit();
}
require COMPOSER;

// Создание директории для логов
if (!file_exists(LOGS) or !is_dir(LOGS)) {
	mkdir(LOGS);
}

// Создание директории для хранения временных файлов
if (!file_exists(TEMP) or !is_dir(TEMP)) {
	mkdir(TEMP);
}