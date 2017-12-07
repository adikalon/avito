<?php

use Randock\AntiCaptcha\Client;
use Randock\AntiCaptcha\Task\ImageToTextTask;
use Randock\AntiCaptcha\TaskResult;
use Randock\AntiCaptcha\Exception\InvalidRequestException;

/**
 * Работа с anti-captcha.com
 */
class AntiCaptcha
{
	/**
	 * Ставит на задание в anti-captcha.ru и возвращает id задания
	 *
	 * @param string $img Ресурс картинки в base64
	 * @param string $token Токен от anti-captcha.ru
	 * @return numeric Id задания
	 * @return false Переданы некорректные параметры или неудалось соедениться с anti-captcha.ru
	 * @return null Недостаточно денег
	 */
	public static function id($img = false, $token = false)
	{
		if (!is_string($img) or !is_string($token)) {
			return false;
		}
		$token = trim($token);
		$json = '{
			"clientKey":"'.$token.'",
			"task": {
				"type": "ImageToTextTask",
				"body": "'.$img.'",
				"phrase": false,
				"case": false,
				"numeric": false,
				"math": 0,
				"minLength": 0,
				"maxLength": 0
			}
		}';
		$options = [
			CURLOPT_URL => 'https://api.anti-captcha.com/createTask',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => [
				'Accept: application/json',
				'Content-Type: application/json'
			]
		];
		$task = Request::curl(false, $options);
		if (!is_string($task)) {
			return false;
		}
		$task = json_decode($task);
		if (!is_object($task)) {
			return false;
		}
		if ($task->errorId == 10) {
			return null;
		} elseif ($task->errorId > 0) {
			return false;
		}
		return $task->taskId;
	}
	
	/**
	 * Отдает картинку кодированную в base64
	 *
	 * @param string $id Id капчи
	 * @param string $token Токен от anti-captcha.ru
	 * @return string кодарованная в base64 картинка
	 * @return false Переданы некорректные параметры
	 * @return null От сервера пришел не ресурс
	 */
	public static function img($id = false)
	{
		if (!is_numeric($id)) {
			return false;
		}
		$options = [
			CURLOPT_URL => "https://www.avito.ru/captcha?$id",
			CURLOPT_USERAGENT => Request::$namedUserAgent,
			CURLOPT_COOKIEFILE => TEMP.'/account.txt',
			CURLOPT_COOKIEJAR => TEMP.'/account.txt',
			CURLOPT_HTTPHEADER => [
				":authority: www.avito.ru",
				":method: GET",
				":path: /captcha?$id",
				":scheme: https",
				"accept-encoding: gzip, deflate, br",
				"accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
				"accept: image/webp,image/apng,image/*,*/*;q=0.8",
				"referer: https://www.avito.ru/profile/login"
			]
		];
		$resource = Request::curl(false, $options);
		if (!is_string($resource)) {
			return null;
		}
		return base64_encode($resource);
	}
	
	/**
	 * Получает разгаданную капчу из anti-captcha.ru или статус задания
	 *
	 * @param string $id Идентификатор задания
	 * @param string $token Токен от anti-captcha.ru
	 * @return string Текст разгаданной капчи
	 * @return false Переданы некорректные параметры или неудалось соедениться с anti-captcha.ru
	 * @return null Капча пока не разгадана
	 */
	public static function result($id = false, $token = false)
	{
		if (!is_numeric($id) or !is_string($token)) {
			return false;
		}
		$json = '{
			"clientKey":"'.$token.'",
			"taskId":"'.$id.'"
		}';
		$token = trim($token);
		$id = trim($id);
		$options = [
			CURLOPT_URL => 'https://api.anti-captcha.com/getTaskResult',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => [
				'Accept: application/json',
				'Content-Type: application/json'
			]
		];
		$result = Request::curl(false, $options);
		if (!is_string($result)) {
			return false;
		}
		$result = json_decode($result);
		if (!is_object($result)) {
			return false;
		}
		if ($result->errorId > 0) {
			return false;
		}
		if ($result->status == 'processing') {
			return null;
		}
		return $result->solution->text;
	}
	
	/**
	 * Проверяет существует ли токен
	 *
	 * @param string $token Токен
	 * @return true Существует
	 * @return false Не существует
	 * @return null Передан некорректный параметр
	 * @return 0 Не удалось соедениться с anti-captcha.com
	 */
	public static function check($token = false)
	{
		if (!is_string($token)) {
			return null;
		}
		$token = trim($token);
		$options = [
			CURLOPT_URL => 'https://api.anti-captcha.com/getBalance',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => '{"clientKey":"'.$token.'"}',
			CURLOPT_HTTPHEADER => [
				'Accept: application/json',
				'Content-Type: application/json'
			]
		];
		$chek = Request::curl(false, $options);
		if (!is_string($chek)) {
			return 0;
		}
		$chek = json_decode($chek);
		if (!is_object($chek)) {
			return 0;
		}
		if ($chek->errorId > 0) {
			return false;
		}
		return true;
	}
	
	/**
	 * Возвращает баланс на anti-captcha.ru
	 *
	 * @param string $token Токен
	 * @return string Сумма баланса на счету
	 * @return false Не удалось соедениться с anti-captcha.com
	 * @return null Некорректный или пустой параметр
	 */
	public static function balance($token = false)
	{
		if (!is_string($token) or empty($token)) {
			return null;
		}
		$options = [
			CURLOPT_URL => 'https://api.anti-captcha.com/getBalance',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => '{"clientKey":"'.$token.'"}',
			CURLOPT_HTTPHEADER => [
				'Accept: application/json',
				'Content-Type: application/json'
			]
		];
		$check = Request::curl(false, $options);
		if (!is_string($check)) {
			return false;
		}
		$check = json_decode($check);
		if (!is_object($chek)) {
			return false;
		}
		if ($check->errorId > 0) {
			return false;
		}
		return $check->balance;
	}
	
	/**
	 * Собирательный метод. Использует все необходимые методы для разгадки капчи
	 *
	 * @param string $img Ссылка на картинку капчи
	 * @param string $token Токен от anti-captcha.ru
	 * @return string Код капчи
	 * @return false Переданы некорректные параметры
	 * @return 1 На балансе anti-captcha.ru закончились деньги
	 * @return 2 Некорректный токен
	 * @return 3 Неизвестная ошибка
	 * @return null Токен не установлен. Разгадка не запустилась
	 */
	public static function decode($id = false)
	{
		if (!is_numeric($id)) {
			return false;
		}
		$sql = "SELECT token FROM settings LIMIT 1";
		$token = DB::connect()->query($sql)->fetch(PDO::FETCH_OBJ)->token;
		$token = trim($token);
		if (empty($token)) {
			return null;
		}
		$chek = self::check($token);
		if ($chek === 0) {
			return 3;
		} elseif ($chek !== true) {
			return 2;
		}
		$img = self::img($id);
		if (!$img) {
			return 3;
		}
		$id = self::id($img, $token);
		if ($id === null) {
			return 1;
		} elseif (!is_numeric($id) or $id === false) {
			return 3;
		}
		$n = 0;
		do {
			sleep(1);
			$n++;
			$result = self::result($id, $token);
			if ($n > 60) {
				break;
			}
		} while (self::result($id, $token) === null);
		if (!$result) {
			return 3;
		}
		return $result;
	}
}
