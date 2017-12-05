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
	 * @param string $link Ссылка на страницу с которой необходимо получить токены
	 * @return null Не удалось соедениться с avito.ru
	 * @return false Передан некорректный параметр
	 * @return array Массив с ключами 'token', 'value' и 'ip'
	 */
	public static function getToken($link = false, $sessid = false)
	{
		if (!is_string($link)) {
			return false;
		}
		$options = [
			CURLOPT_ENCODING => '',
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT => Request::$namedUserAgent,
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		if (is_string($sessid)) {
			$options[CURLOPT_COOKIE] = "sessid=$sessid; auth=1";
			$options[CURLOPT_COOKIEJAR] = TEMP.'/cookie.txt';
		}
		$page = Request::curl($link, $options);
		if (!is_string($page)) {
			return null;
		}
		$token = false;
		$value = false;
		$ip = false;
		preg_match('/.*Доступ временно ограничен.*/U', $page, $match);
		if (isset($match[0]) and !empty($match[0])) {
			$ip = true;
		}
		preg_match('/.*name="token\[(\d+)\].*/', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$token = $match[1];
		}
		preg_match('/.*name="token\[\d+\]"\s?value="(.+)">.*/', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$value = $match[1];
		}
		if (is_string($sessid)) {
			$searchText = phpQuery::newDocument($page)->find('button.write-message-btn.button-origin_large-extra')->eq(0);
			$sendText = trim($searchText->text());
			$searchText->unloadDocument();
			if (strpos($sendText, 'Написать сообщение') === false and strpos($sendText, 'Откликнуться') === false) {
				return false;
			}
		}
		return [
			'token' => $token,
			'value' => $value,
			'ip' => $ip,
		];
	}
	
	/**
	 * Получение sessid и другой инфомаии
	 * 
	 * Возвращаемый массив содержит следующую информацию:
	 * 
	 * name - Имя владельца аккаунта. Не false если заблокирован
	 * 
	 * block - True - если аккаунт заблокирован. True - не заблокирован
	 * 
	 * sessid - Для авторизации по cookies
	 * 
	 * captcha - True - если была запрошена каптча. False - каптчи не было
	 * 
	 * nologpas - True - если неверные логин или пароль. False - такой ошибки не обнаружено
	 *
	 * @param string $login Логин
	 * @param string $password Пароль
	 * @param string $token Ключ токена полученный из Acount::getToken()
	 * @param string $value Значение токена полученное из Acount::getToken()
	 * @return null Не удалось соедениться с avito.ru
	 * @return false Переданы некорректные параметры
	 * @return array Массив данных
	 */
	private static function getSessid($login = false, $password = false, $token = false, $value = false)
	{
		if (!is_string($login) or !is_string($password) or !is_string($token) or !is_string($value)) {
			return false;
		}
		$options = [
			CURLOPT_URL => 'https://www.avito.ru/profile/login',
			CURLOPT_HEADER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "next=/profile&login=$login&password=$password&quick_expire=&token[$token]=$value",
			CURLOPT_ENCODING => '',
			CURLOPT_USERAGENT => Request::$namedUserAgent,
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Origin: https://www.avito.ru",
				"Referer: https://www.avito.ru/profile/login",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		$page = Request::curl(false, $options);
		if (!is_string($page)) {
			return null;
		}
		$block = false;
		$sessid = false;
		$captcha = false;
		$nologpas = false;
		$name = false;
		$reset = false;
		preg_match('/.*Ваш аккаунт заблокирован.*/U', $page, $match);
		if (isset($match[0]) and !empty($match[0])) {
			$block = true;
		}
		preg_match('/.*В целях безопасности ваш пароль был сброшен.*/U', $page, $match);
		if (isset($match[0]) and !empty($match[0])) {
			$reset = true;
		}
		preg_match('/.*sessid=(.+);.*/U', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$sessid = $match[1];
		}
		preg_match('/.*(form-captcha-input).*/', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$captcha = true;
		}
		preg_match('/.*Неправильная пара электронная почта.*/U', $page, $match);
		if (isset($match[0]) and !empty($match[0])) {
			$nologpas = true;
		}
		preg_match('/.*<span class="fader">(.+)<\/span>.*/U', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$name = $match[1];
		}
		return [
			'name' => $name,
			'block' => $block,
			'sessid' => $sessid,
			'captcha' => $captcha,
			'nologpas' => $nologpas,
			'reset' => $reset,
		];
	}
	
	/**
	 * Получение информации о статусе аккаунта и его владельце
	 * 
	 * Возвращаемый массив содержит следующую информацию:
	 * 
	 * name - Имя владельца аккаунта. False - не удалось найти имя
	 * 
	 * auth - True - авторизировался. False - не удалось авторизироваться
	 *
	 * @param string $sessid Строка sessid полученная методом Account::getSessid()
	 * @return null Не удалось соедениться с avito.ru
	 * @return false Переданы некорректные параметры
	 * @return array Массив данных об аккаунте
	 */
	public static function getInfo($sessid = false)
	{
		if (!is_string($sessid)) {
			return false;
		}
		$options = [
			CURLOPT_URL => 'https://www.avito.ru/profile',
			CURLOPT_ENCODING => '',
			CURLOPT_COOKIE => "sessid=$sessid; auth=1",
			CURLOPT_USERAGENT => Request::$namedUserAgent,
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Origin: https://www.avito.ru",
				"Referer: https://www.avito.ru/profile/login",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		$page = Request::curl(false, $options);
		if (!is_string($page)) {
			return null;
		}
		$auth = false;
		$name = false;
		preg_match('/.*<span class="fader">(.+)<\/span>.*/U', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$name = $match[1];
			$auth = true;
		}
		return [
			'name' => $name,
			'auth' => $auth,
		];
	}
	
	/**
	 * Возвращает массив для заполнения в Account::auth()
	 * 
	 * @param mixed $name Имя владельца аккаунта. False - в случае неудачной авторизации
	 * @param string $login Логин аккаунта
	 * @param string $password Пароль аккаунта
	 * @param mixed $sessid Sessid для авторизации по cookies. False - не получено
	 * @param boolean $auth True - Аккаунт авторизирован. False - не авторизирован
	 * @param boolean $captcha True - Была запрошена каптча. False - каптча не запрашивалась
	 * @param boolean $block True - Аккаунт заблокирован. False - не заблокирован
	 * @param boolean $nologpas True - Некорректный логин или пароль. False - корректный
	 * @return array Массив параметров аккаунта
	 */
	private static function getElemsAuth($name, $login, $password, $sessid, $auth, $captcha, $block, $nologpas, $ip, $reset)
	{
		return [
			'name' => $name,
			'login' => $login,
			'password' => $password,
			'sessid' => $sessid,
			'auth' => $auth,
			'captcha' => $captcha,
			'block' => $block,
			'nologpas' => $nologpas,
			'ip' => $ip,
			'reset' => $reset,
		];
	}

	/**
	 * Авторизует переданные аккаунты и отдает массив массивов с результатами
	 * 
	 * Возвращаемый массив массивов аккаунтов со следующей информацией:
	 * 
	 * name - Имя владельца аккаунта. False - если некорректный логин или пароль либо каптча
	 * 
	 * login - Логин
	 * 
	 * password - Пароль
	 * 
	 * sessid - Sessid для авторизации. False - если не удалось связаться с avito.ru
	 * 
	 * auth - True - авторизирован. False - не авторизирован
	 * 
	 * captcha - True - был запрошен ввод каптчи. False - не был
	 * 
	 * block - True - аккаунт заблокирован. False - не заблокирован либо запрошен ввод каптчи, либо неверный логин/пароль
	 * 
	 * nologpas - True - не корректный логин/пароль. False - корректный либо запрошен ввод каптчи
	 * @param array $accounts Массив аккаунтов полученых при помощи Account::parse()
	 * @return false Передан некорректный параметр
	 * @return array Массив аккаунтов с информацией об авторизации
	 */
	public static function auth($accounts = false)
	{
		if (!is_array($accounts) or empty($accounts)) {
			return false;
		}
		$results = [];
		foreach ($accounts as $login => $password) {
			$tokens = self::getToken('https://www.avito.ru/profile/login');
			if (!is_array($tokens)) {
				// Не удалось соедениться с avito.ru
				$results[] = self::getElemsAuth(false, $login, $password, false, false, false, false, false, false, false);
				continue;
			}
			if ($tokens['ip']) {
				// Доступ веменно ограничен по IP
				$results[] = self::getElemsAuth(false, $login, $password, false, false, false, false, false, true, false);
				continue;
			}
			$token = $tokens['token'];
			$value = $tokens['value'];
			$sessid = self::getSessid($login, $password, $token, $value);
			if (!is_array($sessid)) {
				// Не удалось соедениться с avito.ru
				$results[] = self::getElemsAuth(false, $login, $password, false, false, false, false, false, false, false);
				continue;
			}
			$info = self::getInfo($sessid['sessid']);
			if (!is_array($info)) {
				// Не удалось соедениться с avito.ru
				$results[] = self::getElemsAuth(
					$sessid['name'],
					$login,
					$password,
					$sessid['sessid'],
					false,
					$sessid['captcha'],
					$sessid['block'],
					$sessid['nologpas'],
					false,
					$sessid['reset']
				);
				continue;
			}
			$results[] = self::getElemsAuth(
				$info['name'],
				$login,
				$password,
				$sessid['sessid'],
				$info['auth'],
				$sessid['captcha'],
				$sessid['block'],
				$sessid['nologpas'],
				false,
				$sessid['reset']
			);
		}
		return $results;
	}
	
	/**
	 * Собирательный метод. Задействует все необходимые для добавления/обновления аккаунтов методы
	 * 
	 * @param string $accounts Список аккаунтов в формате login:pass
	 * @return false Передан некорректный параметр
	 * @return null Не удалось записать в БД
	 * @return array Массив добавленных и обновленных аккаунтов с информацией о занесении или причинах незанесения в БД
	 */
	public static function setAccounts($accounts)
	{
		$parse = self::parse($accounts);
		if (!$parse) {
			return false;
		}
		$auth = self::auth($parse);
		if (!$auth) {
			return false;
		}
		$insert = AccountWriter::insertOrUpdate($auth);
		if (!$insert) {
			return null;
		}
		return $insert;
	}
	
	/**
	 * Возвращает результирующее информационное сообщение при добавлении аккаунта
	 * 
	 * @param array $account Массив второго уровня от массива полученного в AccountWriter::insertOrUpdate()
	 * @return string Сообщение
	 */
	public static function getAuthMessage($account)
	{
		if ($account['result']) {
			if ($account['block']) {
				return "Аккаунт <b>".$account['login']."</b> заблокирован но ".($account['new'] ? "добавлен в базу" : "обновлен в базе");
			} else {
				if ($account['auth']) {
					return "Аккаунт <b>".$account['login']."</b> авторизирован и ".($account['new'] ? "добавлен в базу" : "обновлен в базе");
				} else {
					if ($account['ip']) {
						return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Доступ временно заблокирован по IP";
					} elseif ($account['reset']) {
						return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Был был сброшен пароль";
					} elseif ($account['captcha']) {
						return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Был затребован ввод каптчи";
					} elseif ($account['nologpas']) {
						return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Неверный логин или пароль";
					} else {
						return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Неизвестная ошибка";
					}
				}
			}
		} elseif (null === $account['result']) {
			if ($account['ip']) {
				return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Доступ временно заблокирован по IP";
			} elseif ($account['reset']) {
				return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Был был сброшен пароль";
			} elseif ($account['captcha']) {
				return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Был затребован ввод каптчи";
			} elseif ($account['nologpas']) {
				return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Неверный логин или пароль";
			} else {
				return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Неизвестная ошибка";
			}
		} else {
			return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Ошибка соединения с базой данных. Попробуйте повторить попытку позже";
		}
	}
	
	/**
	 * Возвращает причину по которой аккаунт не активен
	 * 
	 * @param array $account Массив второго уровня от массива полученного в AccountWriter::insertOrUpdate()
	 * @return string Сообщение
	 */
	public static function getNonAuthCause($account)
	{
		if ($account->auth) {
			return '';
		} else {
			if ($account->ip) {
				return 'Блок по IP';
			} elseif ($account->reset) {
				return 'Сброшен пароль';
			} elseif ($account->captcha) {
				return 'Каптча';
			} elseif ($account->nologpas) {
				return 'Логин или пароль';
			} elseif ($account->block) {
				return 'Заблокирован';
			} else {
				return 'Неизвестно';
			}
		}
	}
}
