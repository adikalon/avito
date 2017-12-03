<?php

/**
 * Работа с категориями
 */
class Category
{
	/**
	 * Парсит переданную ссылку и отдает корректный вариант
	 *
	 * @param string $link Ссылка на категорию товаров
	 * @return string Обработанная ссылка
	 * @return false Если передана не строка
	 */
	public static function parse($link = false)
	{
		if (!is_string($link)) {
			return false;
		}
		$link = trim($link);
		if (strpos($link, 'http://') !== false) {
			return str_replace('http://', 'https://', $link);
		} elseif (strpos($link, 'https://') === false) {
			return 'https://'.$link;
		}
		return $link;
	}
	
	/**
	 * Отдает название категории
	 *
	 * @param string $link Ссылка на категорию полученная при помощи Category::parse()
	 * @return string Название категории
	 * @return false Если передана не строка
	 * @return curl Ошибки метода Request::curl()
	 */
	public static function name($link)
	{
		if (!is_string($link)) {
			return false;
		}
		$options = [
			CURLOPT_ENCODING => '',
			CURLOPT_USERAGENT => Request::$unknownUserAgent,
			CURLOPT_HTTPHEADER => [
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
				"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"Upgrade-Insecure-Requests: 1",
			],
		];
		$page = Request::curl($link, $options);
		if (!is_string($page)) {
			return $page;
		}
		$query = phpQuery::newDocument($page);
		$breadcrumbs = $query->find('div.breadcrumbs.js-breadcrumbs')->eq(0)->text();
		$query->unloadDocument();
		$patterns = [
			'/\'/',
			'/"/',
			'/\n/',
			'/\s{2,}/',
			'/[\d\s]*$/',
			'/\//',
		];
		$replacements = [
			'',
			'',
			'',
			'',
			'',
			' / ',
		];
		return preg_replace($patterns, $replacements, $breadcrumbs);
	}
	
	/**
	 * Собирательный метод. Задействует все необходимые для добавления/обновления категорий методы
	 * 
	 * @param string $link Ссылка на категоию
	 * @return false Некорректная ссылка, либо категория отсутствует
	 * @return array Массив добавленных и обновленных аккаунтов с информацией о занесении или причинах незанесения в БД
	 */
	public static function setCategory($link = false)
	{
		if (!$link) {
			return false;
		}
		$url = self::parse($_POST['link']);
		if (!is_string($url)) {
			return false;
		}
		$name = self::name($url);
		if (!is_string($url)) {
			return false;
		}
		$insert = CategoryWriter::insertOrUpdate($name, $url);
		if (!is_array($insert)) {
			return false;
		}
		return $insert;
	}
}
