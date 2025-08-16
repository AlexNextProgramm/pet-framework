<?php

namespace Pet\Socket;

class Message
{
    public string $fromAddress;
    public string $toAddress;
    public string $data;
    public string $mediaId;
    public string $status;
    public string $name;
    public int $resourceId;
    public string $type;
}