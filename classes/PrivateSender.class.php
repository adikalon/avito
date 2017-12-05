<?php

/**
 * Рассылка личных сообщений авторам объявлений
 */
class PrivateSender
{
	/**
	 * Массив категорий
	 */
	private $categories = [];
	
	/**
	 * Текст сообщения
	 */
	private $text = '';
	
	/**
	 * Аккаунты
	 */
	private $accounts = [];
	
	/**
	 * Диапазон задержки между отправкой сообщений
	 */
	private $pause = ['from' => 0, 'to' => 0];
	
	/**
	 * Текущий аккаунт для рассылки
	 */
	private $current = [];
	
	/**
	 * Выбирать аккаунты по порядку или вразброс
	 */
	private $random = 0;
	
	/**
	 * Не ходить дальше какой страницы категории
	 */
	private $break = 0;
	
	/**
	 * Время на которое будет запрещено выполнение рассылки в случае блока по IP
	 */
	private $wait = 0;
	
	public function __construct()
	{
		Logger::send("Запуск...\n");
		if (!$this->isBlock()) {
			Logger::send("Рассылка запрещена в админке. Остановлено\n");
			exit();
		}
		$this->resetStamp();
		if ($this->isStamp() !== true) {
			Logger::send("Доступ к <b>avito.ru</b> временно заблокирован по IP. Установлен запрет на запуск рассылки до <b>".date('H:i:s - d.m.Y', $this->isStamp())."</b>. Остановлено\n");
			exit();
		}
		if (!$this->isCategory()) {
			Logger::send("Отсутствуют категории. Остановлено\n");
			exit();
		}
		if (!$this->isAccount()) {
			Logger::send("Отсутствуют авторизированные аккаунты. Остановлено\n");
			exit();
		}
		if ($this->setSettings() === false) {
			Logger::send("Ошибка соединения с базой данных. Остановлено\n");
			exit();
		} elseif ($this->setSettings() === null) {
			Logger::send("Отсутствует текст сообщения. Остановлено\n");
			exit();
		}
		$this->current = $this->accounts[0];
	}
	
	public function __destruct()
	{
		Logger::send("Завершено\n");
	}
	
	/**
	 * Запустить рассылку
	 */
	public function start()
	{
		foreach ($this->categories as $category) {
			$this->setAds($category);
		}
	}
	
	/**
	 * Обходит страницы категории
	 *
	 * @param array $category Массив с информацией о категории
	 */
	private function setAds($category)
	{
		Logger::send("Рассылка по категории <a target='_blank' href='".$category['link']."'>".$category['name']."</a>\n");
		$n = 1;
		while (true) {
			if (!$this->isBlock()) {
				Logger::send("Рассылка запрещена в админке. Остановлено\n");
				exit();
			}
			$options = [
				CURLOPT_URL => $category['link'].'?p='.$n.'&s=104',
				CURLOPT_ENCODING => '',
				CURLOPT_HEADER => true,
				CURLOPT_COOKIEFILE => TEMP.'/cookie.txt',
				CURLOPT_COOKIEJAR => TEMP.'/cookie.txt',
				CURLOPT_COOKIESESSION => true,
				CURLOPT_USERAGENT => Request::$unknownUserAgent,
				CURLOPT_HTTPHEADER => [
					"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
					"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
					"Upgrade-Insecure-Requests: 1",
				],
			];
			$html = Request::curl(false, $options, rand($this->pause['from'], $this->pause['to']));
			preg_match('/.*Location.*blocked.*/', $html, $match);
			if (isset($match[0]) and !empty($match[0])) {
				if ($this->wait > 0) {
					Logger::send("Доступ к <b>avito.ru</b> временно заблокирован по IP. Установлен запрет на запуск рассылки до <b>".date('H:i:s - d.m.Y', $this->setStamp())."</b>. Остановлено\n");
				} else {
					Logger::send("Доступ к <b>avito.ru</b> временно заблокирован по IP. Остановлено\n");
				}
				exit();
			}
			preg_match('/.*302 Found.*/', $html, $match);
			if (($this->break > 0 and $n++ > $this->break) or !is_string($html) or (isset($match[0]) and !empty($match[0]))) {
				break;
			}
			$hrefs = phpQuery::newDocument($html)->find('a.item-description-title-link');
			foreach ($hrefs as $href) {
				if (strpos(pq($href)->attr('href'), 'redirect') !== false) {
					continue;
				}
				preg_match('/.*_(\d+)/', pq($href)->attr('href'), $match);
				$ad = [
					'title' => trim(pq($href)->text()),
					'link' => 'https://www.avito.ru'.pq($href)->attr('href'),
					'id' => $match[1],
				];
				$this->send($ad, $category);
			}
			$hrefs->unloadDocument();
		}
	}
	
