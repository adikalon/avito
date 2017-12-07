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
			$login = DB::connect()->quote($account['login']);
			$sql = "SELECT id FROM accounts WHERE login=$login LIMIT 1";
			$acc = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
			if ($acc) {
				if (self::update($acc->id, $account)) {
					// Запись обновлена
					$results[] = self::getElemsIns($account, true, false);
				} else {
					// Ошибка обновления в БД
					$results[] = self::getElemsIns($account, false, false);
				}
			} else {
				if (!$account['name']) {
					$results[] = self::getElemsIns($account, null, true);
					continue;
				}
				if (self::insert($account)) {
					// Запись добавлена
					$results[] = self::getElemsIns($account, true, true);
				} else {
					// Ошибка вставки в БД
					$results[] = self::getElemsIns($account, false, true);
				}
			}
		}
		return $results;
	}
	
	/**
	 * Получить строку аккаунта login:password{proxy} из БД по ID
	 *
	 * @param numeric $id Идентификатор записи в БД
	 * @return null Передан неверный параметр
	 * @return string Строка login:password
	 * @return false Запись не была найдена
	 */
	public static function getAccount($id = false)
	{
		if (!is_numeric($id)) {
			return null;
		}
		$id = DB::connect()->quote($id);
		$sql = "SELECT login, password, proxy FROM accounts WHERE id=$id LIMIT 1";
		$account = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if (!$account) {
			return false;
		}
		if (!empty($account->proxy)) {
			$proxy = '{'.$account->proxy.'}';
			return "$account->login:$account->password".$proxy;
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
		$proxy = DB::connect()->quote($account['proxy']);
		$sessid = DB::connect()->quote($account['sessid']);
		$auth = $account['auth'] ? 1 : 0;
		$captcha = $account['captcha'] ? 1 : 0;
		$block = $account['block'] ? 1 : 0;
		$nologpas = $account['nologpas'] ? 1 : 0;
		$ip = $account['ip'] ? 1 : 0;
		$reset = $account['reset'] ? 1 : 0;
		$http = $account['http'] ? 1 : 0;
		$sql = "INSERT INTO accounts (name, login, password, proxy, sessid, auth, captcha, block, nologpas, ip, reset, http) VALUES ($name, $login, $password, $proxy, $sessid, $auth, $captcha, $block, $nologpas, $ip, $reset, $http)";
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
		$proxy = DB::connect()->quote($account['proxy']);
		$sessid = DB::connect()->quote($account['sessid']);
		$auth = $account['auth'] ? 1 : 0;
		$captcha = $account['captcha'] ? 1 : 0;
		$block = $account['block'] ? 1 : 0;
		$nologpas = $account['nologpas'] ? 1 : 0;
		$ip = $account['ip'] ? 1 : 0;
		$reset = $account['reset'] ? 1 : 0;
		$http = $account['http'] ? 1 : 0;
		if (!$account['name']) {
			$sql = "UPDATE accounts SET password=$password, proxy=$proxy, sessid=$sessid, auth=$auth, captcha=$captcha, block=$block, nologpas=$nologpas, ip=$ip, reset=$reset, http=$http WHERE id=$id";
		} else {
			$sql = "UPDATE accounts SET name=$name, password=$password, proxy=$proxy, sessid=$sessid, auth=$auth, captcha=$captcha, block=$block, nologpas=$nologpas, ip=$ip, reset=$reset, http=$http WHERE id=$id";
		}
		return DB::connect()->exec($sql);
	}

	/**
	 * Возвращает массив для заполнения в AccountWriter::insertOrUpdate()
	 *
	 * @param array $account Массив с инфомаией об авторизации аккаунта
	 * @param mixed $result True - удалось записать данные в БД. False - не удалось. Null - не принималась попытка
	 * @param mixed $new True - новая запись. False - обновленная. Null - не авторизированная
	 * @return array Массив параметров аккаунта
	 */
	private static function getElemsIns($account, $result, $new)
	{
		return [
			'name' => $account['name'],
			'login' => $account['login'],
			'auth' => $account['auth'],
			'captcha' => $account['captcha'],
			'block' => $account['block'],
			'nologpas' => $account['nologpas'],
			'ip' => $account['ip'],
			'reset' => $account['reset'],
			'http' => $account['http'],
			'result' => $result,
			'new' => $new
		];
	}
}
