<?php

/**
 * Чтение логов
 */
class Logs
{
	/**
	 * Получить массив с названиями и путями к файлам логов
	 *
	 * @return array Массив со сиском логов
	 * @return null Логи отстствуют
	 */
	public static function files()
	{
		$logs = [];
		foreach(new DirectoryIterator(LOGS) as $item) {
			if (!$item->isDot() and $item->isFile() and $item->getExtension() == 'txt') {
				$logs[] = str_replace('-', '.', $item->getBasename('.txt'));
			}
		}
		if (empty($logs)) {
			return null;
		}
		return array_reverse($logs);
	}
	
	/**
	 * Получить дату текущего отображаемого лога. Работает в связке с Logs::files()
	 * 
	 * @return string Дата лога
	 * @return null Логи отстствуют
	 * @return false В GET передана дата отстствющиего лога
	 */
	public static function log()
	{
		if (isset($_GET['log']) and is_string($_GET['log'])) {
			$path = LOGS.'/'.str_replace('.', '-', trim($_GET['log'])).'.txt';
			if (!file_exists($path) or is_dir($path)) {
				return false;
			} else {
				return trim($_GET['log']);
			}
		} else {
			if (self::files() !== null) {
				return self::files()[0];
			} else {
				return null;
			}
		}
	}
	
	/**
	 * Получить содержимое лога
	 * 
	 * @param string $date Дата лога в формате 2017.12.03
	 * @return string Текст лога
	 * @return null Лог отстствет
	 * @return false Передан некоректный формат
	 */
	public static function text($date = false)
	{
		if (!is_string($date)) {
			return false;
		}
		$path = LOGS.'/'.str_replace('.', '-', trim($date)).'.txt';
		if (!file_exists($path) or is_dir($path)) {
			return null;
		} else {
			$text = file_get_contents($path);
			return str_replace("\n", "<br>", $text);
		}
	}
}
