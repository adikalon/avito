<?php

/**
 * Работа с аккаунтами
 */
class Account
{
	/**
	 * временное хранилище для sessid
	 */
	private static $sessid = '';
	
	/**
	 * временное хранилище для имени
	 */
	private static $name = '';

	/**
	 * Парсит список аккаунтов переданных строкой
	 *
	 * @param string $accounts Список аккаунтов в формате login:pass{127.0.0.1:80}
	 * @return array Массив с логинами, паролями и прокси
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
			if ($symbols < 1) {
				return false;
			}
			$login = trim(stristr($account, ':', true));
			if (empty($login)) {
				return false;
			}
			$passProxy = trim(substr(stristr($account, ':'), 1));
			$reg = '{((?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?):\d{2,5})}';
			preg_match($reg, $passProxy, $match);
			if (isset($match[1]) and !empty($match[1])) {
				$proxy = $match[1];
				$password = trim(str_replace(['{', '}', $proxy], '', $passProxy));
			} else {
				$password = trim($passProxy);
				$proxy = '';
			}
			if (empty($password)) {
				return false;
			}
			
			$result[] = [
				'login' => $login,
				'password' => $password,
				'proxy' => $proxy,
			];
		}
		if (empty($result)) {
			return false;
		}
		return $result;
	}
	
	/**
	 * Получение токенов со страницы и/или проверка на запрет доступа по IP
	 *
	 * @param string $link Ссылка на страницу с которой необходимо получить токены
	 * @param string $sessid Не обязательный параметр. Для проверки на возможность отправлять сообщение автору объявления
	 * @param string $proxy Прокси сервер
	 * @return false Передан некорректный параметр.
	 * Если передана $sessid, может также вернуть false, если возможность отправлять сообщение автору отсутствует
	 * @return array Массив с ключами:
	 * 
	 * token - токен
	 * 
	 * value - значение токена
	 * 
	 * ip - true, если доступ ограничен по ip или false, если разрешен
	 * 
	 * http - True - прокси умерла. False - жива
	 */
	public static function getToken($link = false, $sessid = false, $proxy = false)
	{
		if (!is_string($link)) {
			return false;
		}
		$options = [
			CURLOPT_ENCODING => '',
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => true,
			CURLOPT_USERAGENT => Request::$namedUserAgent,
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		if (is_string($sessid)) {
			$options[CURLOPT_COOKIE] = "sessid=$sessid; auth=1";
			$options[CURLOPT_COOKIEJAR] = TEMP.'/category.txt';
		}
		if (is_string($proxy) and !empty($proxy)) {
			$options[CURLOPT_PROXY] = $proxy;
		}
		$page = Request::curl($link, $options);
		$http = false;
		$token = false;
		$value = false;
		$ip = false;
		if (!is_string($page) or empty($page)) {
			$http = true;
		}
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
			'http' => $http,
			'token' => $token,
			'value' => $value,
			'ip' => $ip,
		];
	}
	
