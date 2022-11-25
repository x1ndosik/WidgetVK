<?php

declare(strict_types=1);

interface Logger{

	/**
	 * Critical conditions
	 *
	 * @param string $message
	 */
	public function critical(string $message) : void;

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 */
	public function notice(string $message) : void;

	/**
	 * Inersting events.
	 *
	 * @param string $message
	 */
	public function info(string $message) : void;

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 */
	public function log(mixed $level, string $message) : void;

	/**
	 * Logs a Throwable object
	 *
	 * @param Throwable $e
	 * @param $trace
	 */
	public function logException(\Throwable $e, $trace = null) : void;
}