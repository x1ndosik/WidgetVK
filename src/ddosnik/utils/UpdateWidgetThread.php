<?php

declare(strict_types=1);

namespace ddosnik\utils;

use ddosnik\thread\Thread;

class UpdateWidgetThread extends Thread {
    
    /** @var array */
    private array $table;
    /** @var string */
    private string $server;
    /** @var bool */
    public bool $error = false;

	public function __construct(array $table, string $server = 'https://api.vk.com/method/') {
		$this->table = $table;
		$this->server = $server;
	}

	public function run() : void{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->server . 'appWidgets.update?' . http_build_query($this->table));
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		]);
		$data = curl_exec($ch);
		curl_close($ch);
		if (!$data) {
			$this->error = true;
		}
	}
}
