<?php

/**
 * Запросы на сервер
 */
class Request
{
	/**
	 * User-Agent для неавторизированного состояния
	 */
	public static $unknownUserAgent = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36';
	
	/**
	 * User-Agent для авторизированного состояния
	 */
	public static $namedUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36';

	/**
	 * Запрос CURL'ом
	 *
	 * @param string $link Ссылка
	 * @param array $options Опции
	 * @param numeric $pause Задержка перед запросом в секундах
	 * @return string Ответ
	 * @return false Ответ не пришел
	 * @return 1 Не передан URI
	 * @return 2 Параметр задержки указан некорректно
	 */
	public static function curl($link = false, $options = [], $pause = false)
	{
		if ($pause) {
			if (!is_numeric($pause)) {
				return 2;
			}
			sleep((int)$options['pause']);
		}
		if ($link) {
			$options[CURLOPT_URL] = $link;
		}
		if (!isset($options[CURLOPT_URL]) or empty($options[CURLOPT_URL])) {
			return 1;
		}
		if (!isset($options[CURLOPT_USERAGENT]) or empty($options[CURLOPT_USERAGENT])) {
			$options[CURLOPT_USERAGENT] = self::$unknownUserAgent;
		}
		$options[CURLOPT_RETURNTRANSFER] = true;
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);
		curl_close($curl);
		if (!$result) {
			return false;
		}
		return $result;
	}
}