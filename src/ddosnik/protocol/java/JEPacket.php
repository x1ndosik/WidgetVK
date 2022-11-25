<?php

declare(strict_types=1);

namespace ddosnik\protocol\java;

use ddosnik\protocol\Packet;

class JEPacket extends Packet {

	public int $packetId;

	public function __construct(int $packetId) {
		$this->packetId = $packetId;
		$this->buffer = pack('C', $packetId);
	}

	public function getBuffer() : string{
		return pack('C', strlen($this->buffer)) . $this->buffer;
	}
}