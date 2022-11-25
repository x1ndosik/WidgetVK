<?php

declare(strict_types=1);

/**
 * Description of TCPSocket
 * @author ddosnikgit
 */

namespace ddosnik\network;

use ddosnik\utils\Logger;
use function socket_create;
use function socket_set_option;
use function socket_connect;
use function socket_close;
use function socket_read;
use function round;

class TCPSocket extends Socket {

	public function __construct(Logger $logger, string $address, int $port, array $packets, float $time) {
		parent::__construct($logger, $address, $port, $packets, $time);
	}

	public function run() {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		$this->socket = $socket;

		if(!@socket_connect($socket, $this->address, $this->port)) {
        	$this->writeData('error;'.socket_last_error());
        	return;
        }

        $this->sendPackets();

        $length = $this->readVarInt();

        $this->writeData(socket_read($socket, $length, PHP_NORMAL_READ));

        socket_close($socket);

        $this->time = round(microtime(true) - $this->time, 3);
        $this->finish();
    }

    public function getThreadName() : string{
    	return 'TCPQuery Thread';
    }
}