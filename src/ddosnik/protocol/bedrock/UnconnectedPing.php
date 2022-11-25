<?php

declare(strict_types=1);

namespace ddosnik\protocol\bedrock;

use ddosnik\protocol\Packet;

class UnconnectedPing extends Packet{
	public static int $ID = 0x01;

	/** @var int */
	public int $sendPingTime;
	/** @var int */
	public int $clientId;

	public function encode() : void{
		parent::encode();
		$this->putLong($this->sendPingTime);
		$this->writeMagic();
		$this->putLong($this->clientId);
	}

	public function decode() : void{
		parent::decode();
		$this->sendPingTime = $this->getLong();
		$this->clientId = $this->getLong();
	}
}