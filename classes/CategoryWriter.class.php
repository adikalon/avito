<?php

/**
 * Работа с БД для категорий
 */
class CategoryWriter
{
	/**
	 * Обновит или добавит новую категорию в БД
	 *
	 * Информация возвращаемого массива:
	 * 
	 * 'name' - Название категории
	 * 
	 * 'link' - Ссылка на категорию
	 * 
	 * 'status' - Занесено ли в базу. False - произошла ошибка. True - занесено
	 * 
	 * 'new' - Новая или обновленная запись. True - новая. False - обновленная
	 * @param string $name Название категории
	 * @param string $link Ссылка на категорию
	 * @return false Передан некорректный параметр
	 * @return array Массив с инфомаией о категоии и ее добавлении или обновлении в БД
	 */
	public static function insertOrUpdate($name = false, $link = false)
	{
		if (!is_string($link) or empty($link) or !$link) {
			return false;
		}
		if (!is_string($name) or empty($name) or !$name) {
			return false;
		}
		$sql = "SELECT id FROM categories WHERE link='$link' LIMIT 1";
		$category = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if ($category) {
			if (self::update($category->id, $name, $link)) {
				// Запись обновлена
				return self::getElemsIns($name, $link, true, false);
			} else {
				// Ошибка обновления в БД
				return self::getElemsIns($name, $link, false, false);
			}
		} else {
			if (self::insert($name, $link)) {
				// Запись добавлена
				return self::getElemsIns($name, $link, true, true);
			} else {
				// Ошибка вставки в БД
				return self::getElemsIns($name, $link, false, true);
			}
		}
	}
	
	/**
	 * Добавляет новую категорию в БД
	 *
	 * @param string $name Название категории
	 * @param string $link Ссылка на категоию
	 * @return null Переданы некорректные параметры
	 * @return 1 Запись добавлена
	 * @return 0 Произошла ошибка
	 */
	private static function insert($name = false, $link = false)
	{
		if (!is_string($name) or empty($name) or !$name) {
			return null;
		}
		if (!is_string($link) or empty($link) or !$link) {
			return null;
		}
		$name = DB::connect()->quote($name);
		$sql = "INSERT INTO categories (name, link) VALUES ($name, '$link')";
		return DB::connect()->exec($sql);
	}
	
	/**
	 * Обновляет категорию в БД
	 *
	 * @param numeric $id Идентификатор записи в БД
	 * @param string $name Название категории
	 * @param string $link Ссылка на категоию
	 * @return null Переданы некорректные параметры
	 * @return 1 Запись обновлена
	 * @return 0 Произошла ошибка
	 */
	private static function update($id = false, $name = false, $link = false)
	{
		if (!is_numeric($id) or !$id) {
			return null;
		}
		if (!is_string($name) or empty($name) or !$name) {
			return null;
		}
		if (!is_string($link) or empty($link) or !$link) {
			return null;
		}
		$name = DB::connect()->quote($name);
		$sql = "UPDATE categories SET name=$name, link='$link' WHERE id=$id";
		return DB::connect()->exec($sql);
	}
	
	/**
	 * Удаляет категорию из БД
	 *
	 * @param numeric $id Идентификатор записи в БД
	 * @return null Передан неверный параметр
	 * @return 1 Запись удалена
	 * @return 0 Запись отсутствует в БД
	 */
	public static function delete($id)
	{
		if (!is_numeric($id)) {
			return null;
		}
		$sql = "DELETE FROM categories WHERE id=$id";
		return DB::connect()->exec($sql);
	}
	
	/**
	 * Возвращает массив для заполнения в CatoryWriter::insertOrUpdate()
	 *
	 * @param string $name Название категории
	 * @param string $link Ссылка на категорию
	 * @param boolean $status далось или нет добавить аись в БД
	 * @param boolean $new Новая или обновленная запись
	 * @return array Массив параметров аккаунта
	 */
	private static function getElemsIns($name, $link, $status, $new)
	{
		return [
			'name' => $name,
			'link' => $link,
			'status' => $status,
			'new' => $new,
		];
	}
}
