<?php

declare(strict_types=1);

namespace ddosnik\utils;

interface TerminalColors {

	public const WHITE = "\x1b[38;5;231m";
	public const AQUA = "\x1b[38;5;87m";
	public const RED = "\x1b[38;5;203m";
	public const RESET = "\x1b[m";

}