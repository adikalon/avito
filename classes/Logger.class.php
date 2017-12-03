<?php

/**
 * Логирование
 */
class Logger
{
	/**
	 * Вывод лога в консоль
	 * 
	 * @param string $message Текст сообщения
	 */
	public static function console($message)
	{
		echo "[".date('H:i:s')."]: ".mb_convert_encoding($message, 'UTF-8', 'auto')."\n";
	}
	
	/**
	 * Запись лога в файл
	 * 
	 * @param string $message Текст сообщения
	 */
	public static function file($message)
	{
		file_put_contents(self::dir()."/".date('Y-m-d').".txt", "<b>[".date('H:i:s')."]:</b> ".mb_convert_encoding($message, 'UTF-8', 'auto')."\r\n", FILE_APPEND);
	}
	
	/**
	 * Запись лога в файл и вывод в консоль
	 * 
	 * @param string $message Текст сообщения
	 */
	public static function send($message)
	{
		echo "[".date('H:i:s')."]: ".$message."\n";
		file_put_contents(self::dir()."/".date('Y-m-d').".txt", "<b>[".date('H:i:s')."]:</b> ".mb_convert_encoding($message, 'UTF-8', 'auto')."\r\n", FILE_APPEND);
	}
	
	/**
	 * Создание нужных директорий и возврат пути
	 * 
	 * @return string Путь к директории с логами
	 */
	private static function dir()
	{
		if (!file_exists(LOGS) or !is_dir(LOGS)) {
			mkdir(LOGS);
		}
		return LOGS;
	}
}