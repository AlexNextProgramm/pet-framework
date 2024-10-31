<?php
include_once(__DIR__ . '/FTP.php');
use FTP\Ftp;

class ConnectFtp {
    const ROOT_DIR = ROOT_DIR;
    public static $PUBLIC_DIR;
    public static $VENDOR_DIR;
    public static $IGNORE_DIR;
    public static $IGNORE_FILE;
    public static $isInput = false;
    public static $outInput = ['y','Y','д', 'Д'];
    public function __construct()
    {
        self::$PUBLIC_DIR = self::ROOT_DIR . env('PUBLIC_DIR', 'dist');
        self::$VENDOR_DIR = self::ROOT_DIR . '/vendor';
        self::$IGNORE_DIR = explode("|", env('FTP_DIR_EXEPTION'));
        self::$IGNORE_FILE = explode("|", env('FTP_FILE_EXEPTION'));
    }

    public static function load(){
        new self();

        //  Запись ветки для отчетности;
        Console::cmd("cd '".__DIR__ ."' && git branch",
        function ($out) {  
            strpos($out, "*") !== false ? 
            file_put_contents(self::$PUBLIC_DIR .'/.gitinfo',"DT: ".date('d.m.Y H:i:s')." Branch: " . $out."\n", FILE_APPEND) : "";
        });

        //Построить проект npm
        Console::text("Выполнить build Webpack перед загрузкой на сервер? (y/n)", 'yellow');

        if(in_array(Console::input(), self::$outInput)){
            Console::cmd('cd "'. self::ROOT_DIR .'" && npm run build', fn($txt)=> Console::text($txt, 'violet'));
        }
        
        //Загрузка vendor
        Console::text("Выполнить загрузку папки vendor? (y/n)", 'yellow');
        $isVendor = in_array(Console::input(), self::$outInput);

        $ftp = new Ftp();
        $ftp->host = env('FTP_HOST');
        $ftp->login = env('FTP_LOGIN');
        $ftp->fileIgnore = self::$IGNORE_FILE;
        $ftp->dirIgnore = self::$IGNORE_DIR;
        if (trim(env('FTP_PASSWORD')) == '') {
             Console::text("Нет пароля  FTP (*_*)", 'red');
            return false;
        }


        $ftp->password = env('FTP_PASSWORD');
        if ($ftp->connectCount(5)) {
            $ftp->dirHost = env('FTP_FOLDER');
            $ftp->dir(env('FTP_FOLDER'));
            $ftp->putDirFiles(self::$PUBLIC_DIR, 'dist');
            if ($isVendor == 'y') {
                $ftp->putDirFiles(self::$VENDOR_DIR, 'vendor');
            }
            $ftp->close();
        } else {
            Console::text("Не удалось подключиться по  FTP...", 'red');
        }
    }

}