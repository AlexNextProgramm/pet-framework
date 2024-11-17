<?php
namespace Pet\Soket;
use Pet\Soket\Socket;

class Coool{


    static function init(Socket $socket){
       $Coll = new self();
       $server = $Coll->contact($socket);

    }
    private function contact(Socket $socket){
        $ssl = null;
        if ($socket->Cert && $socket->keyPub){
            $ssl = [
                'ssl' => [
                    'local_cert'          => $socket->Cert,
                    'local_pk'            => $socket->keyPub,
                    'disable_compression' => true,
                    'verify_peer'         => false,
                    'ssltransport'        => $socket->transport,
                ]
            ];
        }
     $address =  $socket->transport . '://' . $socket->host . ':' . $socket->port;
     $server = stream_socket_server($address, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $ssl);
     
    }

}