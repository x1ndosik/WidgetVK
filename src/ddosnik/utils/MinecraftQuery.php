<?php

declare(strict_types=1);

namespace ddosnik\utils;

/**
 * Description of MinecraftQuery
 * query minecraft pocket|bedrock edition servers and java servers
 * @author ddosnikgit
 */

/** PE/BE Packets */
use ddosnik\protocol\bedrock\UnconnectedPing;
/** JE Packets */
use ddosnik\protocol\java\HandshakePacket;
use ddosnik\protocol\java\PingPacket;
/** Sockets */
use ddosnik\network\UDPSocket;
use ddosnik\network\TCPSocket;
/** Logger */
use ddosnik\utils\Logger;

final class MinecraftQuery {

    static private function getPlatform(int $protocol) : string{
        return match(true) {
            ($protocol > 113) => 'MCBE',
            ($protocol === 113 || $protocol < 113) => 'MCPE',
        };
    }

    static public function queryJava(Logger $logger, string $host, int $port, int $timeout = 2) : array{
        $host = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        $handshakePacket = new HandshakePacket($host, $port, 107, 1);
        $pingPacket = new PingPacket();

        $thread = new TCPSocket($logger, $host, $port, [$handshakePacket, $pingPacket], microtime(true));

        $thread->start(); $thread->join();

        $data = $thread->data;

        $thread->quit();

        if (empty($data) || is_bool($data)) {
            return ['identifier' => $host.':'.$port, 'num' => 0, 'max' => 0, 'error' => 'server do not answer'];
        }

        $data = strstr($data, '{');
        $data = json_decode($data);

        //results
        $motd = '';

        foreach ($data->description->extra as $partMotd) {
            $motd .= $partMotd->text;
        }

        $versionInfo = explode(' ', $data->version->name);

        return [
            'host' => $host,
            'motd' => $motd,
            'protocol' => $data->version->protocol,
            'version' => $versionInfo[1] ?? false,
            'software' => $versionInfo[0] ?? false,
            'num' => $data->players->online ?? false,
            'max' => $data->players->max,
            'modinfo' => $data->modinfo ?? false,
        ];
    }

    static public function queryPocket(Logger $logger, string $host, int $port, int $timeout = 2) : array{

        $pk = new UnconnectedPing();
        $pk->sendPingTime = mt_rand(30, 96);
        $pk->clientId = random_int(-0x7fffffff, 0x7fffffff);
        $pk->encode();

        $thread = new UDPSocket($logger, $host, $port, [$pk], microtime(true));

        $thread->start(); $thread->join();

        $response = $thread->data;

        $thread->quit();

        if (empty($response) || !$response) {
            return ['identifier' => $host.':'.$port, 'num' => 0, 'max' => 0, 'error' => 'server do not answer'];
        }
        if (substr($response, 0, 1) !== "\x1C") {
            return ['host' => $host, 'error' => 'error'];
        }

        $serverInfo = substr($response, 35);
        $serverInfo = preg_replace("#§.#", "", $serverInfo);
        $serverInfo = explode(';', $serverInfo);

        return [
            'host' => $host,
            'motd' => $serverInfo[1],
            'protocol' => $serverInfo[2],
            'version' => $serverInfo[3],
            'num' => $serverInfo[4],
            'max' => $serverInfo[5],
            'uuid' => $serverInfo[6],
            'software' => $serverInfo[7] ?? '',
            'gamemode' => $serverInfo[8] ?? 'SMP',
            'platform' => self::getPlatform((int)$serverInfo[2])
        ];
    }
}