	/**
	 * Получение sessid и информации об авторизации
	 *
	 * @param string $login Логин
	 * @param string $password Пароль
	 * @param string $token Ключ токена полученный из Acount::getToken()
	 * @param string $value Значение токена полученное из Acount::getToken()
	 * @param string $captcha Код капчи
	 * @param string $proxy Прокси сервер
	 * @return false Переданы некорректные параметры
	 * @return array Массив данных об авторизации:
	 * 
	 * name - Имя владельца аккаунта. False - не удалось получить
	 * 
	 * block - True - если аккаунт заблокирован. False - не заблокирован
	 * 
	 * sessid - Для авторизации по cookies
	 * 
	 * captcha - Строка капчи или false, если ее не было
	 * 
	 * nologpas - True - если неверные логин или пароль. False - такой ошибки не обнаружено
	 * 
	 * http - True - прокси умерла. False - жива
	 */
	private static function getSessid($login = false, $password = false, $token = false, $value = false, $captcha = false, $proxy = false)
	{
		if (!is_string($login) or !is_string($password) or !is_string($token) or !is_string($value)) {
			return false;
		}
		$options[CURLOPT_URL] = 'https://www.avito.ru/profile/login';
		$options[CURLOPT_HEADER] = true;
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_ENCODING] = '';
		if ($captcha === false) {
			$options[CURLOPT_POSTFIELDS] = "next=/profile&login=$login&password=$password&quick_expire=&token[$token]=$value";
		} else {
			$options[CURLOPT_POSTFIELDS] = "next=/profile&login=$login&password=$password&quick_expire=&token[$token]=$value&captcha=$captcha";
			$options[CURLOPT_COOKIEFILE] = TEMP.'/account.txt';
		}
		$options[CURLOPT_COOKIEJAR] = TEMP.'/account.txt';
		$options[CURLOPT_USERAGENT] = Request::$namedUserAgent;
		$options[CURLOPT_HTTPHEADER] = [
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
			"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
			"Origin: https://www.avito.ru",
			"Referer: https://www.avito.ru/profile/login",
			"Upgrade-Insecure-Requests: 1",
		];
		if (is_string($proxy) and !empty($proxy)) {
			$options[CURLOPT_PROXY] = $proxy;
		}
		$page = Request::curl(false, $options);
		$http = false;
		$block = false;
		$sessid = false;
		$captcha = false;
		$nologpas = false;
		$name = false;
		$reset = false;
		if (!is_string($page) or empty($page)) {
			$http = true;
		}
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
			self::$sessid = $match[1];
		}
		if (!empty(self::$sessid)) {
			$sessid = self::$sessid;
		}
		preg_match('/.*<span class="fader">(.+)<\/span>.*/U', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			self::$name = $match[1];
		}
		if (!empty(self::$name)) {
			$name = self::$name;
		}
		preg_match('/.*src="\/captcha\?(\d+)".*/', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$captcha = $match[1];
		}
		preg_match('/.*Неправильная пара электронная почта.*/U', $page, $match);
		if (isset($match[0]) and !empty($match[0])) {
			$nologpas = true;
		}
		return [
			'http' => $http,
			'name' => $name,
			'block' => $block,
			'sessid' => $sessid,
			'captcha' => $captcha,
			'nologpas' => $nologpas,
			'reset' => $reset
		];
	}
	
	/**
	 * Получение информации о статусе аккаунта и его владельце
	 * 
	 * @param string $sessid Строка sessid полученная методом Account::getSessid()
	 * @param string $proxy Прокси сервер
	 * @return false Передан некорректные параметры
	 * @return array Массив данных об аккаунте:
	 * 
	 * name - Имя владельца аккаунта. False - не удалось получить имя
	 * 
	 * auth - True - авторизировался. False - не удалось авторизироваться
	 * 
	 * http - True - прокси умерла. False - жива
	 */
	public static function getInfo($sessid = false, $proxy = false)
	{
		if (!is_string($sessid)) {
			return false;
		}
		$options = [
			CURLOPT_URL => 'https://www.avito.ru/profile',
			CURLOPT_ENCODING => '',
			CURLOPT_HEADER => true,
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
		if (is_string($proxy) and !empty($proxy)) {
			$options[CURLOPT_PROXY] = $proxy;
		}
		$page = Request::curl(false, $options);
		$http = false;
		$auth = false;
		$name = false;
		if (!is_string($page) or empty($page)) {
			$http = true;
		}
		$auth = false;
		$name = false;
		preg_match('/.*<span class="fader">(.+)<\/span>.*/U', $page, $match);
		if (isset($match[1]) and !empty($match[1])) {
			$name = $match[1];
			$auth = true;
		}
		return [
			'http' => $http,
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
	 * @param string $proxy Прокси сервер
	 * @param mixed $sessid Sessid для авторизации по cookies. False - не получено
	 * @param boolean $auth True - Аккаунт авторизирован. False - не авторизирован
	 * @param boolean $captcha True - Была запрошена каптча. False - каптча не запрашивалась
	 * @param boolean $block True - Аккаунт заблокирован. False - не заблокирован
	 * @param boolean $nologpas True - Некорректный логин или пароль. False - корректный
	 * @param boolean $ip True - Заблокирован доступ по IP. False - не заблокирован
	 * @param boolean $reset True - Сброшен пароль у аккаунта. False - не сброшен
	 * @param boolean $http True - прокси умерла. False - жива
	 * @return array Массив параметров аккаунта с одноименными именами ключей
	 */
	private static function getElemsAuth($name, $login, $password, $proxy, $sessid, $auth, $captcha, $block, $nologpas, $ip, $reset, $http)
	{
		return [
			'name' => $name,
			'login' => $login,
			'password' => $password,
			'proxy' => $proxy,
			'sessid' => $sessid,
			'auth' => $auth,
			'captcha' => $captcha,
			'block' => $block,
			'nologpas' => $nologpas,
			'ip' => $ip,
			'reset' => $reset,
			'http' => $http
		];
	}

	/**
	 * Авторизует переданные аккаунты и отдает массив массивов с результатами
	 * 
	 * @param array $accounts Массив аккаунтов полученых при помощи Account::parse()
	 * @return false Передан некорректный параметр
	 * @return array Массив аккаунтов с информацией об авторизации:
	 * 
	 * name - Имя владельца аккаунта. False - не удалось получить имя
	 * 
	 * login - Логин
	 * 
	 * password - Пароль
	 * 
	 * proxy - Прокси
	 * 
	 * sessid - Sessid для авторизации. False - если не удалось связаться с avito.ru
	 * 
	 * auth - True - авторизирован. False - не авторизирован
	 * 
	 * captcha - True - был запрошен ввод каптчи. False - не был
	 * 
	 * block - True - аккаунт заблокирован. False - не получена информация о блокировке
	 * 
	 * nologpas - True - не корректный логин/пароль. False - не получена информация
	 * 
	 * ip - True - доступ заблокирован по IP. False - не заблокирован
	 * 
	 * reset - True - сброшен пароль аккаунта. False - не получена информация
	 * 
	 * http - True - прокси умерла. False - жива
	 */
	public static function auth($accounts = false)
	{
		if (!is_array($accounts) or empty($accounts)) {
			return false;
		}
		$results = [];
		foreach ($accounts as $account) {
			$tokens = self::getToken('https://www.avito.ru/profile/login', false, $account['proxy']);
			if ($tokens['http'] or $tokens['ip']) {
				$results[] = self::getElemsAuth(
					false,
					$account['login'],
					$account['password'],
					$account['proxy'],
					false,
					false,
					false,
					false,
					false,
					$tokens['ip'],
					false,
					$tokens['http']
				);
				continue;
			}
			$token = $tokens['token'];
			$value = $tokens['value'];
			$sessid = self::getSessid($account['login'], $account['password'], $token, $value, false, $account['proxy']);
			if ($sessid['http']) {
				$results[] = self::getElemsAuth(
					false,
					$account['login'],
					$account['password'],
					$account['proxy'],
					false,
					false,
					false,
					false,
					false,
					$tokens['ip'],
					false,
					$sessid['http']
				);
				continue;
			}
			if ($sessid['captcha'] !== false) {
				do {
					$code = AntiCaptcha::decode($sessid['captcha']);
					// Если вернулась не строка - отпускаем аккаунт без антикапчи
					if (!is_string($code)) {
						break;
					}
					// Лепим по второму кругу. Пусть уже так будет
					$tokens = self::getToken('https://www.avito.ru/profile/login', false, $account['proxy']);
					if ($tokens['http'] or $tokens['ip']) {
						$results[] = self::getElemsAuth(
							false,
							$account['login'],
							$account['password'],
							$account['proxy'],
							false,
							false,
							false,
							false,
							false,
							$tokens['ip'],
							false,
							$tokens['http']
						);
						continue;
					}
					$token = $tokens['token'];
					$value = $tokens['value'];
					$sessid = self::getSessid($account['login'], $account['password'], $token, $value, $code, $account['proxy']);
					if ($sessid['http']) {
						$results[] = self::getElemsAuth(
							false,
							$account['login'],
							$account['password'],
							$account['proxy'],
							false,
							false,
							false,
							false,
							false,
							$tokens['ip'],
							false,
							$sessid['http']
						);
						continue;
					}
				} while ($sessid['captcha'] !== false);
			}
			$info = self::getInfo($sessid['sessid'], $account['proxy']);
			$results[] = self::getElemsAuth(
				$info['name'],
				$account['login'],
				$account['password'],
				$account['proxy'],
				$sessid['sessid'],
				$info['auth'],
				$sessid['captcha'],
				$sessid['block'],
				$sessid['nologpas'],
				$tokens['ip'],
				$sessid['reset'],
				$info['http']
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
	 * @return array Массив добавленных и обновленных аккаунтов с информацией о занесении или причинах незанесения в БД:
	 * 
	 * name - Имя владельца аккаунта. False - в случае неудачной авторизации
	 * 
	 * login - Логин
	 * 
	 * auth - Пароль
	 * 
	 * captcha - True - был запрошен ввод каптчи. False - не был
	 * 
	 * block - True - аккаунт заблокирован. False - не получена информация о блокировке
	 * 
	 * nologpas - True - не корректный логин/пароль. False - не получена информация
	 * 
	 * ip - True - доступ заблокирован по IP. False - не заблокирован
	 * 
	 * reset - True - сброшен пароль аккаунта. False - не получена информация
	 * 
	 * result - True - записано в БД. False - ошибка соединения с БД. Null - не пыталось записывать (вероятно невернлый логин или пароль)
	 * 
	 * new - Новая или обновленная запись. True - новая. False - обновленная
	 * 
	 * http - True - прокси умерла. False - жива
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
	 * Возвращает результирующее информационное сообщение при добавлении/обновлении аккаунта
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
					if ($account['http']) {
						return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Вероятно неработоспособный прокси-сервер";
					} elseif ($account['ip']) {
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
			if ($account['http']) {
				return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Вероятно неработоспособный прокси-сервер";
			} elseif ($account['ip']) {
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
			return "Аккаунт <b>".$account['login']."</b> не удалось авторизировать. Ошибка соединения с базой данных";
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
			if ($account->http) {
				return 'Прокси';
			} elseif ($account->ip) {
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
