<?php
namespace Pet\Soket;

abstract class Socket{

    public $Cert = null;
    public $keyPub = null;
    public $host = 'localhost';
    public $port = 7777;
    public $url = '';
    public $transport = 'tlsv1.3';
    public $entityConnect = [];
    public $isHahdShake = true;

    abstract public function eventConnect():void;
    abstract public function eventDisonnect():void;
    abstract public function eventGet():void;

    
   function hendshake($connect, $header): bool
    {
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

        $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                    "Upgrade: websocket\r\n" .
                    "Connection: Upgrade\r\n" .
                    "WebSocket-Origin:" . $this->url . "\r\n" .
                    "WebSocket-Location: wss://" . $this->url . ":" . $this->port . "\r\n" .
                    "Sec-WebSocket-Version: 13\r\n" .
                    "Sec-WebSocket-Accept: $secAccept\r\n\r\n";
        fwrite($connect, $upgrade);
        return true;
    }


    public static function decode($text):string
    {
        $length = @ord($text[1]) & 127;
        
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }
}