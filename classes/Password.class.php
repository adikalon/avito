<?php

/**
 * Смена пароля
 */
class Password
{
	/**
	 * Проверяет введенные данные при смене пароля
	 *
	 * @param string $login Логин пользователя, которому следует поменять пароль
	 * @param string $old Старый пароль
	 * @param string $new Новый пароль
	 * @param string $renew Новый пароль еще раз
	 * @return false Переданы некорректные параметры
	 * @return null Логин отсутствует в БД
	 * @return 1 Некорректный старый пароль
	 * @return 2 Новые пароли не совпадают
	 * @return array id - id пользователя в БД, login - логин, password - новый пароль
	 */
	public static function checkRePass($login = false, $old = false, $new = false, $renew = false)
	{
		if (!is_string($old) or !is_string($new) or !is_string($renew) or !is_string($login)) {
			return false;
		}
		$login = DB::connect()->quote($login);
		$old = trim($old);
		$sql = "SELECT id, pass FROM users WHERE login=$login LIMIT 1";
		$user = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if (!$user) {
			return null;
		}
		if (!password_verify($old, $user->pass)) {
			return 1;
		}
		$new = trim($new);
		$renew = trim($renew);
		if ($new != $renew) {
			return 2;
		}
		return [
			'id' => $user->id,
			'login' => $login,
			'password' => password_hash($new, PASSWORD_DEFAULT),
		];
	}
	
	/**
	 * Сохраняет новый пароль в БД
	 *
	 * @param string $id идентификатор пользователя в БД, которому следует изменить пароль
	 * @param string $password Хэш нового пароля
	 * @return false Переданы некорректные параметры
	 * @return null Пользователь отсутствует в БД
	 * @return true Пароль изменен
	 */
	public static function saveNewPass($id = false, $password = false)
	{
		if (!is_string($id) or !is_string($password)) {
			return false;
		}
		$password = DB::connect()->quote($password);
		$sql = "UPDATE users SET pass=$password WHERE id=$id";
		if (DB::connect()->exec($sql)) {
			return true;
		} else {
			return null;
		}
	}
	
	/**
	 * Собирательный метод. Задействует все необходимые для изменения пароля методы
	 *
	 * @param string $id идентификатор пользователя в БД, которому следует изменить пароль
	 * @param string $password Хэш нового пароля
	 * @return false Переданы некорректные параметры
	 * @return null Проблема с БД
	 * @return true Пароль изменен
	 * @return 1 Некорректный старый пароль
	 * @return 2 Новые пароли не совпадают
	 */
	public static function rePass($login = false, $old = false, $new = false, $renew = false)
	{
		if (!is_string($old) or !is_string($new) or !is_string($renew) or !is_string($login)) {
			return false;
		}
		$check = self::checkRePass($login, $old, $new, $renew);
		if (!is_array($check)) {
			switch ($check) {
				case 1:
					return 1;
				case 2:
					return 2;
				default:
					return null;
			}
		}
		if (!isset($check['id']) or !isset($check['password'])) {
			return null;
		}
		return Password::saveNewPass($check['id'], $check['password']);
	}
}
