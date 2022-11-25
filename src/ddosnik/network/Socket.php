<?php

declare(strict_types=1);

/**
 * Description of Socket
 * @author ddosnikgit
 */

namespace ddosnik\network;

use ddosnik\thread\Thread;
use ddosnik\utils\Logger;

class Socket extends Thread {

	/** @var char[] */
	public string $address, $data;
	/** @var JEPacket[]|PEPacket[] */
	public array $packets;
    /** @var \Socket */
    public \Socket $socket;
    /** @var Logger */
    private Logger $logger;
	/** @var short */
	public int $port;
	/** @var float */
	public float $time;

	public function __construct(Logger $logger, string $address, int $port, array $packets, float $time) {
		$this->address = $address;
		$this->port = $port;
        $this->logger = $logger;
		$this->packets = $packets;
		$this->time = $time;
	}

    public function readVarInt() : mixed{
        $a = 0;
        $b = 0;
        while (true) {
            $c = socket_read($this->socket, 1);
            if (!$c) return 0;
            $c = ord($c);
            $a |= ($c & 0x7F) << $b ++ * 7;
            if ($b > 5) return false;
            if (($c & 0x80) != 128) {
                break;
            }
        }
        return $a;
    }

    public function getSocket() : \Socket{
    	return $this->socket;
    }

    protected function finish() : void{
        $this->logger->notice('completed ('. $this->time . ')');
    }

    public function sendPackets() : void{
    	foreach ($this->packets as $packet) {
    		$this->writePacket($packet->getBuffer());
    	}
    }

    public function writePacket(string $buffer) : mixed{
        return socket_sendto($this->socket, $buffer, strlen($buffer), 0, $this->address, $this->port);
    }

    public function writeData(mixed $data) : void{
    	if ($data) {
    		$this->data = $data;
    		return;
    	}
    	throw new \Exception($this->address . ':' . $this->port . ' not returned data');
    }
}