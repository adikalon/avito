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
define('CSS', 'public/style.css');

// Автозагрузка классов
spl_autoload_register(function ($class) {
	require CLASSES.'/'.$class.'.class.php';
});

// Автозагрузка модулей
$autoload = spl_autoload_register(function ($class) {
	require MODULES.'/'.$class.'.php';
});

if (!$autoload) {
	Logger::file("|START|ERROR| - Неизвестный класс");
	exit();
}

// Проверка зависимостей композера и автозагрузка
if (!file_exists(COMPOSER) or is_dir(COMPOSER)) {
	Logger::file("|START|ERROR| - Не установлены зависимости от composer");
	exit();
}
require COMPOSER;