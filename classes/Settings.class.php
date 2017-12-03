<?php

/**
 * Настройки рассылки
 */
class Settings
{
	/**
	 * Проверяет валидность переданых настроек
	 *
	 * @param numeric $block Разрешен запуск рассылки или нет. 1 - разрешен. 0 - нет
	 * @param numeric $random Выбирать произвольный аккаунт или нет. 1 - да. 0 - нет
	 * @param array $pause Пауза между отправкой сообщения (в секундах). Элемент массива from - от. to - до
	 * @param numeric $break Дальше какой страницы категории не ходить
	 * @return true Все параметры указаны верно
	 * @return false Параметры указаны не верно
	 */
	public static function check($block = false, $random = false, $pause = false, $break = false)
	{
		if (!is_numeric($block) or !is_numeric($random) or !is_array($pause) or !is_numeric($break)) {
			return false;
		}
		if ($block > 1 or $random > 1) {
			return false;
		}
		if (!isset($pause['from']) or !is_numeric($pause['from']) or !isset($pause['to']) or !is_numeric($pause['to'])) {
			return false;
		}
		if ($pause['from'] < 0 or $pause['to'] < 0 or $break < 0) {
			return false;
		}
		return true;
	}
	
	/**
	 * Запись натроек в БД
	 *
	 * @param numeric $block Разрешен запуск рассылки или нет. 1 - разрешен. 0 - нет
	 * @param numeric $random Выбирать произвольный аккаунт или нет. 1 - да. 0 - нет
	 * @param array $pause Пауза между отправкой сообщения (в секундах). Элемент массива from - от. to - до
	 * @param numeric $break Дальше какой страницы категории не ходить
	 * @return true Сохранено в БД
	 * @return false Проблемы с БД
	 */
	public static function write($block, $random, $pause, $break)
	{
		$block = DB::connect()->quote(trim($block));
		$random = DB::connect()->quote(trim($random));
		$pause_from = DB::connect()->quote(trim($pause['from']));
		$pause_to = DB::connect()->quote(trim($pause['to']));
		$break = DB::connect()->quote(trim($break));
		$sql = "UPDATE settings SET block=$block, random=$random, pause_from=$pause_from, pause_to=$pause_to, break=$break";
		if (DB::connect()->exec($sql)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Собирательный метод. Задействует все необходимые для изменения настроек методы
	 *
	 * @param numeric $block Разрешен запуск рассылки или нет. 1 - разрешен. 0 - нет
	 * @param numeric $random Выбирать произвольный аккаунт или нет. 1 - да. 0 - нет
	 * @param array $pause Пауза между отправкой сообщения (в секундах). Элемент массива from - от. to - до
	 * @param numeric $break Дальше какой страницы категории не ходить
	 * @return true Сохранено в БД
	 * @return null Проблема с БД или переданы некорректные параметры
	 * @return false Переданы некорректные параметры
	 */
	public static function set($block, $random, $pause, $break)
	{
		$check = self::check($block, $random, $pause, $break);
		if (!$check) {
			return false;
		}
		$write = self::write($block, $random, $pause, $break);
		if (!$write) {
			return null;
		}
		return true;
	}
}
