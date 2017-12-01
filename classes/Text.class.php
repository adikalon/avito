<?php

/**
 * Обработка рассылочного текста
 */
class Text
{
	/**
	 * Отдает распарсеный вариант текста
	 *
	 * @param string $text Текст с синонимами
	 * @return string Рандомный вариант текста
	 * @return false Если передана не строка
	 */
	public static function rand($text = false)
	{
		if (!is_string($text)) {
			return false;
		}
		$text = trim($text);
		$result = preg_replace_callback(
			'/\{[^\{\}]*\|[^\{\}]*\}/',
			function ($matches) {
				foreach ($matches as $match) {
					$match = str_replace(['{', '}'], '', $match);
					$match = explode('|', $match);
					return $match[array_rand($match)];
				}
			},
			$text
		);
		preg_match('/\{[^\{\}]*\|[^\{\}]*\}/', $result, $match);
		if (isset($match[0]) and !empty($match[0])) {
			$result = self::rand($result);
		}
		return $result;
	}
}
