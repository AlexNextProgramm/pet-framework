<?php
namespace Pet\Apache;

use Pet\Command\Console\Console;
use Pet\Tools\Tools;

class Apache 
{
    static $DIR_APACHE_SITES_ENABLED = DIR_APACHE_SITES_ENABLED;
    static $DIR_SAMPLE = __DIR__.'/../Command/sample/apache';
    static $DIR_SSL = ROOT . DIRECTORY_SEPARATOR .'ssl';
    static $DIR_SH = '/vendor/pet/framework/Command/Console/linux';
    static $DIR_HOSTS = '/etc/hosts';

    function setVirtualHost($nameHost, $type = 'http', $opt = [])
    {
        
        $sample = file_get_contents(self::$DIR_SAMPLE.($type == 'https' ? '/VirtualHostSSL.conf': '/VirtualHost.conf'));
        $sample = str_replace([
            '{name}',
            '{PUBLIC_DIR}',
            '{ROOT}'
         ],[
            $nameHost,
            PUBLIC_DIR,
            ROOT
        ], $sample);
        if(($type == 'https')){
            $sample = str_replace([
                "{pathCert}",
                "{pathKey}",
            ],[
                $opt['crt'],
                $opt['key']
            ], $sample);
        }
        // запись в
        file_put_contents(self::$DIR_APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $nameHost. ($type == 'https'?'-ssl.conf':'.conf'), $sample);
        $this->setHosts($nameHost);
    }
    
    function setHosts($nameHost, $local = "127.0.0.1" ){
        if(!str_contains(file_get_contents(HOSTS),"$local $nameHost")){
            file_put_contents(HOSTS,"\n$local  $nameHost", FILE_APPEND);
        }
    }

    function setCert($nameHost = 'localhost')
    {
        if (!is_dir(self::$DIR_SSL)) mkdir(self::$DIR_SSL, 0777, true);
        Console::log("Создание центра сертификации", "yellow");
        Console::cmd("cd ./ssl && openssl req -newkey rsa:2048 -nodes -keyout CA$nameHost.key -x509 -days 3654 -out CA$nameHost.crt -subj /CN=$nameHost", function ($out) {
            Console::log($out);
        });
        Console::log("Собираю конфиг", "yellow");
        // ssl-config
        $conf = [
            '[ req ]',
            'default_bits = 2048',
            'distinguished_name  = req_distinguished_name',
            'req_extensions     = req_ext',
            '[ req_distinguished_name ]',
            'countryName                 = Country Name (2 letter code)',
            'countryName_default          = RU',
            'stateOrProvinceName          = State or Province Name (full name)',
            'stateOrProvinceName_default  = Moscow',
            'localityName                 = Locality Name (eg, city)',
            'localityName_default         = Moscow',
            'organizationName             = Organization Name (eg, company)',
            'organizationName_default     = Sysadminium',
            'commonName                   = Common Name (eg, YOUR name or FQDN)',
            'commonName_max               = 64',
            "commonName_default           = $nameHost",
            '[ req_ext ]',
            'basicConstraints = CA:FALSE',
            'keyUsage = nonRepudiation, digitalSignature, keyEncipherment',
            "subjectAltName          = DNS:$nameHost"
        ];
        file_put_contents(self::$DIR_SSL . "/ssl.cnf", implode("\n", $conf));
        Console::log("Создаю сертификаты для домена", "yellow");
        Console::cmd("cd ./ssl && openssl req -newkey rsa:2048 -nodes -keyout $nameHost.key -config ssl.cnf -reqexts req_ext -out $nameHost.csr -subj \/C=XX/ST=XX/L=XX/O=XX/OU=XX/CN=XX/emailAddress=XX/", function ($out) {
            Console::log($out);
        });
        Console::log("Подпись центром сертификации", "yellow");
        Console::cmd("cd ./ssl && openssl x509 -req -days 365 -CA CA$nameHost.crt -CAkey CA$nameHost.key -extfile ssl.cnf -extensions req_ext -in $nameHost.csr -out $nameHost.crt -subj /CN=$nameHost", function ($out) {
            Console::log($out);
        });

        Console::log("Добавляю сертификат в доверенные для машины Linux", "yellow");
        Console::cmd("cd ./ssl && sudo cp ./CA$nameHost.crt /usr/local/share/ca-certificates/CA$nameHost.crt && sudo update-ca-certificates", function($out){
            Console::log($out);
        });
     
        $this->setVirtualHost($nameHost, "https",[
            'crt' => ROOT . "/ssl/$nameHost.crt",
            'key' => ROOT . "/ssl/$nameHost.key",
        ]);
        // Console::log('')
    }
}