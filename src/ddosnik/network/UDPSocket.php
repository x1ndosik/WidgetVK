<?php

declare(strict_types=1);

/**
 * Description of UDPSocket
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

class UDPSocket extends Socket {

	public function __construct(Logger $logger, string $address, int $port, array $packets, float $time) {
		parent::__construct($logger, $address, $port, $packets, $time);
	}

	public function run() : void{
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

		$this->socket = $socket;

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);

        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]);

        if(!@socket_connect($socket, $this->address, $this->port)) {
        	$this->writeData('error;'.socket_last_error());
        	return;
        }

        $this->sendPackets();

        $this->writeData(socket_read($socket, 8192));

        socket_close($socket);
        
    	$this->time = round(microtime(true) - $this->time, 3);
        $this->finish();
    }

    public function getThreadName() : string{
    	return 'UDPQuery Thread';
    }
}