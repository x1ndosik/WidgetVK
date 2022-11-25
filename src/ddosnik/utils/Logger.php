<?php

declare(strict_types=1);

namespace ddosnik\utils;

use ddosnik\thread\Thread;
use LogLevel;
use Throwable;
use function sprintf;
use const PHP_EOL;

class Logger extends \AttachableThreadedLogger implements \BufferedLogger{

	private string $format = TerminalColors::AQUA . "[%s] " . TerminalColors::RESET . "%s[%s/%s]: %s" . TerminalColors::RESET;

	public function __construct() {
		parent::__construct();
	}

	public function critical(string $message) : void{
		$this->sendLog($message, LogLevel::CRITICAL, 'CRITICAL', TerminalColors::RED);
	}

	public function info(string $message) : void{
		$this->sendLog($message, LogLevel::INFO, 'INFO', TerminalColors::WHITE);
	}

	public function notice(string $message) : void{
		$this->sendLog($message, LogLevel::NOTICE, 'NOTICE', TerminalColors::AQUA);
	}

	public function buffer(\Closure $c) : void{
		$this->synchronized($c);
	}

    public function log(mixed $level, string $message) : void{
		switch($level){
			case LogLevel::CRITICAL:
				$this->critical($message);
				break;
			case LogLevel::NOTICE:
				$this->notice($message);
				break;
			case LogLevel::INFO:
				$this->info($message);
				break;
		}
	}

	public function logException(Throwable $e, $trace = null) : void{
		$this->critical($e->getMessage());
	}

	protected function getThreadName(?\Thread $thread) : string{
		if ($thread === NULL) {
			return 'Main thread';
		} else if ($thread instanceof Thread) {
			return $thread->getThreadName();
		}
	}

	protected function sendLog(string $message, string $level, string $type, string $color) : void{
		$time = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));

		$threadName = $this->getThreadName(\Thread::getCurrentThread());
		$message = sprintf($this->format, $time->format("H:i:s.v"), $color, $threadName, $type, $message);

		$this->synchronized(function() use ($message, $level) : void{
			echo($message . PHP_EOL);
		});
	}
}