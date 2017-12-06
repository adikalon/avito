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
	 * @param numeric $wait Время запрета рассылки в случаек блока по ip (в секундах)
	 * @param string $token токен от anti-captcha.com
	 * @return true Все параметры указаны верно
	 * @return false Параметры указаны не верно
	 * @return null Несуществующий токен anti-captcha.com
	 */
	public static function check($block = false, $random = false, $pause = false, $break = false, $wait = false, $token = false)
	{
		if (!is_numeric($block) or !is_numeric($random) or !is_array($pause) or !is_numeric($break) or !is_numeric($wait) or !is_string($token)) {
			return false;
		}
		if (!empty(trim($token))) {
			if (!AntiCaptcha::check($token)) {
				return null;
			}
		}
		if ($block > 1 or $random > 1 or $block < 0 or $random < 0) {
			return false;
		}
		if (!isset($pause['from']) or !is_numeric($pause['from']) or !isset($pause['to']) or !is_numeric($pause['to'])) {
			return false;
		}
		if ($pause['from'] < 0 or $pause['to'] < 0 or $break < 0 or $wait < 0) {
			return false;
		}
		if ($pause['to'] < $pause['from']) {
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
	 * @param numeric $wait Время запрета рассылки в случаек блока по ip (в секундах)
	 * @param string $token токен от anti-captcha.com
	 * @return true Сохранено в БД
	 * @return false Проблемы с БД
	 */
	public static function write($block, $random, $pause, $break, $wait, $token)
	{
		$block = DB::connect()->quote(trim($block));
		$random = DB::connect()->quote(trim($random));
		$pause_from = DB::connect()->quote(trim($pause['from']));
		$pause_to = DB::connect()->quote(trim($pause['to']));
		$break = DB::connect()->quote(trim($break));
		$wait = DB::connect()->quote(trim($wait));
		$token = DB::connect()->quote(trim($token));
		$sql = "UPDATE settings SET block=$block, random=$random, pause_from=$pause_from, pause_to=$pause_to, break=$break, wait=$wait, token=$token";
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
	 * @return false Переданы некорректные параметры
	 * @return null Несуществующий токен anti-captcha.com
	 * @return 0 Проблема с БД или переданы некорректные параметры
	 */
	public static function set($block, $random, $pause, $break, $wait, $token)
	{
		$check = self::check($block, $random, $pause, $break, $wait, $token);
		if (!$check) {
			return $check;
		}
		$write = self::write($block, $random, $pause, $break, $wait, $token);
		if (!$write) {
			return 0;
		}
		return true;
	}
}
