<?php

declare(strict_types=1);

namespace ddosnik\utils;

final class Utils {

	public static function requestToVKApi(string $method, array $parameters, string $server = 'https://api.vk.com/method/') : ?array{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $server . $method . '?' . http_build_query($parameters));
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		]);
		$data = curl_exec($ch);
		curl_close($ch);
		if (!$data) {
			throw new \Exception('Failed to connect to VKontakte server');
		}
		return json_decode($data, true);
	}
}