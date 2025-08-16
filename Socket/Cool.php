<?php
namespace Pet\Socket;
use Pet\Socket\Socket;

class Cool{
    private $server;
    private $error = [
        'error'=> '',
        'message' => ''
    ];

     static function init(Socket $socket){
       $cool = new self();
       $cool->server = $cool->stream($socket);
       return $cool;
    }

    private function stream(Socket $socket){
        $ssl = [];
        if (!empty($socket->Cert) && !empty($socket->keyPub)){
            $ssl = [
                'ssl' => [
                    'local_cert'          => $socket->Cert,
                    'local_pk'            => $socket->keyPub,
                    'disable_compression' => true,
                    'verify_peer'         => false,
                    'ssltransport'        => $socket->protocol,
                ]
            ];
        }
     $address =  $socket->protocol. '://' . $socket->host . ':' . $socket->port;
     return stream_socket_server($address, $this->error['error'], $this->error['message'], STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,  stream_context_create($ssl));
    }
    public function getError(){
        return $this->error;
    }

    public function getServer(){
        return $this->server;
    }
    public function isLaunch():bool
    {
        return $this->getServer() !== false;
    }
    public function getIdResource(){
        return get_resource_id($this->getServer());
    }
}