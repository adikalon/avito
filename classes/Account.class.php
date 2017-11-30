<?php

/**
 * Работа с аккаунтами
 */
class Account
{
	
	/**
	 * Парсит список аккаунтов переданных строкой
	 *
	 * @param string $accounts Список аккаунтов в формате login:pass
	 * @return array Массив с логинами и паролями
	 * @return false В случае некорректно составленой строки
	 */
	public static function parse($accounts)
	{
		$result = [];
		$accounts = trim($accounts);
		if (empty($accounts)) {
			return false;
		}
		$accounts = explode("\n", $accounts);
		foreach ($accounts as $account) {
			$symbols = substr_count($account, ':');
			if ($symbols != 1) {
				return false;
			}
			$divide = explode(':', $account);
			$login = trim($divide[0]);
			$pass = trim($divide[1]);
			if (empty($login) or empty($pass)) {
				return false;
			}
			$result[$login] = $pass;
		}
		if (empty($result)) {
			return false;
		}
		return $result;
	}
	
	/**
	 * Получение токенов со страницы для дальнейших действий
	 *
	 * @param string $page Ключевое cлово страницы
	 * @return curl Ошибки метода Request::curl()
	 * @return false В случае неизвестной ошибки
	 * @return array Массив с ключами 'token' и 'value'
	 */
	private static function getToken($page = 'auth')
	{
		switch ($page) {
			case 'auth':
				$page = 'https://www.avito.ru/profile/login';
				break;
		}
		$options = [
			CURLOPT_ENCODING => '',
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		$pageToken = Request::curl($page, $options);
		if (!is_string($pageToken)) {
			return $pageToken;
		}
		preg_match('/.*name="token\[(\d+)\].*/', $pageToken, $match);
		$token = $match[1];
		preg_match('/.*name="token\[\d+\]"\s?value="(.+)">.*/', $pageToken, $match);
		$value = $match[1];
		if (!$token or !$value) {
			return false;
		}
		return [
			'token' => $token,
			'value' => $value,
		];
	}
	
	/**
	 * Получение sessid cookie для авторизации
	 *
	 * @param string $login Логин
	 * @param string $password Пароль
	 * @param string $token Ключ токена полученный из Acount::getToken()
	 * @param string $value Значение токена полученное из Acount::getToken()
	 * @return curl Ошибки метода Request::curl()
	 * @return false В случае неизвестной ошибки
	 * @return string Sessid для cookies
	 */
	private static function getSessid($login = false, $password = false, $token = false, $value = false)
	{
		if (!$login or !$password or !$token or !$value) {
			return 1;
		}
		$options = [
			CURLOPT_URL => 'https://www.avito.ru/profile/login',
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "next=/profile&login=$login&password=$password&quick_expire=&token[$token]=$value",
			CURLOPT_ENCODING => '',
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Origin: https://www.avito.ru",
				"Referer: https://www.avito.ru/profile/login",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		$pageSessid = Request::curl(false, $options);
		if (!is_string($pageSessid)) {
			return $pageSessid;
		}
		preg_match('/.*sessid=(.+);.*/U', $pageSessid, $match);
		$sessid = $match[1];
		if (!$sessid) {
			return false;
		}
		return $sessid;
	}
	
	/**
	 * Получение информации о статусе аккаунта и его владельце
	 *
	 * @param string $sessid Строка sessid полученная методом Account::getSessid()
	 * @return curl Ошибки метода Request::curl()
	 * @return false В случае неизвестной ошибки
	 * @return null Неверно передан параметр $sessid
	 * @return array Массив данных об аккаунте
	 */
	private static function getInfo($sessid = false)
	{
		if (!$sessid or !is_string($sessid) or empty($sessid)) {
			return null;
		}
		$options = [
			CURLOPT_URL => 'https://www.avito.ru/profile',
			CURLOPT_ENCODING => '',
			CURLOPT_COOKIE => "sessid=$sessid; auth=1",
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Origin: https://www.avito.ru",
				"Referer: https://www.avito.ru/profile/login",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		$pageInfo = Request::curl(false, $options);
		if (!is_string($pageInfo)) {
			return $pageInfo;
		}
		preg_match('/.*<span class="fader">(.+)<\/span>.*/U', $pageInfo, $match);
		$name = $match[1];
		if (!$name) {
			return false;
		}
		/*
		 * Тут необходимо получить auth заблокирован или активен
		 */
		return [
			'name' => $name,
			'auth' => '',
		];
	}
	
	/**
	 * Возвращает массив для заполнения в Account::auth()
	 * @param mixed $name Имя хозяина аккаунту. Null в случае неудачной авторизации
	 * @param string $login Логин аккаунта
	 * @param string $password Пароль аккаунта
	 * @param mixed $sessid Sessid для cookies. Null в случае неудачной авторизации
	 * @param mixed $status True - аккаунт активен. False - аккаунт заблокирован. Null в случае неудачной авторизации
	 * @return array Массив параметров аккаунта
	 */
	private static function getElemsAuth($name, $login, $password, $sessid, $status)
	{
		return [
			'name' => $name,
			'login' => $login,
			'password' => $password,
			'sessid' => $sessid,
			'status' => $status,
		];
	}

	/**
	 * Авторизует и добавляет в БД переданные аккаунты
	 * 
	 * Еслимент 'auth' возвращаемого массива отвечает за статус авторизации:
	 * 
	 * null - Не удалось авторизироваться
	 * 
	 * false - Аккаунт заблокирован
	 * 
	 * true - Аккаунт активен
	 * @param array $accounts Массив аккаунтов полученых при помощи Account::parse()
	 * @return false Ошибка оединения с avito.ru
	 * @return null Неверно передан параметр $accounts
	 * @return array Массив аккаунтов с информацией об авторизации
	 */
	public static function auth($accounts = false)
	{
		if (!$accounts or empty($accounts) or ! is_array($accounts)) {
			return null;
		}
		$results = [];
		foreach ($accounts as $login => $password) {
			$tokens = self::getToken('auth');
			if (!is_array($tokens)) {
				// Не удалось соедениться с сервером
				return false;
			}
			$token = $tokens['token'];
			$value = $tokens['value'];
			$sessid = self::getSessid($login, $password, $token, $value);
			if (!is_string($sessid)) {
				// Не удалось авторизироваться
				$results[] = self::getElemsAuth(null, $login, $password, null, null);
				continue;
			}
			$info = self::getInfo($sessid);
			if (!is_array($info)) {
				// Не удалось авторизироваться
				$results[] = self::getElemsAuth(null, $login, $password, $sessid, null);
				continue;
			}
			$results[] = self::getElemsAuth($info['name'], $login, $password, $sessid, $info['auth']);
		}
		return $results;
	}
}
