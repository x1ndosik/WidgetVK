<?php

declare(strict_types=1);

interface LogLevel{

	public const EMERGENCY = "emergency";
	public const ALERT = "alert";
	public const CRITICAL = "critical";
	public const ERROR = "error";
	public const WARNING = "warning";
	public const NOTICE = "notice";
	public const INFO = "info";
	public const DEBUG = "debug";
}