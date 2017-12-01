<?php

/**
 * Работа с БД для текста
 */
class TextWriter
{
	/**
	 * Сохраняет текст в БД
	 *
	 * @param string $text Текст с синонимами
	 * @return false Если передана не строка
	 * @return 1 Запись обновлена
	 * @return 0 Произошла ошибка
	 */
	public static function save($text = false)
	{
		if (!is_string($text)) {
			return false;
		}
		$text = DB::connect()->quote(trim($text));
		$sql = "UPDATE settings SET text=$text";
		return DB::connect()->exec($sql);
	}
}
