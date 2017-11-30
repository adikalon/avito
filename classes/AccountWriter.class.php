<?php

/**
 * Работа с БД для аккаунтов
 */
class AccountWriter
{
	
	/**
	 * Обновит или добавит новый аккаунт в БД
	 *
	 * Информация возвращаемого массива:
	 * 
	 * 'login' - Логин аккаунта
	 * 
	 * 'status' - Статус авторизации. Null - не авторизован. False - заблокирован. True - активен
	 * 
	 * 'captcha' - Просило каптчу. False - не просило. True - просило
	 * 
	 * 'new' - Новая или обновленная запись. Null - неизвестно. True - новая. False - обновленная
	 * @param array $accounts Массив аккаунтов, полученных при помощи Account::auth()
	 * @return false Передан некорректный параметр
	 * @return array Массив добавленных и обновленных аккаунтов с информацией о занесении или причинах незанесения в БД
	 */
	public static function insertOrUpdate($accounts)
	{
		if (!is_array($accounts) or empty($accounts)) {
			return false;
		}
		$results = [];
		foreach ($accounts as $account) {
			if (!$account['status']) {
				// Если captcha === true - требуется ввод каптчи
				// В противном случае аккаунт заблокирован, либо введены неверные логин или пароль
				$results[] = self::getElemsIns($account['login'], $account['status'], $account['captcha'], null);
				continue;
			}
			$login = DB::connect()->quote($account['login']);
			$sql = "SELECT id FROM accounts WHERE login=$login LIMIT 1";
			$acc = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
			if ($acc) {
				if (self::update($acc->id, $account)) {
					// Запись обновлена
					$results[] = self::getElemsIns($account['login'], $account['status'], $account['captcha'], false);
				} else {
					// Ошибка обновления в БД
					$results[] = self::getElemsIns($account['login'], null, $account['captcha'], false);
				}
			} else {
				if (self::insert($account)) {
					// Запись добавлена
					$results[] = self::getElemsIns($account['login'], $account['status'], $account['captcha'], true);
				} else {
					// Ошибка вставки в БД
					$results[] = self::getElemsIns($account['login'], null, $account['captcha'], true);
				}
			}
		}
		return $results;
	}
	
	/**
	 * Получить строку аккаунта login:password из БД по ID
	 *
	 * @param numeric $id Идентификатор записи в БД
	 * @return null Передан неверный параметр
	 * @return string Строка login:password
	 * @return false Запись не была найдена
	 */
	public static function getAccount($id)
	{
		if (!is_numeric($id)) {
			return null;
		}
		$id = DB::connect()->quote($id);
		$sql = "SELECT login, password FROM accounts WHERE id=$id LIMIT 1";
		$account = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if (!$account) {
			return false;
		}
		return "$account->login:$account->password";
	}
	
	/**
	 * Удаляет аккаунт из БД
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
		$sql = "DELETE FROM accounts WHERE id=$id";
		return DB::connect()->exec($sql);
	}
	
	/**
	 * Добавляет новый аккаунт в БД
	 *
	 * @param array $account Элемент массива эккаунтов получаемых при помощи Account::auth()
	 * @return null Передан неверный параметр
	 * @return 1 Запись добавлена
	 * @return 0 Произошла ошибка
	 */
	private static function insert($account)
	{
		if (!is_array($account) or empty($account)) {
			return null;
		}
		$name = DB::connect()->quote($account['name']);
		$login = DB::connect()->quote($account['login']);
		$password = DB::connect()->quote($account['password']);
		$sessid = DB::connect()->quote($account['sessid']);
		$status = DB::connect()->quote($account['status']);
		$sql = "INSERT INTO accounts (name, login, password, sessid, status) VALUES ($name, $login, $password, $sessid, $status)";
		return DB::connect()->exec($sql);
	}
	
	/**
	 * Обновляет аккаунт в БД
	 *
	 * @param array $account Элемент массива эккаунтов получаемых при помощи Account::auth()
	 * @return null Передан неверный параметр
	 * @return 1 Запись обновлена
	 * @return 0 Произошла ошибка
	 */
	private static function update($id, $account)
	{
		if (!is_array($account) or empty($account)) {
			return null;
		}
		$name = DB::connect()->quote($account['name']);
		$login = DB::connect()->quote($account['login']);
		$password = DB::connect()->quote($account['password']);
		$sessid = DB::connect()->quote($account['sessid']);
		$status = DB::connect()->quote($account['status']);
		$sql = "UPDATE accounts SET name=$name, password=$password, sessid=$sessid, status=$status WHERE id=$id";
		return DB::connect()->exec($sql);
	}
	
	/**
	 * Возвращает массив для заполнения в AccountWriter::insertOrUpdate()
	 *
	 * @param string $login Логин аккаунта
	 * @param string $status Статус авторизации
	 * @param boolean $captcha Был ли запрос на ввод каптчи
	 * @param boolean $new Новая или обновленная запись
	 * @return array Массив параметров аккаунта
	 */
	private static function getElemsIns($login, $status, $captcha, $new)
	{
		return [
			'login' => $login,
			'status' => $status,
			'captcha' => $captcha,
			'new' => $new,
		];
	}
}
