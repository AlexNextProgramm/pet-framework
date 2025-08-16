<?php

namespace Pet\Socket;

use Error;
use Exception;
use Pet\Command\Console\Console;
use Pet\Socket\Cool;
use Pet\Tools\Tools;
use PSpell\Config;

abstract class Socket {

    public $Cert = null;
    public $keyPub = null;
    public $host = 'localhost';
    public $port = 7777;
    public $url = 'localhost';
    public $protocol = 'tlsv1.3';
    public $entityConnect = [];

    public $HeadHahdShake = [
        'HTTP/1.1 101 Web Switching Protocols',
        'Upgrade' => 'websocket',
        'Connection' => 'upgrade',
        'WebSocket-Origin' => 'http://localhost:7777',
        'WebSocket-Location' => 'ws://localhost:7777',
    ];


    abstract public function evConnect(ResorceSocket $resource): void;
    abstract public function evDisconnect(ResorceSocket $resource): void;
    abstract public function evData(ResorceSocket $resource): void;
    abstract public function evError(string $resource): void;


    function hendshake($connect, $header): bool {
        $headers = array();
        $lines = preg_split("/\r\n/", $header);

        foreach ($lines as $line) {
            $line = rtrim($line);

            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        if (!array_key_exists('Sec-WebSocket-Key', $headers)) return false;
        $secKey = $headers['Sec-WebSocket-Key'];

        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        $headHahd = $this->HeadHahdShake;
        $protocol = $headHahd[0] . "\r\n";
        unset($headHahd[0]);
        $headHahd['Sec-WebSocket-Version'] = '13';
        $headHahd['Sec-WebSocket-Accept'] = $secAccept;
        $headHahd = Tools::filter($headHahd, fn($k, $v) => "$k: $v \r\n");
        $upgrade  = $protocol . implode("", $headHahd) . "\r\n";
        fwrite($connect, $upgrade);
        return true;
    }




    public function start(): void
    {
        $socket = Cool::init($this);

        if (!$socket->isLaunch()) {
            Console::text(print_r($this, true), Console::RED);
            Console::text($socket->getError()['message'], Console::RED);
            Console::die("Not start server", Console::RED);
        }

        $this->entityConnect[$socket->getIdResource()] = new ResorceSocket($socket->getServer());
       

        while (true) {

            $reads = $this->selectStream($this->entityConnect);
            $readsIds = array_keys($reads);

            if (in_array($socket->getIdResource(), $readsIds)) {
                $connect = stream_socket_accept($socket->getServer());
                if (!$connect) continue;

                stream_set_blocking($connect, true);
                $header = fread($connect, 1500);

                $hendbool = $this->hendshake($connect, $header);
                stream_set_blocking($connect, false);
                $resource = new ResorceSocket($connect);

                if ($hendbool) {

                    $this->entityConnect[$resource->id] = $resource;

                    Console::text("Connect $connect ", Console::GREEN);
                    try {
                        $this->evConnect($resource);
                    } catch (Exception | Error $e) {
                        $this->evError($e->getMessage());
                    }
                }
            }

            foreach ($reads as $i => $connect) {
                if ($connect->id == $socket->getIdResource()) continue;

                $data = $connect->getMessange();

                if ($data == false) {
                    Console::text("Disconnect #{$connect->id} ", Console::RED);
    
                    try {
                        $this->evDisconnect($connect, $i);
                    } catch (Exception | Error $e) {
                        $this->evError($e->getMessage());
                    }

                    unset($this->entityConnect[$connect->id]);
                    continue;
                }
                    try {
                        Console::text("Data #{$connect->id} :" . $data, Console::YELLOW);
                        $this->evData($connect, $data);
                    } catch (Exception | Error $e) {
                        $this->evError($e->getMessage());
                    }
                
            }
        }
    }


    private function selectStream(array $reads) {
        $write = $except = null;
        $data = [];
        foreach ($reads as $resource) {
            if ($resource->isConnect())
                $data[] = $resource->getConnect();
        }
        stream_select($data, $write, $except, 10);
        $result = [];
        foreach ($data as $connect)
        {
            $resource = new ResorceSocket($connect);
            $result[$resource->id] = $resource;
        }
        return $result;
    }

}
