<?php

declare(strict_types=1);

namespace ddosnik\protocol\java;

class PingPacket extends JEPacket {

	public function __construct() {
		parent::__construct(0);
	}
}