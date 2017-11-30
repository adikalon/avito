<?php

/**
 * Соединение с базой данных
 */
class DB
{
	static private $connect = null;
	
	/**
	 * Дескриптор соединения с БД
	 */
	static public function connect()
	{
		if (null === self::$connect) {
			self::$connect = new PDO('sqlite:'.DATABASES.'/database.db') ;
		}
		return self::$connect;
	}
}