	/**
	 * Отправляет сообщения
	 *
	 * @param array $ad Массив с информацией об объявлении
	 * @param array $category Массив с информацией о категории
	 */
	private function send($ad, $category)
	{
		if (!$this->isBlock()) {
			Logger::send("Рассылка запрещена в админке. Остановлено\n");
			exit();
		}
		$this->checkAuth();
		$tokens = Account::getToken($ad['link'], $this->current['sessid']);
		if (!is_array($tokens)) {
			Logger::send("Нельзя отправить сообщение\n<b>Аккаунт:</b> ".$this->current['login']."\n<b>Категория:</b> <a target='_blank' href='".$category['link']."'>".$category['name']."</a>\n<b>Объявление:</b> <a target='_blank' href='".$ad['link']."'>".$ad['title']."</a>\n");
			sleep($this->pause['from'], $this->pause['to']);
			return null;
		}
		$text = Text::rand($this->text);
		$options = [
			CURLOPT_URL => 'https://www.avito.ru/items/write/'.$ad['id'],
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => '',
			CURLOPT_POSTFIELDS => "token[".$tokens['token']."]=".$tokens['value']."&comment=".$text,
			CURLOPT_HTTPHEADER => Request::$namedUserAgent,
			CURLOPT_COOKIE => "sessid=".$this->current['sessid']."; auth=1",
			CURLOPT_COOKIEFILE => TEMP.'/cookie.txt',
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		Request::curl(false, $options, rand($this->pause['from'], $this->pause['to']));
		Logger::send("Отправлено сообщение\n<b>Аккаунт:</b> ".$this->current['login']."\n<b>Категория:</b> <a target='_blank' href='".$category['link']."'>".$category['name']."</a>\n<b>Объявление:</b> <a target='_blank' href='".$ad['link']."'>".$ad['title']."</a>\n<b>Сообщение:</b> ".$text."\n");
		if ($this->random > 0) {
			$this->setRandAccount();
		}
	}

	/**
	 * Проверяет не отключена ли рассылка
	 *
	 * @return true Включена
	 * @return false Отключена
	 */
	private function isBlock()
	{
		$sql = "SELECT block FROM settings LIMIT 1";
		$send = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if ($send->block == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Проверяет имееются ли категории и если имеются записывает в массив $this->categories;
	 *
	 * @return true Категории имеются
	 * @return false Не имеются
	 */
	private function isCategory()
	{
		$sql = "SELECT name, link FROM categories";
		$catories = DB::connect()->query($sql)->fetchAll(PDO::FETCH_OBJ);
		if (count($catories) > 0) {
			foreach ($catories as $catory) {
				$this->categories[] = [
					'name' => $catory->name,
					'link' => $catory->link,
				];
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Проверяет имееются ли авторизированные аккаунты и если имеются записывает в массив $this->accounts;
	 *
	 * @return true Аккаунты имееются
	 * @return false Не имеется
	 */
	private function isAccount()
	{
		$sql = "SELECT name, sessid, login, password FROM accounts WHERE auth=1";
		$accounts = DB::connect()->query($sql)->fetchAll(PDO::FETCH_OBJ);
		if (count($accounts) > 0) {
			foreach ($accounts as $account) {
				$this->accounts[] = [
					'name' => $account->name,
					'sessid' => $account->sessid,
					'login' => $account->login,
					'password' => $account->password,
				];
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Проверяет не установлена ли метка stamp позже текущего времени
	 *
	 * @return numeric Веменная метка - когда можно возобновить рассылку.Можно начинать рассылку
	 * @return true Можно начинать рассылку
	 */
	private function isStamp()
	{
		$sql = "SELECT stamp FROM settings LIMIT 1";
		$stamp = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if ($stamp->stamp < time()) {
			return true;
		} else {
			return $stamp->stamp;
		}
	}
	
	/**
	 * Устанавливает временную метку до которой нельзя начинать рассылку
	 *
	 * @return numeric Временная метка, когдаможно будет начать рассылку
	 */
	private function setStamp()
	{
		$stamp = DB::connect()->quote($this->wait + time());
		$sql = "UPDATE settings SET stamp=$stamp";
		DB::connect()->exec($sql);
		return $stamp;
	}
	
	/**
	 * Сбивает временную метку stamp если wait установлено в 0
	 */
	private function resetStamp()
	{
		$sql = "SELECT wait FROM settings LIMIT 1";
		$settings = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if ($settings->wait < 1) {
			$sql = "UPDATE settings SET stamp=0";
			DB::connect()->exec($sql);
		}
	}
	
	/**
	 * Устанавливает настройки
	 *
	 * @return true Все прошло успешно
	 * @return false Проблемы с БД
	 */
	private function setSettings()
	{
		$sql = "SELECT text, random, pause_from, pause_to, break, wait FROM settings LIMIT 1";
		$settings = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ);
		if (count($settings) > 0) {
			$this->pause = [
				'from' => $settings->pause_from,
				'to' => $settings->pause_to,
			];
			$this->random = $settings->random;
			$this->break = $settings->break;
			$this->wait = $settings->wait;
			if (empty($settings->text)) {
				return null;
			}
			$this->text = $settings->text;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Запиcать рандомный аккаунт в $this->current
	 */
	private function setRandAccount()
	{
		$this->current = $this->accounts[array_rand($this->accounts)];
	}
	
	/**
	 * Проверка авторизации
	 * 
	 * @return true Авторизирован
	 */
	private function checkAuth()
	{
		$info = Account::getInfo($this->current['sessid']);
		if (is_array($info)) {
			return true;
		}
		$auth = Account::setAccounts($this->current['login'].':'.$this->current['password']);
		foreach ($auth as $aut) {
			Logger::send(Account::getAuthMessage($aut)."\n");
			if ($aut['ip'] and $this->wait > 0) {
				Logger::send("Доступ к <b>avito.ru</b> временно заблокирован по IP. Установлен запрет на запуск рассылки до <b>".date('H:i:s - d.m.Y', $this->setStamp())."</b>. Остановлено\n");
				exit();
			}
		}
		if ($this->isAccount()) {
			$this->current = $this->accounts[0];
		} else {
			Logger::send("Отсутствуют авторизированные аккаунты. Остановлено\n");
			exit();
		}
	}
}