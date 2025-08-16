<?php

namespace Pet\Socket;

class ResorceSocket
{
    private  $resource;
    public $id;
    public function __construct($resource) {
        $this->resource = is_resource($resource)? $resource : false;
        if ($this->resource) {
            $this->id = get_resource_id($resource);
        }
    }

    public function send(string $text, $opcode = 0x1)
    {
        if ($this->resource == false) return; 
        fwrite($this->resource, self::encode($text));
    }

    public function getMessange():string|false
    {
        if ($this->resource == false) return false;
        $data = stream_get_contents($this->resource);
        if($data ==  false) return false;
        return self::decode($data); 
    }

    public function getConnect(){
        return $this->resource;
    }
    public function isConnect(){
        return $this->resource !== false;
    }

    private static function encode($text, $opcode = 0x1)
    {
        $b1 = 0x80 | ($opcode & 0x0f);
        $length = strlen($text);
        if ($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);
        return $header . $text;
    }

    private static function decode($text):string
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

    public function close() {
        fclose($this->resource);
    }
}