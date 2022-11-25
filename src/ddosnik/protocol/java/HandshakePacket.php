<?php

declare(strict_types=1);

namespace ddosnik\protocol\java;

class HandshakePacket extends JEPacket {

    public function __construct (string $host, int $port, int $protocol, int $nextState) {
        parent::__construct(0);
        $this->putByte($protocol);
        $this->putAddress($host, $port);
        $this->putByte($nextState);
    }
}
